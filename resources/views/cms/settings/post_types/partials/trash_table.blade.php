<div class="card-body table-responsive">
    <table class="table table-striped align-middle mb-0 w-100">
        <thead>
            <tr>
                <th scope="col">{{ __('ID') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Deleted at') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postTypes as $postType)
                <tr>
                    <td>{{ $postType->id }}</td>
                    <th scope="row"><code>{{ $postType->type }}</code></th>
                    <td>{{ $postType->deleted_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <form class="d-inline" method="POST" data-crud-form data-loading-region="tr"
                              data-loading-text="{{ __('Recovering…') }}"
                              action="{{ route($adminRoutePrefix.'settings.post-types.recover', $postType) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">{{ __('Recover') }}</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-danger"
                                data-coreui-toggle="modal" data-coreui-target="#destroy-post" data-post-delete
                                data-delete-url="{{ route($adminRoutePrefix.'settings.post-types.force_delete', $postType) }}"
                                data-post-title="{{ $postType->displayName() }}">
                            {{ __('Destroy') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('No deleted post types found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($postTypes->hasPages())
    <div class="card-footer">
        {{ $postTypes->onEachSide(2)->links('pagination::bootstrap-5') }}
    </div>
@endif
