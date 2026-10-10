<div class="modal fade" id="delete-post-{{ $post->id }}" tabindex="-1" aria-labelledby="delete-post-title-{{ $post->id }}" aria-describedby="delete-post-message-{{ $post->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="delete-post-title-{{ $post->id }}">{{ __('Confirm delete') }}</h2>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body" id="delete-post-message-{{ $post->id }}">
                {{ __('Are you sure that you want to delete this record?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">{{ __('Cancel') }}</button>
                <form method="POST" action="{{ route($adminRoutePrefix.'posts.destroy', ['post' => $post]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-success">{{ __('Confirm delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
