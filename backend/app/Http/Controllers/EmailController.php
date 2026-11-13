<?php

namespace App\Http\Controllers;

use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Services\GmailService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailController extends Controller
{
    public function __construct(private readonly GmailService $gmail) {}

    public function inbox(Request $request): View
    {
        $folder = (string) $request->query('folder', 'inbox');

        $emails = EmailMessage::query()
            ->when($folder !== 'all', fn ($query) => $query->where('folder', $folder))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('subject', 'like', $search)
                    ->orWhere('from_name', 'like', $search)
                    ->orWhere('from_email', 'like', $search)
                    ->orWhere('teaser', 'like', $search));
            })
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $account = GmailAccount::query()->where('is_active', true)->orderByDesc('id')->first();

        return view('pages.email.inbox', [
            'emails' => $emails,
            'folder' => $folder,
            'folderCounts' => [
                'inbox' => EmailMessage::query()->where('folder', 'inbox')->count(),
                'sent' => EmailMessage::query()->where('folder', 'sent')->count(),
                'unread' => EmailMessage::query()->where('is_read', false)->count(),
                'starred' => EmailMessage::query()->where('is_starred', true)->count(),
            ],
            'account' => $account,
            'gmailConfigured' => $this->gmail->isConfigured(),
        ]);
    }

    public function show(EmailMessage $email): View
    {
        if (! $email->is_read) {
            $email->update(['is_read' => true]);
        }

        return view('pages.email.read', [
            'email' => $email,
            'account' => GmailAccount::query()->where('is_active', true)->orderByDesc('id')->first(),
        ]);
    }

    public function compose(Request $request): View|RedirectResponse
    {
        $account = GmailAccount::query()->where('is_active', true)->orderByDesc('id')->first();

        if ($request->isMethod('get')) {
            return view('pages.email.compose', [
                'account' => $account,
                'gmailConfigured' => $this->gmail->isConfigured(),
                'replyTo' => $request->filled('reply_to') ? EmailMessage::query()->find($request->integer('reply_to')) : null,
            ]);
        }

        $data = $request->validate([
            'to' => ['required', 'email', 'max:255'],
            'cc' => ['nullable', 'string', 'max:255'],
            'bcc' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $attachments = [];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'mime' => (string) $file->getClientMimeType(),
                'data' => (string) file_get_contents($file->getRealPath()),
            ];
        }

        $sent = false;
        $error = null;

        if ($account !== null) {
            $result = $this->gmail->sendMessage(
                $account,
                $data['to'],
                $data['subject'],
                $data['body'],
                $data['cc'] ?? null,
                $data['bcc'] ?? null,
                $attachments,
            );

            $sent = $result['ok'] === true;
            $error = $result['error'] ?? null;
        } else {
            $error = 'No Gmail account connected. The email was saved as a draft only.';
        }

        EmailMessage::query()->create([
            'gmail_account_id' => $account?->id,
            'provider' => $account !== null ? 'gmail' : 'local',
            'folder' => 'sent',
            'from_name' => $account?->display_name ?? (auth()->user()?->name ?? 'CRM'),
            'from_email' => $account?->email ?? (auth()->user()?->email ?? 'noreply@localhost'),
            'to_email' => $data['to'],
            'to_recipients' => array_filter(array_map('trim', explode(',', $data['to']))),
            'cc_recipients' => array_filter(array_map('trim', explode(',', $data['cc'] ?? ''))),
            'bcc_recipients' => array_filter(array_map('trim', explode(',', $data['bcc'] ?? ''))),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'teaser' => Str::limit(strip_tags($data['body']), 90),
            'is_read' => true,
            'received_at' => now(),
            'time_label' => now()->format('H:i'),
            'date_label' => now()->format('d M'),
        ]);

        Activity::log('sent', 'Email sent to '.$data['to'], null, ['sent' => $sent], 'email');

        if (! $sent) {
            return back()->withErrors(['email' => $error ?? 'Email could not be sent.'])->withInput();
        }

        return redirect()->route('email-inbox', ['folder' => 'sent'])->with('status', 'Email sent to '.$data['to'].'.');
    }

    public function destroy(Request $request, EmailMessage $email): RedirectResponse
    {
        $email->delete();

        return $request->expectsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('status', 'Email deleted.');
    }
}
