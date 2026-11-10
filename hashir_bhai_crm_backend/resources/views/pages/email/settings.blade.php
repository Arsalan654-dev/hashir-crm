@extends('layouts.app')

@section('title', 'Gmail Connection')
@section('page-title', 'Gmail Connection')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('/settings/integrations') }}">Settings</a></li>
    <li class="breadcrumb-item active">Gmail Connection</li>
@endsection

@section('page-actions')
    @if ($configured)
        <a href="{{ url('/gmail/connect') }}" class="btn btn-primary btn-sm"><i class="bx bxl-google me-1"></i> Connect Gmail</a>
        <form action="{{ url('/gmail/sync') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-primary btn-sm"><i class="bx bx-refresh me-1"></i> Sync inbox</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Google OAuth Credentials</h5></div>
                <div class="card-body">
                    <form action="{{ url('/gmail/settings') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', $clientId) }}" placeholder="xxxxxxxx.apps.googleusercontent.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client Secret</label>
                            <input type="text" name="google_client_secret" class="form-control" value="" placeholder="{{ $hasSecret ? 'Saved — leave blank to keep' : 'GOCSPX-...' }}">
                            @if ($hasSecret)
                                <small class="text-success"><i class="bx bx-check-circle me-1"></i>A client secret is already saved. Leave this blank to keep it.</small>
                            @else
                                <small class="text-muted">Paste the client secret generated in Google Cloud Console.</small>
                            @endif
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Redirect URI</label>
                            <input type="text" name="google_redirect_uri" class="form-control" value="{{ old('google_redirect_uri', $redirectUri) }}">
                        </div>
                        <div class="border-top mt-3 pt-3 text-end">
                            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Credentials</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Connection Status</h5></div>
                <div class="card-body">
                    @if ($configured)
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-success-subtle text-success me-2"><i class="bx bx-check me-1"></i>Configured</span>
                            <span class="text-muted font-size-13">{{ number_format($syncedCount) }} emails synced from Gmail</span>
                        </div>
                        <a href="{{ url('/gmail/connect') }}" class="btn btn-primary w-100 mb-2"><i class="bx bxl-google me-1"></i> Connect Gmail</a>
                        <form action="{{ url('/gmail/sync') }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-primary w-100"><i class="bx bx-refresh me-1"></i> Sync inbox</button>
                        </form>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bx bx-error-circle me-1"></i>
                            Add the <strong>Client ID</strong> and <strong>Client secret</strong>, save them, then connect your Gmail account.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header"><h5 class="card-title mb-0">Setup Guide</h5></div>
                <div class="card-body">
                    <ol class="ps-3 mb-0 font-size-13 text-muted">
                        <li class="mb-2">Open the <strong>Google Cloud Console</strong> and create or select a project.</li>
                        <li class="mb-2">Configure the <strong>OAuth consent screen</strong> (External) and add your Gmail address as a test user.</li>
                        <li class="mb-2">Go to <strong>Credentials &rarr; Create Credentials &rarr; OAuth client ID</strong> and choose <strong>Web application</strong>.</li>
                        <li class="mb-2">Add this <strong>Authorised redirect URI</strong>:
                            <div class="input-group input-group-sm mt-1">
                                <input type="text" class="form-control" id="gmailRedirectUri" value="{{ $redirectUri }}" readonly>
                                <button type="button" class="btn btn-outline-secondary" data-copy-target="gmailRedirectUri" title="Copy"><i class="bx bx-copy"></i></button>
                            </div>
                        </li>
                        <li class="mb-2">Enable the <strong>Gmail API</strong> for the project.</li>
                        <li>Copy the generated <strong>Client ID</strong> and <strong>Client secret</strong> into the form and save.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Connected Accounts</h5>
            <span class="text-muted font-size-13">{{ $accounts->count() }} account(s)</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Email</th>
                            <th>Last Sync</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bxl-gmail font-size-20 text-danger me-2"></i>
                                        <div>
                                            <h6 class="mb-0 font-size-14">{{ $account->email }}</h6>
                                            @if ($account->last_error)
                                                <span class="text-danger font-size-12">{{ \Illuminate\Support\Str::limit($account->last_error, 60) }}</span>
                                            @else
                                                <span class="text-muted font-size-12">{{ $account->display_name ?? 'Gmail mailbox' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>
                                    @if ($account->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end cds-table-actions">
                                    <form action="{{ url('/gmail/'.$account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Disconnect {{ $account->email }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-unlink me-1"></i> Disconnect</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No Gmail account connected yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy-target]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-copy-target'));
                if (! input) { return; }
                navigator.clipboard.writeText(input.value);
                button.innerHTML = '<i class="bx bx-check"></i>';
            });
        });
    </script>
@endpush
