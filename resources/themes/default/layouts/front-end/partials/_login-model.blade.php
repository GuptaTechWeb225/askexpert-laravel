<div class="modal fade" id="authRequiredModal" tabindex="-1" aria-labelledby="authRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authRequiredModalLabel">Login or Sign Up Required</h5>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">Login or sign up required to connect with expert and proceed with payment.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('customer.auth.login') }}?return_url={{ urlencode(url()->current()) }}" class="btn btn-primary">
                    Login
                </a> <button type="button" class="btn btn-outline-secondary" id="cancelAuthFlow">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>