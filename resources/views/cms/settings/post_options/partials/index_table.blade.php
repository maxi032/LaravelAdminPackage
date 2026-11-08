<div class="card-body table-responsive">
    <table class="table table-striped align-middle mb-0 w-100">
        <thead>
            <tr>
                <th scope="col">{{ __('ID') }}</th>
                <th scope="col">{{ __('Label') }}</th>
                <th scope="col">{{ __('Key') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Languages') }}</th>
                <th scope="col">{{ __('Sort order') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($options as $option)
                <tr>
                    <td>{{ $option->id }}</td>
                    <th scope="row">{{ $option->label }}</th>
                    <td><code>{{ $option->key }}</code></td>
                    <td>{{ __(ucfirst($option->type)) }}</td>
                    <td>{{ $option->is_translatable ? __('Per language') : __('Shared') }}</td>
                    <td>{{ $option->sort_order }}</td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   aria-label="{{ __('Status for :option', ['option' => $option->label]) }}"
                                   @checked($option->enabled)
                                   data-status-toggle data-id="{{ $option->id }}"
                                   data-status-url="{{ route($adminRoutePrefix.'settings.post-options.change-status', $option) }}"
                                   data-loading-text="{{ __('Updating…') }}"
                                   data-error-text="{{ __('The post option status could not be updated. Please try again.') }}">
                        </div>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-primary"
                           href="{{ route($adminRoutePrefix.'settings.post-options.edit', $option) }}">
                            <span class="cil-notes" aria-hidden="true"></span> {{ __('Edit') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-warning"
                                data-coreui-toggle="modal" data-coreui-target="#delete-post" data-post-delete
                                data-delete-url="{{ route($adminRoutePrefix.'settings.post-options.destroy', $option) }}"
                                data-post-title="{{ $option->label }}">
                            <span class="cil-trash" aria-hidden="true"></span> {{ __('Delete') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">{{ __('No post options found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($options->hasPages())
    <div class="card-footer">
        {{ $options->onEachSide(2)->links('pagination::bootstrap-5') }}
    </div>
@endif
