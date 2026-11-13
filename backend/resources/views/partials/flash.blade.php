@if (session('status') || $errors->any())
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        @if (session('status'))
            <div class="toast show align-items-center text-bg-success border-0 mb-2" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bx bx-check-circle me-1"></i>{{ session('status') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="toast show align-items-center text-bg-danger border-0 mb-2" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bx bx-error-circle me-1"></i>
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
@endif
