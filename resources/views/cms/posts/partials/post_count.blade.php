@if($count > 0)
    <button type="button" class="btn btn-link p-0" popovertarget="{{ $popoverId }}"
            popovertargetaction="show" data-post-count
            aria-label="{{ __('Posts') }}: {{ $count }}">{{ $count }}</button>
    <div id="{{ $popoverId }}" popover class="post-count-popover border rounded shadow p-3"
         data-post-count-url="{{ $postsUrl }}" data-error-text="{{ __('The associated posts could not be loaded.') }}">
        <span role="status">{{ __('Loading…') }}</span>
    </div>
@else
    {{ $count }}
@endif
