@php
    $promptUser = auth()->user();
@endphp
@if ($promptUser !== null && \App\Support\AttendancePrompt::shouldPrompt($promptUser))
    <div class="modal fade" id="attendance-prompt-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title"><i class="bx bx-time-five text-primary me-1"></i> Attendance Check-in</h5>
                        <p class="text-muted mb-0 font-size-13">{{ now()->format('l, d M Y — h:i A') }}</p>
                    </div>
                </div>
                <div class="modal-body text-center pt-2">
                    <p class="mb-4">Assalam-o-Alaikum <strong>{{ $promptUser->name }}</strong>! Please mark your attendance to continue.</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <form action="{{ route('attendance.prompt') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="present">
                            <button class="btn btn-success btn-lg px-4">
                                <i class="bx bx-check-circle me-1"></i> Present
                            </button>
                        </form>
                        <form action="{{ route('attendance.prompt') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="leave">
                            <button class="btn btn-outline-warning btn-lg px-4">
                                <i class="bx bx-calendar-x me-1"></i> Leave
                            </button>
                        </form>
                    </div>
                    <form action="{{ route('attendance.prompt') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="action" value="later">
                        <button class="btn btn-link btn-sm text-muted">Remind me later</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('attendance-prompt-modal');
            if (el && window.bootstrap && window.bootstrap.Modal) {
                new window.bootstrap.Modal(el).show();
            }
        });
    </script>
@endif
