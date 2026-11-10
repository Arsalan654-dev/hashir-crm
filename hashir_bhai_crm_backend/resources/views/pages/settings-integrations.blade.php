@extends('layouts.app')

@section('title', 'Integrations & API Keys')
@section('page-title', 'Integrations & API Keys')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/settings') }}">Settings</a></li>
    <li class="breadcrumb-item active">Integrations</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <form action="{{ url('/settings/integrations') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Google / Gmail API</h5></div>
                    <div class="card-body">
                        <p class="text-muted font-size-13">
                            Create OAuth credentials in Google Cloud Console, then paste them here.
                            After saving, open <a href="{{ url('/gmail/settings') }}">Gmail Connection</a> to connect the mailbox.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', $settings['integrations.google_client_id'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client Secret</label>
                            <input type="text" name="google_client_secret" class="form-control" value="{{ old('google_client_secret', $settings['integrations.google_client_secret'] ?? '') }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Redirect URI</label>
                            <input type="text" name="google_redirect_uri" class="form-control" value="{{ old('google_redirect_uri', $settings['integrations.google_redirect_uri'] ?? url('/gmail/callback')) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">n8n / WhatsApp Bot</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">n8n Webhook URL</label>
                            <input type="text" name="n8n_webhook_url" class="form-control" value="{{ old('n8n_webhook_url', $settings['integrations.n8n_webhook_url'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">n8n API Key</label>
                            <input type="text" name="n8n_api_key" class="form-control" value="{{ old('n8n_api_key', $settings['integrations.n8n_api_key'] ?? '') }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Bot Shared Secret</label>
                            <input type="text" name="bot_shared_secret" class="form-control" value="{{ old('bot_shared_secret', $settings['integrations.bot_shared_secret'] ?? '') }}">
                            <small class="text-muted">
                                Your n8n bot must send this in the <code>X-Bot-Secret</code> header when posting to
                                <code>{{ url('/api/bot/lead') }}</code>.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Maps</h5></div>
                    <div class="card-body">
                        <label class="form-label">Google Maps API Key</label>
                        <input type="text" name="maps_api_key" class="form-control" value="{{ old('maps_api_key', $settings['integrations.maps_api_key'] ?? '') }}">
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Integrations</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">WhatsApp Cloud API</h5></div>
                <div class="card-body">
                    @if ($whatsappAccount)
                        <p class="mb-2"><strong>{{ $whatsappAccount->name }}</strong></p>
                        <p class="mb-1 text-muted font-size-13">Phone: {{ $whatsappAccount->phone_number ?? '—' }}</p>
                        <p class="mb-3">
                            <span class="badge bg-{{ $whatsappAccount->status === 'active' ? 'success' : 'warning' }}-subtle text-{{ $whatsappAccount->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($whatsappAccount->status) }}
                            </span>
                        </p>
                    @else
                        <p class="text-muted font-size-13">No WhatsApp account configured yet.</p>
                    @endif
                    <a href="{{ url('/whatsapp/settings') }}" class="btn btn-success btn-sm">
                        <i class="bx bxl-whatsapp me-1"></i> Configure WhatsApp API
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Webhook Endpoints</h5></div>
                <div class="card-body">
                    <label class="form-label font-size-13">WhatsApp inbound webhook</label>
                    <input type="text" class="form-control mb-3" readonly value="{{ url('/webhooks/whatsapp') }}">
                    <label class="form-label font-size-13">Bot lead intake (n8n)</label>
                    <input type="text" class="form-control mb-3" readonly value="{{ url('/api/bot/lead') }}">
                    <label class="form-label font-size-13">Bot knowledge endpoint</label>
                    <input type="text" class="form-control" readonly value="{{ url('/api/bot/knowledge') }}">
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Connected Mailboxes</h5></div>
                <div class="card-body">
                    @forelse ($gmailAccounts as $account)
                        <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                            <div>
                                <h6 class="mb-0 font-size-14">{{ $account->email }}</h6>
                                <span class="text-muted font-size-12">
                                    Last sync: {{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}
                                </span>
                            </div>
                            <span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}-subtle text-{{ $account->is_active ? 'success' : 'secondary' }}">
                                {{ $account->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted mb-0 font-size-13">No Gmail account connected.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
