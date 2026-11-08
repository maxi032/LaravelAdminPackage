<div class="card-body table-responsive">
    <table class="table table-striped align-middle mb-0 w-100">
        <thead>
            <tr>
                <th scope="col">{{ __('ID') }}</th>
                <th scope="col">{{ __('Label') }}</th>
                <th scope="col">{{ __('Key') }}</th>
                <th scope="col">{{ __('Deleted at') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($options as $option)
                <tr>
                    <td>{{ $option->id }}</td>
                    <th scope="row">{{ $option->label }}</th>
                    <td><code>{{ $option->key }}</code></td>
                    <td>{{ $option->deleted_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <form class="d-inline" method="POST" data-crud-form data-loading-region="tr"
                              data-loading-text="{{ __('Recovering…') }}"
                              action="{{ route($adminRoutePrefix.'settings.post-options.recover', $option) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">{{ __('Recover') }}</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-danger"
                                data-coreui-toggle="modal" data-coreui-target="#destroy-post" data-post-delete
                                data-delete-url="{{ route($adminRoutePrefix.'settings.post-options.force_delete', $option) }}"
                                data-post-title="{{ $option->label }}">
                            {{ __('Destroy') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('No deleted post options found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($options->hasPages())
    <div class="card-footer">
        {{ $options->onEachSide(2)->links('pagination::bootstrap-5') }}
    </div>
@endif
