@php
    $impersonatorId = session('impersonator_id');
    $impersonator = $impersonatorId !== null ? \App\Models\User::query()->find($impersonatorId) : null;
@endphp
@if ($impersonator !== null)
    <div class="alert alert-warning d-flex align-items-center justify-content-between mb-0 rounded-0 py-2 px-3" style="position: relative; z-index: 1035;">
        <div class="font-size-13">
            <i class="bx bx-user-check me-1"></i>
            You are viewing the CRM as <strong>{{ auth()->user()->name }}</strong> (impersonated by {{ $impersonator->name }}).
        </div>
        <form action="{{ route('impersonation.stop') }}" method="POST" class="mb-0">
            @csrf
            <button class="btn btn-sm btn-warning"><i class="bx bx-log-out-circle me-1"></i> Return to my account</button>
        </form>
    </div>
@endif
