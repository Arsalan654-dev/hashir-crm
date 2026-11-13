@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('page-actions')
    <a href="{{ url('/notifications/read-all') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-check-double me-1"></i> Mark all as read</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0"><i class="bx bx-bell me-1"></i> All Notifications</h5>
            @if ($unread > 0)
                <span class="badge bg-danger-subtle text-danger">{{ number_format($unread) }} unread</span>
            @else
                <span class="badge bg-success-subtle text-success">All caught up</span>
            @endif
        </div>
        <div class="card-body p-0">
            @forelse ($notifications as $notification)
                <div class="d-flex align-items-start p-3 border-bottom {{ $notification->read_at ? '' : 'bg-primary-subtle' }}">
                    <div class="avatar-xs me-3">
                        <span class="avatar-title bg-{{ $notification->read_at ? 'secondary' : 'primary' }}-subtle text-{{ $notification->read_at ? 'secondary' : 'primary' }} rounded-circle font-size-16">
                            <i class="bx bx-bell"></i>
                        </span>
                    </div>
                    <div class="flex-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <h6 class="mb-1 font-size-14">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                            <span class="text-muted font-size-12 text-nowrap ms-2">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        @if (filled($notification->data['message'] ?? null))
                            <p class="text-muted mb-2 font-size-13">{{ $notification->data['message'] }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            @if (filled($notification->data['url'] ?? null))
                                <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-link-external me-1"></i> Open</a>
                            @endif
                            @if ($notification->read_at === null)
                                <a href="{{ url('/notifications/'.$notification->id.'/read') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-check me-1"></i> Mark as read</a>
                            @endif
                            <span class="text-muted font-size-12 align-self-center">{{ $notification->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bx bx-bell-off font-size-24 text-muted d-block mb-2"></i>
                    <h5 class="mb-1">No notifications yet</h5>
                    <p class="text-muted mb-0">You will see alerts about sales, leads, and approvals here.</p>
                </div>
            @endforelse
        </div>
        @if ($notifications->hasPages())
            <div class="card-footer">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
