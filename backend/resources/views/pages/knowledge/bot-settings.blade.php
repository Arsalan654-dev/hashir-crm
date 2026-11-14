@extends('layouts.app')

@section('title', 'Bot Settings')
@section('page-title', 'WhatsApp AI Bot Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/bot/conversations') }}">Bot Conversations</a></li>
    <li class="breadcrumb-item active">Bot Settings</li>
@endsection

@php
    $toneOptions = ['friendly-professional', 'friendly', 'professional', 'formal', 'casual', 'enthusiastic'];
    $currentTone = old('bot_tone', $settings['bot.tone'] ?? 'friendly-professional');
    if ($currentTone !== '' && ! in_array($currentTone, $toneOptions, true)) {
        array_unshift($toneOptions, $currentTone);
    }
@endphp

@section('content')
    <div class="alert alert-info d-flex align-items-start">
        <i class="bx bx-bot font-size-20 me-2"></i>
        <div>
            These settings control the WhatsApp assistant. The bot answers from your
            <a href="{{ url('/knowledge') }}" class="alert-link">Knowledge Base</a> and your
            <a href="{{ url('/products') }}" class="alert-link">product catalog</a>, captures orders and pushes qualified leads.
            Without an AI key, the bot uses smart keyword matching; add an AI key below for natural conversations.
        </div>
    </div>

    <form action="{{ url('/bot/settings') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Identity &amp; Language</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Bot Name</label>
                            <input type="text" name="bot_name" class="form-control" value="{{ old('bot_name', $settings['bot.name'] ?? '') }}" placeholder="Jaffri Assistant">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tone</label>
                            <select name="bot_tone" class="form-select">
                                @foreach ($toneOptions as $tone)
                                    <option value="{{ $tone }}" @selected($currentTone === $tone)>{{ ucwords(str_replace('-', ' ', $tone)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Languages</label>
                            <input type="text" name="bot_languages" class="form-control" value="{{ old('bot_languages', $settings['bot.languages'] ?? '') }}" placeholder="en,ur,ar,hi">
                            <small class="text-muted">Comma-separated language codes the bot can reply in.</small>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="bot_enabled" value="1" id="bot-enabled" @checked(old('bot_enabled', ($settings['bot.enabled'] ?? '0') === '1'))>
                            <label class="form-check-label" for="bot-enabled">Bot enabled</label>
                            <div class="form-text mb-0">Master switch — turns automatic replies on/off for your WhatsApp number.</div>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="bot_send_products_media" value="1" id="bot-send-media" @checked(old('bot_send_products_media', ($settings['bot.send_products_media'] ?? '1') === '1'))>
                            <label class="form-check-label" for="bot-send-media">Send product media</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="bot_order_enabled" value="1" id="bot-order-enabled" @checked(old('bot_order_enabled', ($settings['bot.order_enabled'] ?? '1') === '1'))>
                            <label class="form-check-label" for="bot-order-enabled">Allow in-chat ordering</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Conversation Flow</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Greeting</label>
                            <textarea name="bot_greeting" class="form-control" rows="3" placeholder="Hi! How can I help you today?">{{ old('bot_greeting', $settings['bot.greeting'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fallback Message</label>
                            <textarea name="bot_fallback_message" class="form-control" rows="3" placeholder="Sorry, I did not understand that. Let me connect you with a team member.">{{ old('bot_fallback_message', $settings['bot.fallback_message'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Handoff Number</label>
                            <input type="text" name="bot_handoff_number" class="form-control" value="{{ old('bot_handoff_number', $settings['bot.handoff_number'] ?? '') }}" placeholder="+357...">
                            <small class="text-muted">WhatsApp number the bot hands over to when a human is needed.</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Escalation Keywords</label>
                            <input type="text" name="bot_escalation_keywords" class="form-control" value="{{ old('bot_escalation_keywords', $settings['bot.escalation_keywords'] ?? '') }}" placeholder="human, agent, support">
                            <small class="text-muted">Comma-separated words that instantly route the chat to your support team.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">AI Brain (optional)</h5></div>
                    <div class="card-body">
                        <p class="text-muted font-size-13">Add an AI provider key for natural conversations in multiple languages. Works with OpenAI and any OpenAI-compatible API. Without a key, the bot uses keyword matching.</p>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="bot_ai_enabled" value="1" id="bot-ai-enabled" @checked(old('bot_ai_enabled', ($settings['bot.ai_enabled'] ?? '0') === '1'))>
                            <label class="form-check-label" for="bot-ai-enabled">Use AI replies</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Base URL</label>
                            <input type="text" name="bot_ai_base_url" class="form-control" value="{{ old('bot_ai_base_url', $settings['bot.ai_base_url'] ?? 'https://api.openai.com/v1') }}" placeholder="https://api.openai.com/v1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="bot_ai_model" class="form-control" value="{{ old('bot_ai_model', $settings['bot.ai_model'] ?? 'gpt-4o-mini') }}" placeholder="gpt-4o-mini">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">API Key</label>
                            <input type="password" name="bot_ai_api_key" class="form-control" autocomplete="new-password" placeholder="{{ $hasAiKey ? '•••••••• (saved — leave blank to keep)' : 'sk-...' }}">
                            <small class="text-muted">Stored encrypted. Paste your key when you are ready — the bot picks it up immediately.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">Order Status Messages (WhatsApp)</h5>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="kitchen_ask_payment" value="1" id="kitchen-ask-payment" @checked(old('kitchen_ask_payment', ($settings['kitchen.ask_payment'] ?? '1') === '1'))>
                            <label class="form-check-label" for="kitchen-ask-payment">Ask payment method after order</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted font-size-13">Sent to the customer automatically when the kitchen updates the order. Placeholders: <code>{name}</code>, <code>{order_no}</code>, <code>{total}</code>, <code>{payment_link}</code>, <code>{items}</code>.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment method question</label>
                                <textarea name="kitchen_msg_payment_question" class="form-control" rows="2">{{ old('kitchen_msg_payment_question', $settings['kitchen.msg_payment_question'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Order confirmed</label>
                                <textarea name="kitchen_msg_confirmed" class="form-control" rows="2">{{ old('kitchen_msg_confirmed', $settings['kitchen.msg_confirmed'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preparing</label>
                                <textarea name="kitchen_msg_preparing" class="form-control" rows="2">{{ old('kitchen_msg_preparing', $settings['kitchen.msg_preparing'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ready</label>
                                <textarea name="kitchen_msg_ready" class="form-control" rows="2">{{ old('kitchen_msg_ready', $settings['kitchen.msg_ready'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Out for delivery</label>
                                <textarea name="kitchen_msg_dispatched" class="form-control" rows="2">{{ old('kitchen_msg_dispatched', $settings['kitchen.msg_dispatched'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivered (invoice is attached)</label>
                                <textarea name="kitchen_msg_delivered" class="form-control" rows="2">{{ old('kitchen_msg_delivered', $settings['kitchen.msg_delivered'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cancelled</label>
                                <textarea name="kitchen_msg_cancelled" class="form-control" rows="2">{{ old('kitchen_msg_cancelled', $settings['kitchen.msg_cancelled'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cash on delivery reply</label>
                                <textarea name="kitchen_msg_payment_cod" class="form-control" rows="2">{{ old('kitchen_msg_payment_cod', $settings['kitchen.msg_payment_cod'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Online payment reply</label>
                                <textarea name="kitchen_msg_payment_online" class="form-control" rows="2">{{ old('kitchen_msg_payment_online', $settings['kitchen.msg_payment_online'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">System Instructions</h5></div>
                    <div class="card-body">
                        <textarea name="bot_system_instructions" class="form-control" rows="8" placeholder="You are the sales assistant for ...">{{ old('bot_system_instructions', $settings['bot.system_instructions'] ?? '') }}</textarea>
                        <small class="text-muted">Describe how the bot should qualify leads, recommend products and collect order details.</small>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Bot Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
