<?php

namespace App\Http\Controllers;

use App\Models\WhatsappBroadcast;
use App\Models\WhatsappContact;
use App\Services\WhatsappService;
use App\Support\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BroadcastController extends Controller
{
    public function index(Request $request): View
    {
        $whatsapp = app(WhatsappService::class);

        return view('pages.whatsapp.broadcast', [
            'broadcasts' => WhatsappBroadcast::query()
                ->with('user:id,name')
                ->latest()
                ->paginate(15),
            'audiences' => [
                'all' => 'All contacts ('.WhatsappContact::query()->where('is_blocked', false)->count().')',
                'leads' => 'Contacts with a lead ('.WhatsappContact::query()->where('is_blocked', false)->whereNotNull('lead_id')->count().')',
                'customers' => 'Contacts with a customer ('.WhatsappContact::query()->where('is_blocked', false)->whereNotNull('customer_id')->count().')',
            ],
            'configured' => $whatsapp->isConfigured(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:4000'],
            'audience' => ['required', Rule::in(WhatsappBroadcast::AUDIENCES)],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $whatsapp = app(WhatsappService::class);

        if (! $whatsapp->isConfigured()) {
            return back()->withErrors(['message' => 'WhatsApp is not configured yet. Add your credentials in WhatsApp settings first.'])->withInput();
        }

        $query = WhatsappContact::query()->where('is_blocked', false);

        if ($data['audience'] === 'leads') {
            $query->whereNotNull('lead_id');
        }

        if ($data['audience'] === 'customers') {
            $query->whereNotNull('customer_id');
        }

        $contacts = $query->limit((int) ($data['limit'] ?? 100))->get();

        if ($contacts->isEmpty()) {
            return back()->withErrors(['message' => 'No contacts match this audience.'])->withInput();
        }

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($contacts as $contact) {
            $result = $whatsapp->sendText($contact->wa_id, $data['message']);

            if ($result['ok']) {
                $sent++;
            } else {
                $failed++;
                $errors[$contact->wa_id] = (string) ($result['error'] ?? 'failed');
            }
        }

        $broadcast = WhatsappBroadcast::query()->create([
            'whatsapp_account_id' => $whatsapp->account()?->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'] ?? null,
            'message' => $data['message'],
            'recipients_count' => $contacts->count(),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => match (true) {
                $failed === 0 => 'completed',
                $sent > 0 => 'partial',
                default => 'failed',
            },
            'meta' => $errors === [] ? null : ['errors' => array_slice($errors, 0, 20, true)],
        ]);

        Activity::log(
            'broadcast_sent',
            'Broadcast sent to '.$sent.' contacts'.($failed > 0 ? ' ('.$failed.' failed)' : ''),
            $broadcast,
            ['recipients' => $contacts->count()],
            'whatsapp',
        );

        return back()->with('status', 'Broadcast sent to '.$sent.' contact(s)'.($failed > 0 ? ' — '.$failed.' failed' : '').'.');
    }
}
