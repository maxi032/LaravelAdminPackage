<div class="modal fade" id="delete-post" data-post-delete-modal tabindex="-1" aria-labelledby="delete-post-title" aria-describedby="delete-post-message" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="delete-post-title">{{ __('Confirm delete') }}</h2>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body" id="delete-post-message">
                <p>{{ __('Are you sure that you want to delete this record?') }}</p>
                <p class="fw-semibold mb-0" data-post-delete-title></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
                <form data-crud-form data-loading-region=".modal-content" data-loading-text="{{ __('Deleting…') }}" method="POST" data-post-delete-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-success" disabled>{{ __('Confirm delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
