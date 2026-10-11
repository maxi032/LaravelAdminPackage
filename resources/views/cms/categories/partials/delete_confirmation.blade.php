<div class="modal fade" id="delete-category-{{ $category->id }}" tabindex="-1" aria-labelledby="delete-category-title-{{ $category->id }}" aria-describedby="delete-category-message-{{ $category->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="delete-category-title-{{ $category->id }}">{{ __('Confirm delete') }}</h2>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body" id="delete-category-message-{{ $category->id }}">
                {{ __('Are you sure that you want to delete this record?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
                <form data-crud-form data-loading-region=".modal-content" data-loading-text="{{ __('Deleting…') }}" method="POST" action="{{ route($adminRoutePrefix.'categories.destroy', ['category' => $category]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-success">{{ __('Confirm delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
