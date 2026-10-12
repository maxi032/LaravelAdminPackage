<div class="modal fade" id="destroy-post" data-post-delete-modal tabindex="-1" aria-labelledby="destroy-post-title" aria-describedby="destroy-post-message" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="destroy-post-title">{{ __('Confirm destroy') }}</h2>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body" id="destroy-post-message">
                <p>{{ __('Are you sure that you want to permanently destroy this record? Its translations and child posts will also be deleted. This cannot be undone.') }}</p>
                <p class="fw-semibold mb-0" data-post-delete-title></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
                <form data-crud-form data-loading-region=".modal-content" data-loading-text="{{ __('Deleting permanently…') }}" method="POST" data-post-delete-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" disabled>{{ __('Confirm destroy') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
