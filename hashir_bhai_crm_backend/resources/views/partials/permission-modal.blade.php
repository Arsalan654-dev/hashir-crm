<div class="modal fade" id="cds-permission-modal" tabindex="-1" aria-labelledby="cds-permission-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body text-center p-5">
                <div class="avatar-lg mx-auto mb-4">
                    <span class="avatar-title bg-danger-subtle text-danger rounded-circle font-size-42">
                        <i class="bx bx-lock-alt"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-danger mb-2">403</h3>
                <h5 class="mb-2" id="cds-permission-modal-label">You cannot access this</h5>
                <p class="text-muted mb-4" data-permission-message>
                    Your role does not have permission to view this page or perform this action.
                    Please contact your administrator to request access.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url('/') }}" class="btn btn-primary"><i class="bx bx-home-alt me-1"></i> Back to Dashboard</a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
