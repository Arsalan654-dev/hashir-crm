@extends('layouts.app')

@section('title', 'WhatsApp API Settings')
@section('page-title', 'WhatsApp API Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/whatsapp') }}">WhatsApp</a></li>
    <li class="breadcrumb-item active">API Settings</li>
@endsection

@section('page-actions')
    <a href="{{ url('/whatsapp') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Inbox</a>
    @if ($account->exists)
        <form action="{{ url('/whatsapp/settings/test') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-primary btn-sm"><i class="bx bx-pulse me-1"></i> Test connection</button>
        </form>
    @endif
@endsection

@php
    $statusColor = match ($account->status) {
        'active' => 'success',
        'error' => 'danger',
        default => 'secondary',
    };
@endphp

@section('content')
    @if ($account->last_error)
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bx bx-error-circle font-size-18"></i>
            <span class="flex-grow-1">{{ $account->last_error }}</span>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0"><i class="bx bxl-whatsapp me-1"></i> WhatsApp provider credentials</h5>
                        <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                            {{ ucfirst($account->status ?? 'not saved') }}
                        </span>
                    </div>

                    <form action="{{ url('/whatsapp/settings') }}" method="POST">
                        @csrf
                        @if ($account->exists)
                            <input type="hidden" name="account_id" value="{{ $account->id }}">
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Provider <span class="text-danger">*</span></label>
                                <select name="provider" class="form-select" id="cds-wa-provider">
                                    @foreach ($providers as $value => $label)
                                        <option value="{{ $value }}" @selected(old('provider', $account->provider ?? 'meta_cloud') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">WhatChimp handles the WhatsApp complexity — get your API key from app.whatchimp.com → API Developer Console.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $account->name) }}" class="form-control" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display phone number</label>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $account->phone_number) }}" class="form-control" maxlength="50" placeholder="+357 99 123456">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone number ID <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number_id" value="{{ old('phone_number_id', $account->phone_number_id) }}" class="form-control" maxlength="100">
                                <div class="form-text">For WhatChimp: find this in your WhatChimp account settings. For Meta: from the Cloud API dashboard.</div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="mb-1"><i class="bx bx-key me-1"></i> WhatChimp credentials</h6>
                                <p class="text-muted font-size-13 mb-0">Only needed when provider is WhatChimp. Paste these when your account is ready — the integration activates instantly.</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatChimp API key (apiToken)</label>
                                <input type="password" name="api_key" value="" class="form-control" maxlength="2000" autocomplete="new-password">
                                <div class="form-text">
                                    @if (filled($account->api_key))
                                        A key is already saved — leave blank to keep it.
                                    @else
                                        From app.whatchimp.com → API Developer Console.
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">API base URL</label>
                                <input type="text" name="base_url" value="{{ old('base_url', $account->base_url) }}" class="form-control" maxlength="255" placeholder="https://app.whatchimp.com/api/v1">
                                <div class="form-text">Leave blank to use the default WhatChimp API URL.</div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="mb-1"><i class="bx bxl-meta me-1"></i> Meta Cloud API credentials</h6>
                                <p class="text-muted font-size-13 mb-0">Only needed when provider is Meta Cloud API.</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Business Account ID</label>
                                <input type="text" name="business_account_id" value="{{ old('business_account_id', $account->business_account_id) }}" class="form-control" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WABA ID</label>
                                <input type="text" name="waba_id" value="{{ old('waba_id', $account->waba_id) }}" class="form-control" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">API version</label>
                                <input type="text" name="api_version" value="{{ old('api_version', $account->api_version ?? 'v21.0') }}" class="form-control" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Meta app ID</label>
                                <input type="text" name="app_id" value="{{ old('app_id', $account->app_id) }}" class="form-control" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verify token</label>
                                <input type="text" name="verify_token" value="{{ old('verify_token', $account->verify_token) }}" class="form-control" maxlength="255">
                                <div class="form-text">Paste this same value into the Meta webhook configuration.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Access token</label>
                                <input type="password" name="access_token" value="" class="form-control" maxlength="2000" autocomplete="new-password">
                                <div class="form-text">
                                    @if (filled($account->access_token))
                                        A token is already saved — leave blank to keep it.
                                    @else
                                        Use a permanent access token from Meta.
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">App secret</label>
                                <input type="password" name="app_secret" value="" class="form-control" maxlength="255" autocomplete="new-password">
                                <div class="form-text">
                                    @if (filled($account->app_secret))
                                        A secret is already saved — leave blank to keep it.
                                    @else
                                        Used to validate incoming webhook signatures.
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="cds-wa-default" @checked(old('is_default', $account->is_default))>
                                    <label class="form-check-label" for="cds-wa-default">Use as the default WhatsApp account</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="bot_enabled" value="1" id="cds-wa-bot" @checked(old('bot_enabled', $account->bot_enabled))>
                                    <label class="form-check-label" for="cds-wa-bot">WhatsApp bot enabled (auto-reply to incoming messages)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save settings</button>
                            @if ($account->exists)
                                <button class="btn btn-outline-primary" type="submit" formaction="{{ url('/whatsapp/settings/test') }}"><i class="bx bx-pulse me-1"></i> Test connection</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if ($accounts->isNotEmpty())
                <div class="card mt-3 mb-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Saved accounts</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone number ID</th>
                                        <th>API version</th>
                                        <th>Status</th>
                                        <th>Default</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $savedAccount)
                                        <tr>
                                            <td class="fw-medium">{{ $savedAccount->name }}</td>
                                            <td><span class="text-muted font-size-13">{{ $savedAccount->phone_number_id ?? '—' }}</span></td>
                                            <td>{{ $savedAccount->api_version ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $savedAccount->status === 'active' ? 'success' : ($savedAccount->status === 'error' ? 'danger' : 'secondary') }}-subtle text-{{ $savedAccount->status === 'active' ? 'success' : ($savedAccount->status === 'error' ? 'danger' : 'secondary') }}">
                                                    {{ ucfirst($savedAccount->status ?? 'inactive') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($savedAccount->is_default)
                                                    <i class="bx bx-check-circle text-success"></i>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-4">
            <div class="card mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bx bx-link me-1"></i> Webhook endpoints</h5>

                    <label class="form-label">Webhook callback URL (Meta Cloud API)</label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" value="{{ $webhookUrl }}" readonly>
                        <button class="btn btn-outline-primary" type="button" data-copy="{{ $webhookUrl }}"><i class="bx bx-copy"></i></button>
                    </div>

                    <label class="form-label">Webhook URL (WhatChimp — paste in Bot Manager &rarr; Configuration)</label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" value="{{ $whatchimpWebhookUrl }}" readonly>
                        <button class="btn btn-outline-primary" type="button" data-copy="{{ $whatchimpWebhookUrl }}"><i class="bx bx-copy"></i></button>
                    </div>

                    <label class="form-label">Bot lead endpoint</label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" value="{{ $botEndpoint }}" readonly>
                        <button class="btn btn-outline-primary" type="button" data-copy="{{ $botEndpoint }}"><i class="bx bx-copy"></i></button>
                    </div>

                    <label class="form-label">Bot knowledge endpoint</label>
                    <div class="input-group input-group-sm mb-0">
                        <input type="text" class="form-control" value="{{ $knowledgeEndpoint }}" readonly>
                        <button class="btn btn-outline-primary" type="button" data-copy="{{ $knowledgeEndpoint }}"><i class="bx bx-copy"></i></button>
                    </div>
                </div>
            </div>

            <div class="card mt-3 mb-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bx bx-list-ol me-1"></i> Setup instructions</h5>
                    <ol class="mb-0 ps-3 font-size-13 text-muted">
                        <li class="mb-2"><strong>WhatChimp:</strong> in <span class="text-body">app.whatchimp.com &rarr; API Developer Console</span>, copy your <strong>API key</strong> and <strong>Phone number ID</strong>. Select provider <strong>WhatChimp</strong>, paste both above and save.</li>
                        <li class="mb-2"><strong>WhatChimp:</strong> in <strong>Bot Manager &rarr; Configuration &rarr; Webhook URL</strong>, paste the WhatChimp webhook URL above and enable incoming messages.</li>
                        <li class="mb-2"><strong>Meta Cloud API:</strong> create a Meta app at <span class="text-body">developers.facebook.com</span>, add the <strong>WhatsApp</strong> product.</li>
                        <li class="mb-2"><strong>Meta:</strong> open <strong>WhatsApp &rarr; API Setup</strong>, copy the <strong>Phone number ID</strong> and a <strong>permanent access token</strong>, then paste with Business/WABA IDs and a verify token.</li>
                        <li class="mb-2"><strong>Meta:</strong> in <strong>WhatsApp &rarr; Configuration</strong>, add the Meta webhook callback URL above, enter the same verify token and subscribe to the <code>messages</code> field.</li>
                        <li class="mb-2">Enable <strong>WhatsApp bot</strong> in the form above (or in <a href="{{ url('/bot/settings') }}">Bot Settings</a>) to auto-reply to customers.</li>
                        <li>Click <strong>Test connection</strong> to verify the credentials.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy]').forEach(function (button) {
            button.addEventListener('click', function () {
                var value = button.getAttribute('data-copy');
                var done = function () {
                    var original = button.innerHTML;
                    button.innerHTML = '<i class="bx bx-check"></i>';
                    window.setTimeout(function () { button.innerHTML = original; }, 1500);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(value).then(done).catch(function () {});
                    return;
                }
                var input = document.createElement('input');
                input.value = value;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                done();
            });
        });
    </script>
@endpush
