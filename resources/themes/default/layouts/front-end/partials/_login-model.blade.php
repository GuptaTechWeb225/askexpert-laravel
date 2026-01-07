<!-- Email Modal for Guest Users -->
<div class="modal fade" id="guestEmailModal" tabindex="-1" aria-labelledby="guestEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="guestEmailModalLabel">Continue with Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please provide your email to proceed with your question:</p>
                <form id="guestEmailForm">
                    <div class="mb-3">
                        <input type="email" class="form-control" id="guestEmail" placeholder="your@email.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Process</button>
                </form>
            </div>
        </div>
    </div>
</div>