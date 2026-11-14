<div class="card-body table-responsive">
    <table class="table table-striped align-middle mb-0 w-100">
        <thead>
            <tr>
                <th scope="col">{{ __('ID') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Posts') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postTypes as $postType)
                <tr>
                    <td>{{ $postType->id }}</td>
                    <th scope="row"><code>{{ $postType->type }}</code></th>
                    <td>{{ $postType->posts_count }}</td>
                    <td>
                        <div class="form-check form-switch post-type-status-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   aria-label="{{ __('Status for :type', ['type' => $postType->displayName()]) }}"
                                   @checked($postType->status)
                                   @if($postType->posts_count > 0)
                                       disabled title="{{ __('A post type with posts cannot be disabled.') }}"
                                   @else
                                       data-status-toggle data-id="{{ $postType->id }}"
                                       data-status-url="{{ route($adminRoutePrefix.'settings.post-types.change-status', $postType) }}"
                                       data-loading-text="{{ __('Updating…') }}"
                                       data-error-text="{{ __('The post type status could not be updated. Please try again.') }}"
                                   @endif>
                        </div>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-primary"
                           href="{{ route($adminRoutePrefix.'settings.post-types.edit', $postType) }}">
                            <span class="cil-notes" aria-hidden="true"></span> {{ __('Edit') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-warning"
                                @disabled($postType->posts_count > 0)
                                data-coreui-toggle="modal" data-coreui-target="#delete-post" data-post-delete
                                data-delete-url="{{ route($adminRoutePrefix.'settings.post-types.destroy', $postType) }}"
                                data-post-title="{{ $postType->displayName() }}">
                            <span class="cil-trash" aria-hidden="true"></span> {{ __('Delete') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('No post types available.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($postTypes->hasPages())
    <div class="card-footer">
        {{ $postTypes->onEachSide(2)->links('pagination::bootstrap-5') }}
    </div>
@endif
