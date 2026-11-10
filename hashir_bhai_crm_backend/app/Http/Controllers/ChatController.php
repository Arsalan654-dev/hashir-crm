<?php

namespace App\Http\Controllers;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\Message;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $channels = ChatChannel::query()
            ->whereHas('memberRecords', fn ($query) => $query->where('user_id', $user->id))
            ->with(['members:id,name,avatar', 'latestMessage' => fn ($query) => $query->limit(1), 'whatsappContact'])
            ->withCount(['messages'])
            ->orderByDesc(DB::raw('coalesce(last_message_at, chat_channels.created_at)'))
            ->get();

        return view('pages.chat.index', [
            'channels' => $channels,
            'users' => User::query()->where('is_active', true)->whereKeyNot($user->id)->orderBy('name')->get(['id', 'name', 'avatar']),
            'unreadTotal' => ChatChannelMember::query()
                ->where('user_id', $user->id)
                ->whereHas('channel')
                ->get()
                ->sum(fn (ChatChannelMember $member) => $this->unreadCount($member)),
        ]);
    }

    public function show(Request $request, ChatChannel $channel): View
    {
        $this->authorizeChannel($channel, $request);

        $channel->load(['members:id,name,avatar', 'whatsappContact:id,name,phone,wa_id']);

        $this->markRead($channel, $request->user()->id);

        return view('pages.chat.show', [
            'channel' => $channel,
            'channels' => ChatChannel::query()
                ->whereHas('memberRecords', fn ($query) => $query->where('user_id', $request->user()->id))
                ->with('latestMessage')
                ->orderByDesc(DB::raw('coalesce(last_message_at, chat_channels.created_at)'))
                ->get(),
            'messages' => $channel->messages()->with('user:id,name,avatar')->orderBy('id')->limit(200)->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'isWhatsapp' => $channel->type === ChatChannel::TYPE_WHATSAPP,
        ]);
    }

    public function store(Request $request, ChatChannel $channel): RedirectResponse|JsonResponse
    {
        $this->authorizeChannel($channel, $request);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'reply_to_id' => ['nullable', 'exists:messages,id'],
        ]);

        $type = 'text';
        $path = null;
        $name = null;
        $mime = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getClientMimeType();
            $type = match (true) {
                str_starts_with((string) $mime, 'image/') => 'image',
                str_starts_with((string) $mime, 'video/') => 'video',
                str_starts_with((string) $mime, 'audio/') => 'audio',
                default => 'document',
            };
            $path = $file->store('chat', 'public');
            $name = $file->getClientOriginalName();
        }

        $message = Message::query()->create([
            'chat_channel_id' => $channel->id,
            'user_id' => $request->user()->id,
            'type' => $type,
            'body' => $data['body'] ?? null,
            'attachment_path' => $path,
            'attachment_name' => $name,
            'mime_type' => $mime,
            'reply_to_id' => $data['reply_to_id'] ?? null,
            'sent_at' => now(),
        ]);

        $channel->update(['last_message_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $message->id,
                'html' => view('partials.chat-message', ['message' => $message->load('user:id,name,avatar'), 'mine' => true])->render(),
            ]);
        }

        return back();
    }

    public function fetch(Request $request, ChatChannel $channel): JsonResponse
    {
        $this->authorizeChannel($channel, $request);

        $after = (int) $request->query('after', 0);

        $messages = $channel->messages()
            ->with('user:id,name,avatar')
            ->when($after > 0, fn ($query) => $query->where('id', '>', $after))
            ->orderBy('id')
            ->limit(100)
            ->get();

        $this->markRead($channel, $request->user()->id);

        return response()->json([
            'messages' => $messages->map(fn (Message $message) => [
                'id' => $message->id,
                'html' => view('partials.chat-message', [
                    'message' => $message,
                    'mine' => $message->user_id === $request->user()->id,
                ])->render(),
            ]),
        ]);
    }

    public function startDirect(Request $request, User $user): RedirectResponse
    {
        $channel = ChatChannel::findDirectChannel($request->user()->id, $user->id);

        if ($channel === null) {
            $channel = ChatChannel::query()->create([
                'type' => ChatChannel::TYPE_DIRECT,
                'name' => $request->user()->name.' & '.$user->name,
                'created_by' => $request->user()->id,
                'last_message_at' => now(),
            ]);

            $channel->memberRecords()->createMany([
                ['user_id' => $request->user()->id, 'role' => 'member', 'joined_at' => now()],
                ['user_id' => $user->id, 'role' => 'member', 'joined_at' => now()],
            ]);
        }

        return redirect()->route('chat.show', $channel);
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'members' => ['required', 'array', 'min:1'],
            'members.*' => ['exists:users,id'],
        ]);

        $channel = ChatChannel::query()->create([
            'type' => ChatChannel::TYPE_GROUP,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => $request->user()->id,
            'last_message_at' => now(),
        ]);

        $memberIds = collect($data['members'])->push($request->user()->id)->unique();

        foreach ($memberIds as $memberId) {
            $channel->memberRecords()->create([
                'user_id' => $memberId,
                'role' => $memberId === $request->user()->id ? 'admin' : 'member',
                'joined_at' => now(),
            ]);
        }

        Activity::log('created', 'Chat group created: '.$channel->name, $channel, [], 'chat');

        return redirect()->route('chat.show', $channel)->with('status', 'Group created.');
    }

    public function addMembers(Request $request, ChatChannel $channel): RedirectResponse
    {
        abort_unless($channel->isGroup(), 404);

        $data = $request->validate([
            'members' => ['required', 'array', 'min:1'],
            'members.*' => ['exists:users,id'],
        ]);

        foreach ($data['members'] as $memberId) {
            $channel->memberRecords()->firstOrCreate(
                ['user_id' => $memberId],
                ['role' => 'member', 'joined_at' => now()],
            );
        }

        return back()->with('status', 'Members added to the group.');
    }

    public function removeMember(Request $request, ChatChannel $channel, User $user): RedirectResponse
    {
        abort_unless($channel->isGroup(), 404);

        if ($user->id === $channel->created_by && $request->user()->id !== $channel->created_by) {
            return back()->withErrors(['member' => 'The group owner cannot be removed.']);
        }

        $channel->memberRecords()->where('user_id', $user->id)->delete();

        return back()->with('status', 'Member removed.');
    }

    private function authorizeChannel(ChatChannel $channel, Request $request): void
    {
        $isMember = $channel->memberRecords()->where('user_id', $request->user()->id)->exists();

        if (! $isMember && ! $request->user()->isSuperAdmin()) {
            abort(403, '403 — You cannot access this.');
        }
    }

    private function markRead(ChatChannel $channel, int $userId): void
    {
        $channel->memberRecords()->where('user_id', $userId)->update(['last_read_at' => now()]);
    }

    private function unreadCount(ChatChannelMember $member): int
    {
        return Message::query()
            ->where('chat_channel_id', $member->chat_channel_id)
            ->when($member->last_read_at !== null, fn ($query) => $query->where('created_at', '>', $member->last_read_at))
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', '!=', $member->user_id))
            ->count();
    }
}
