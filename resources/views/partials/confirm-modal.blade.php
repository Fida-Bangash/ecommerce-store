{{-- Reusable confirmation modal. Any form with class="js-confirm-form"
     and a data-confirm-message attribute will trigger this modal instead
     of the native browser confirm() popup. --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <svg width="48" height="48" viewBox="0 0 24 24" class="text-danger">
                        <path fill="currentColor" d="M12 2 1 21h22Zm0 6a1 1 0 0 1 1 1v5a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1Zm0 9.5a1.25 1.25 0 1 1 0 2.5a1.25 1.25 0 0 1 0-2.5Z"/>
                    </svg>
                </div>
                <p id="confirmModalMessage" class="mb-4 fs-6">Are you sure?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmModalAccept" class="btn btn-danger px-4">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('confirmModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        const modal = new bootstrap.Modal(modalEl);
        const messageEl = document.getElementById('confirmModalMessage');
        const acceptBtn = document.getElementById('confirmModalAccept');
        let pendingForm = null;

        document.querySelectorAll('.js-confirm-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                pendingForm = form;
                messageEl.textContent = form.dataset.confirmMessage || 'Are you sure?';
                modal.show();
            });
        });

        acceptBtn.addEventListener('click', function () {
            modal.hide();
            if (pendingForm) {
                pendingForm.submit();
                pendingForm = null;
            }
        });
    });
</script>
