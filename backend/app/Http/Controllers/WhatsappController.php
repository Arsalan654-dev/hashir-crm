<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\WhatsappAccount;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use App\Services\WhatsappService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WhatsappController extends Controller
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    public function index(Request $request): View
    {
        $account = WhatsappAccount::default();

        $contacts = WhatsappContact::query()
            ->when($account !== null, fn ($query) => $query->where('whatsapp_account_id', $account->id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('name', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('wa_id', 'like', $search));
            })
            ->with(['lead:id,name,status', 'customer:id,name'])
            ->withCount('messages')
            ->orderByDesc(DB::raw('coalesce(last_message_at, whatsapp_contacts.created_at)'))
            ->paginate(25)
            ->withQueryString();

        return view('pages.whatsapp.index', [
            'account' => $account,
            'contacts' => $contacts,
            'configured' => $this->whatsapp->for($account)->isConfigured(),
            'totals' => [
                'contacts' => WhatsappContact::query()->count(),
                'incoming' => WhatsappMessage::query()->where('direction', 'in')->count(),
                'outgoing' => WhatsappMessage::query()->where('direction', 'out')->count(),
                'today' => WhatsappMessage::query()->whereDate('created_at', today())->count(),
                'leads' => Lead::query()->where('source', 'whatsapp')->orWhere('source', 'whatsapp_bot')->count(),
            ],
        ]);
    }

    public function contacts(Request $request): View
    {
        return view('pages.whatsapp.contacts', [
            'contacts' => WhatsappContact::query()
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = '%'.$request->string('search').'%';
                    $query->where(fn ($query) => $query->where('name', 'like', $search)->orWhere('phone', 'like', $search));
                })
                ->with(['lead:id,name,status', 'customer:id,name'])
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString(),
            'leads' => Lead::query()->orderByDesc('created_at')->limit(200)->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, WhatsappContact $contact): View
    {
        $contact->load(['account', 'lead', 'customer']);

        return view('pages.whatsapp.show', [
            'contact' => $contact,
            'messages' => $contact->messages()->orderBy('id')->limit(300)->get(),
            'configured' => $this->whatsapp->for($contact->account)->isConfigured(),
            'contacts' => WhatsappContact::query()
                ->when($contact->whatsapp_account_id, fn ($query) => $query->where('whatsapp_account_id', $contact->whatsapp_account_id))
                ->orderByDesc(DB::raw('coalesce(last_message_at, whatsapp_contacts.created_at)'))
                ->limit(30)
                ->get(),
        ]);
    }

    public function fetch(Request $request, WhatsappContact $contact): JsonResponse
    {
        $after = (int) $request->query('after', 0);

        $messages = $contact->messages()
            ->when($after > 0, fn ($query) => $query->where('id', '>', $after))
            ->orderBy('id')
            ->limit(100)
            ->get();

        return response()->json([
            'messages' => $messages->map(fn (WhatsappMessage $message) => [
                'id' => $message->id,
                'html' => view('partials.whatsapp-message', ['message' => $message])->render(),
            ]),
        ]);
    }

    public function send(Request $request, WhatsappContact $contact): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:4000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,mp4,mov,pdf,doc,docx,xls,xlsx,txt'],
        ]);

        $this->whatsapp->for($contact->account);

        if (! $this->whatsapp->isConfigured()) {
            $error = 'WhatsApp Cloud API is not configured. Add the phone number ID and access token in WhatsApp settings.';

            return $request->expectsJson()
                ? response()->json(['message' => $error], 422)
                : back()->withErrors(['whatsapp' => $error]);
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = (string) $file->getClientMimeType();
            $type = match (true) {
                str_starts_with($mime, 'image/') => 'image',
                str_starts_with($mime, 'video/') => 'video',
                str_starts_with($mime, 'audio/') => 'audio',
                default => 'document',
            };

            $path = $file->store('whatsapp', 'public');
            $result = $this->whatsapp->sendMedia(
                $contact->wa_id,
                $type,
                url('storage/'.$path),
                $data['body'] ?? null,
                $file->getClientOriginalName(),
            );
        } else {
            $result = $this->whatsapp->sendText($contact->wa_id, $data['body']);
        }

        if ($result['ok'] !== true) {
            return $request->expectsJson()
                ? response()->json(['message' => $result['error']], 422)
                : back()->withErrors(['whatsapp' => $result['error']]);
        }

        Activity::log('sent', 'WhatsApp message sent to '.$contact->displayName(), $contact, [], 'whatsapp');

        return $request->expectsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('status', 'Message sent.');
    }

    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contacts' => ['required', 'string', 'max:200000'],
            'account_id' => ['nullable', 'exists:whatsapp_accounts,id'],
        ]);

        $account = filled($data['account_id'] ?? null)
            ? WhatsappAccount::query()->find($data['account_id'])
            : WhatsappAccount::default();

        $lines = preg_split('/\r\n|\r|\n/', trim($data['contacts'])) ?: [];
        $imported = 0;

        DB::transaction(function () use ($lines, $account, &$imported) {
            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                $parts = str_getcsv($line);
                $name = trim((string) ($parts[0] ?? ''));
                $phone = trim((string) ($parts[1] ?? $parts[0] ?? ''));

                $waId = $this->whatsapp->normaliseNumber($phone);

                if (strlen($waId) < 6) {
                    continue;
                }

                WhatsappContact::query()->updateOrCreate(
                    ['whatsapp_account_id' => $account?->id, 'wa_id' => $waId],
                    [
                        'phone' => $phone,
                        'name' => $name !== '' ? $name : null,
                        'source' => 'import',
                    ],
                );

                $imported++;
            }
        });

        Activity::log('imported', $imported.' WhatsApp contacts imported.', null, [], 'whatsapp');

        return back()->with('status', $imported.' contacts imported.');
    }

    public function createLead(Request $request, WhatsappContact $contact): RedirectResponse
    {
        if ($contact->lead_id !== null) {
            return back()->with('status', 'This contact is already linked to a lead.');
        }

        $lead = Lead::query()->create([
            'lead_no' => Lead::generateNumber(),
            'name' => $contact->displayName(),
            'phone' => $contact->phone,
            'whatsapp_number' => $contact->phone ?? $contact->wa_id,
            'email' => $contact->email,
            'company' => $contact->company,
            'source' => 'whatsapp',
            'status' => 'new',
            'assigned_to' => $request->user()->id,
            'created_by' => $request->user()->id,
            'whatsapp_contact_id' => $contact->id,
            'message' => 'Created manually from WhatsApp inbox.',
        ]);

        $contact->update(['lead_id' => $lead->id]);

        Activity::log('created', 'Lead created from WhatsApp contact '.$contact->displayName(), $lead, [], 'whatsapp');

        return redirect()->route('leads.show', $lead)->with('status', 'Lead created from WhatsApp contact.');
    }

    public function settings(): View
    {
        return view('pages.whatsapp.settings', [
            'accounts' => WhatsappAccount::query()->orderByDesc('is_default')->get(),
            'account' => WhatsappAccount::default() ?? new WhatsappAccount(['api_version' => 'v21.0', 'provider' => 'meta_cloud']),
            'webhookUrl' => url('/webhooks/whatsapp'),
            'whatchimpWebhookUrl' => url('/webhooks/whatsapp/whatchimp'),
            'botEndpoint' => url('/api/bot/lead'),
            'knowledgeEndpoint' => url('/api/bot/knowledge'),
            'providers' => WhatsappAccount::PROVIDER_LABELS,
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'provider' => ['nullable', 'in:'.implode(',', WhatsappAccount::PROVIDERS)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'phone_number_id' => ['nullable', 'string', 'max:100'],
            'business_account_id' => ['nullable', 'string', 'max:100'],
            'waba_id' => ['nullable', 'string', 'max:100'],
            'access_token' => ['nullable', 'string', 'max:2000'],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'verify_token' => ['nullable', 'string', 'max:255'],
            'app_id' => ['nullable', 'string', 'max:100'],
            'app_secret' => ['nullable', 'string', 'max:255'],
            'api_version' => ['nullable', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
            'bot_enabled' => ['nullable', 'boolean'],
        ]);

        $account = WhatsappAccount::query()->firstOrNew(['id' => $request->integer('account_id') ?: null]);

        $account->fill([
            'name' => $data['name'],
            'provider' => $data['provider'] ?? 'meta_cloud',
            'phone_number' => $data['phone_number'] ?? null,
            'phone_number_id' => $data['phone_number_id'] ?? null,
            'business_account_id' => $data['business_account_id'] ?? null,
            'waba_id' => $data['waba_id'] ?? null,
            'base_url' => $data['base_url'] ?? null,
            'verify_token' => $data['verify_token'] ?? $account->verify_token ?? Str::random(32),
            'app_id' => $data['app_id'] ?? null,
            'api_version' => $data['api_version'] ?? 'v21.0',
            'bot_enabled' => $request->boolean('bot_enabled'),
        ]);

        if (filled($data['access_token'] ?? null)) {
            $account->access_token = $data['access_token'];
        }

        if (filled($data['api_key'] ?? null)) {
            $account->api_key = $data['api_key'];
        }

        if (filled($data['app_secret'] ?? null)) {
            $account->app_secret = $data['app_secret'];
        }

        if ($request->boolean('is_default')) {
            WhatsappAccount::query()->update(['is_default' => false]);
            $account->is_default = true;
        }

        $account->save();

        Activity::log('settings_updated', 'WhatsApp account saved: '.$account->name, $account, [], 'whatsapp');

        return back()->with('status', 'WhatsApp settings saved.');
    }

    public function testConnection(Request $request): RedirectResponse
    {
        $account = WhatsappAccount::default();

        if ($account === null) {
            return back()->withErrors(['whatsapp' => 'Save the account first, then test the connection.']);
        }

        $result = $this->whatsapp->for($account)->testConnection();

        if ($result['ok'] !== true) {
            $account->update(['status' => 'error', 'last_error' => $result['error'] ?? 'Unknown error']);

            return back()->withErrors(['whatsapp' => $result['error'] ?? 'Connection failed.']);
        }

        $account->update([
            'status' => 'active',
            'last_error' => null,
            'last_synced_at' => now(),
            'phone_number' => $result['data']['display_phone_number'] ?? $account->phone_number,
            'meta' => $result['data'] ?? $account->meta,
        ]);

        return back()->with('status', 'Connection successful — '.($result['data']['verified_name'] ?? 'account verified').'.');
    }
}
