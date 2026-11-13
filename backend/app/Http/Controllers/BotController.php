<?php

namespace App\Http\Controllers;

use App\Models\BotConversation;
use App\Models\WhatsappAccount;
use App\Support\Activity;
use App\Support\BotSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function conversations(Request $request): View
    {
        $conversations = BotConversation::query()
            ->with(['lead:id,name,lead_no', 'sale:id,sale_no,total', 'invoice:id,invoice_no'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query->where('contact_name', 'like', $search)
                    ->orWhere('contact_phone', 'like', $search)
                    ->orWhere('summary', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('pages.knowledge.bot-conversations', [
            'conversations' => $conversations,
            'stats' => [
                'total' => BotConversation::query()->count(),
                'converted' => BotConversation::query()->whereNotNull('sale_id')->count(),
                'withOrders' => BotConversation::query()->whereNotNull('order_data')->count(),
                'today' => BotConversation::query()->whereDate('created_at', today())->count(),
            ],
        ]);
    }

    public function show(BotConversation $conversation): View
    {
        $conversation->load(['lead', 'sale', 'invoice', 'contact']);

        return view('pages.knowledge.bot-conversation-show', [
            'conversation' => $conversation,
        ]);
    }

    public function settings(Request $request): View
    {
        $organization = $request->user()->organization;
        $account = WhatsappAccount::default();

        return view('pages.knowledge.bot-settings', [
            'settings' => [
                'bot.name' => (string) BotSettings::get($organization, 'bot.name', ''),
                'bot.tone' => (string) BotSettings::get($organization, 'bot.tone', 'friendly-professional'),
                'bot.languages' => (string) BotSettings::get($organization, 'bot.languages', 'en'),
                'bot.greeting' => (string) BotSettings::get($organization, 'bot.greeting', ''),
                'bot.fallback_message' => (string) BotSettings::get($organization, 'bot.fallback_message', ''),
                'bot.handoff_number' => (string) BotSettings::get($organization, 'bot.handoff_number', ''),
                'bot.system_instructions' => (string) BotSettings::get($organization, 'bot.system_instructions', ''),
                'bot.send_products_media' => BotSettings::boolean($organization, 'bot.send_products_media', true) ? '1' : '0',
                'bot.order_enabled' => BotSettings::boolean($organization, 'bot.order_enabled', true) ? '1' : '0',
                'bot.escalation_keywords' => (string) BotSettings::get($organization, 'bot.escalation_keywords', ''),
                'bot.ai_enabled' => BotSettings::boolean($organization, 'bot.ai_enabled') ? '1' : '0',
                'bot.ai_base_url' => (string) BotSettings::get($organization, 'bot.ai_base_url', 'https://api.openai.com/v1'),
                'bot.ai_model' => (string) BotSettings::get($organization, 'bot.ai_model', 'gpt-4o-mini'),
                'bot.enabled' => $account?->bot_enabled ? '1' : '0',
                'kitchen.ask_payment' => BotSettings::boolean($organization, 'kitchen.ask_payment', true) ? '1' : '0',
                'kitchen.msg_payment_question' => (string) BotSettings::get($organization, 'kitchen.msg_payment_question'),
                'kitchen.msg_confirmed' => (string) BotSettings::get($organization, 'kitchen.msg_confirmed'),
                'kitchen.msg_preparing' => (string) BotSettings::get($organization, 'kitchen.msg_preparing'),
                'kitchen.msg_ready' => (string) BotSettings::get($organization, 'kitchen.msg_ready'),
                'kitchen.msg_dispatched' => (string) BotSettings::get($organization, 'kitchen.msg_dispatched'),
                'kitchen.msg_delivered' => (string) BotSettings::get($organization, 'kitchen.msg_delivered'),
                'kitchen.msg_cancelled' => (string) BotSettings::get($organization, 'kitchen.msg_cancelled'),
                'kitchen.msg_payment_cod' => (string) BotSettings::get($organization, 'kitchen.msg_payment_cod'),
                'kitchen.msg_payment_online' => (string) BotSettings::get($organization, 'kitchen.msg_payment_online'),
            ],
            'hasAiKey' => filled(BotSettings::aiApiKey($organization)),
            'account' => $account,
            'organization' => $organization,
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bot_name' => ['nullable', 'string', 'max:100'],
            'bot_tone' => ['nullable', 'string', 'max:60'],
            'bot_languages' => ['nullable', 'string', 'max:120'],
            'bot_system_instructions' => ['nullable', 'string', 'max:20000'],
            'bot_enabled' => ['nullable', 'boolean'],
            'bot_send_products_media' => ['nullable', 'boolean'],
            'bot_order_enabled' => ['nullable', 'boolean'],
            'bot_handoff_number' => ['nullable', 'string', 'max:50'],
            'bot_greeting' => ['nullable', 'string', 'max:1000'],
            'bot_fallback_message' => ['nullable', 'string', 'max:1000'],
            'bot_escalation_keywords' => ['nullable', 'string', 'max:500'],
            'bot_ai_enabled' => ['nullable', 'boolean'],
            'bot_ai_base_url' => ['nullable', 'string', 'max:255'],
            'bot_ai_model' => ['nullable', 'string', 'max:100'],
            'bot_ai_api_key' => ['nullable', 'string', 'max:500'],
            'kitchen_ask_payment' => ['nullable', 'boolean'],
            'kitchen_msg_payment_question' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_confirmed' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_preparing' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_ready' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_dispatched' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_delivered' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_cancelled' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_payment_cod' => ['nullable', 'string', 'max:1000'],
            'kitchen_msg_payment_online' => ['nullable', 'string', 'max:1000'],
        ]);

        $organization = $request->user()->organization;

        BotSettings::put($organization, [
            'bot.name' => $data['bot_name'] ?? '',
            'bot.tone' => $data['bot_tone'] ?? 'friendly-professional',
            'bot.languages' => $data['bot_languages'] ?? 'en',
            'bot.system_instructions' => $data['bot_system_instructions'] ?? '',
            'bot.handoff_number' => $data['bot_handoff_number'] ?? '',
            'bot.greeting' => $data['bot_greeting'] ?? '',
            'bot.fallback_message' => $data['bot_fallback_message'] ?? '',
            'bot.escalation_keywords' => $data['bot_escalation_keywords'] ?? '',
            'bot.ai_base_url' => $data['bot_ai_base_url'] ?? 'https://api.openai.com/v1',
            'bot.ai_model' => $data['bot_ai_model'] ?? 'gpt-4o-mini',
            'bot.send_products_media' => $request->boolean('bot_send_products_media') ? '1' : '0',
            'bot.order_enabled' => $request->boolean('bot_order_enabled') ? '1' : '0',
            'bot.ai_enabled' => $request->boolean('bot_ai_enabled') ? '1' : '0',
            'bot.ai_api_key' => $request->input('bot_ai_api_key'),
            'kitchen.ask_payment' => $request->boolean('kitchen_ask_payment') ? '1' : '0',
            'kitchen.msg_payment_question' => $data['kitchen_msg_payment_question'] ?? '',
            'kitchen.msg_confirmed' => $data['kitchen_msg_confirmed'] ?? '',
            'kitchen.msg_preparing' => $data['kitchen_msg_preparing'] ?? '',
            'kitchen.msg_ready' => $data['kitchen_msg_ready'] ?? '',
            'kitchen.msg_dispatched' => $data['kitchen_msg_dispatched'] ?? '',
            'kitchen.msg_delivered' => $data['kitchen_msg_delivered'] ?? '',
            'kitchen.msg_cancelled' => $data['kitchen_msg_cancelled'] ?? '',
            'kitchen.msg_payment_cod' => $data['kitchen_msg_payment_cod'] ?? '',
            'kitchen.msg_payment_online' => $data['kitchen_msg_payment_online'] ?? '',
        ]);

        $account = WhatsappAccount::default();

        if ($account !== null) {
            $account->update(['bot_enabled' => $request->boolean('bot_enabled')]);
        }

        Activity::log('settings_updated', 'Bot settings updated.', null, [], 'bot');

        return back()->with('status', 'Bot settings saved.');
    }
}
