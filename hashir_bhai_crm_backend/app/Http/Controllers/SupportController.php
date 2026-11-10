<?php

namespace App\Http\Controllers;

use App\Models\BotConversation;
use App\Models\WhatsappAccount;
use App\Services\WhatsappService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'escalated');
        $tag = (string) $request->query('tag', '');

        $conversations = BotConversation::query()
            ->with(['contact:id,name,phone,avatar_url,last_message_at', 'lead:id,name,lead_no', 'handler:id,name'])
            ->when($status === 'escalated', fn ($query) => $query->where('status', 'escalated'))
            ->when($status === 'resolved', fn ($query) => $query->where('status', 'resolved'))
            ->when($status === 'all', fn ($query) => $query->whereIn('status', ['escalated', 'resolved']))
            ->when($tag !== '', fn ($query) => $query->whereJsonContains('tags', $tag))
            ->orderByRaw("case when status = 'escalated' then 0 else 1 end")
            ->orderByDesc('escalated_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.support.index', [
            'conversations' => $conversations,
            'status' => $status,
            'tag' => $tag,
            'counts' => [
                'escalated' => BotConversation::query()->where('status', 'escalated')->count(),
                'resolved_today' => BotConversation::query()->where('status', 'resolved')->whereDate('resolved_at', today())->count(),
                'converted' => BotConversation::query()->where('status', 'converted')->count(),
                'total' => BotConversation::query()->count(),
            ],
            'canManage' => $request->user()->isSuperAdmin() || $request->user()->can('support.manage'),
        ]);
    }

    public function show(BotConversation $conversation): View
    {
        $conversation->load(['contact', 'lead', 'handler:id,name', 'sale', 'invoice']);
        $contact = $conversation->contact;

        return view('pages.support.show', [
            'conversation' => $conversation,
            'contact' => $contact,
            'messages' => $contact?->messages()->orderBy('id')->limit(300)->get() ?? collect(),
            'canManage' => auth()->user()?->isSuperAdmin() || auth()->user()?->can('support.manage'),
        ]);
    }

    public function reply(Request $request, BotConversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $contact = $conversation->contact;

        if ($contact === null) {
            return back()->withErrors(['body' => 'This conversation has no WhatsApp contact.']);
        }

        $account = $contact->account ?? WhatsappAccount::default();

        if ($account === null) {
            return back()->withErrors(['body' => 'No WhatsApp account configured.']);
        }

        $result = app(WhatsappService::class)->for($account)->sendText($contact->wa_id, $data['body']);

        if (! $result['ok']) {
            return back()->withErrors(['body' => $result['error'] ?? 'Message could not be sent.']);
        }

        $transcript = $conversation->transcript ?? [];
        $transcript[] = ['role' => 'agent', 'text' => $data['body'], 'at' => now()->toIso8601String()];

        if (count($transcript) > 60) {
            $transcript = array_slice($transcript, -60);
        }

        $conversation->update(['transcript' => $transcript, 'handled_by' => $request->user()->id]);

        Activity::log('support_reply', 'Support replied to '.$contact->displayName(), $conversation, [], 'support');

        return back()->with('status', 'Reply sent to '.$contact->displayName().'.');
    }

    public function resolve(Request $request, BotConversation $conversation): RedirectResponse
    {
        $conversation->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'handled_by' => $request->user()->id,
        ]);

        Activity::log('support_resolved', 'Conversation resolved: '.$conversation->contact_name, $conversation, [], 'support');

        return back()->with('status', 'Conversation marked as resolved.');
    }

    public function updateTags(Request $request, BotConversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'tags' => ['nullable', 'string', 'max:300'],
        ]);

        $tags = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn (string $tag): string => mb_strtolower(trim($tag)))
            ->filter()
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $conversation->update(['tags' => $tags]);

        Activity::log('support_tagged', 'Conversation tagged: '.$conversation->contact_name, $conversation, ['tags' => $tags], 'support');

        return back()->with('status', 'Tags updated.');
    }

    public function reopen(Request $request, BotConversation $conversation): RedirectResponse
    {
        $conversation->update([
            'status' => 'escalated',
            'escalated_at' => $conversation->escalated_at ?? now(),
            'resolved_at' => null,
            'handled_by' => $request->user()->id,
        ]);

        Activity::log('support_reopened', 'Conversation reopened: '.$conversation->contact_name, $conversation, [], 'support');

        return back()->with('status', 'Conversation reopened.');
    }

    public function handToBot(Request $request, BotConversation $conversation): RedirectResponse
    {
        $conversation->update([
            'status' => 'active',
            'resolved_at' => now(),
            'handled_by' => $request->user()->id,
            'ended_at' => now(),
        ]);

        Activity::log('support_handed_to_bot', 'Conversation handed back to bot: '.$conversation->contact_name, $conversation, [], 'support');

        return back()->with('status', 'Conversation handed back to the bot. It will reply to the next message.');
    }
}
