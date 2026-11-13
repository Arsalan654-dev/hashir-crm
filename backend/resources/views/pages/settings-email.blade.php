@extends('layouts.app')

@section('title', 'Email Settings')
@section('page-title', 'Email / SMTP Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/settings') }}">Settings</a></li>
    <li class="breadcrumb-item active">Email / SMTP</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">SMTP / Mail Server</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.email.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="mail[enabled]" value="1" id="mail-enabled" @checked($settings['enabled'])>
                                    <label class="form-check-label" for="mail-enabled">Use these settings for outgoing email (welcome emails, notifications)</label>
                                </div>
                                <div class="form-text">If disabled, the system uses the mail settings from the server configuration file.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Mailer</label>
                                <select name="mail[mailer]" class="form-select">
                                    <option value="smtp" @selected($settings['mailer'] === 'smtp')>SMTP</option>
                                    <option value="log" @selected($settings['mailer'] === 'log')>Log only (testing)</option>
                                    <option value="array" @selected($settings['mailer'] === 'array')>Array (testing)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="mail[host]" class="form-control" value="{{ $settings['host'] }}" placeholder="smtp.hostinger.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Port</label>
                                <input type="number" name="mail[port]" class="form-control" value="{{ $settings['port'] }}" placeholder="587">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Encryption</label>
                                <select name="mail[encryption]" class="form-select">
                                    <option value="tls" @selected($settings['encryption'] === 'tls')>TLS (587)</option>
                                    <option value="ssl" @selected($settings['encryption'] === 'ssl')>SSL (465)</option>
                                    <option value="none" @selected($settings['encryption'] === 'none')>None</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Username</label>
                                <input type="text" name="mail[username]" class="form-control" value="{{ $settings['username'] }}" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password</label>
                                <input type="password" name="mail[password]" class="form-control" autocomplete="new-password" placeholder="{{ $settings['has_password'] ? '•••••••• (saved — leave blank to keep)' : 'Enter password' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Address</label>
                                <input type="email" name="mail[from_address]" class="form-control" value="{{ $settings['from_address'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Name</label>
                                <input type="text" name="mail[from_name]" class="form-control" value="{{ $settings['from_name'] }}">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3"><i class="bx bx-credit-card me-1"></i> Billing Instructions (shown on client billing page)</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Bank / Payment Details</label>
                                <textarea name="billing[bank_details]" class="form-control" rows="3" placeholder="Bank: ...&#10;Account: ...&#10;IBAN: ...">{{ $settings['bank_details'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Instructions</label>
                                <textarea name="billing[instructions]" class="form-control" rows="2" placeholder="Please transfer the amount and submit the receipt...">{{ $settings['billing_instructions'] }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Send Test Email</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted font-size-13">Save your settings first, then send a test email to verify everything works.</p>
                    <form action="{{ route('settings.email.test') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Send test email to</label>
                            <input type="email" name="test_email" class="form-control" value="{{ auth()->user()->email }}" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary w-100"><i class="bx bx-paper-plane me-1"></i> Send Test</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Common SMTP Examples</h5></div>
                <div class="card-body font-size-13 text-muted">
                    <p class="mb-2"><strong>Hostinger:</strong> host <code>smtp.hostinger.com</code>, port <code>465</code> with SSL, or <code>587</code> with TLS.</p>
                    <p class="mb-0"><strong>Gmail:</strong> host <code>smtp.gmail.com</code>, port <code>587</code> with TLS, username is full Gmail address, password is an App Password.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
