@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">{{ __($postType->type) }}</h1>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'posts.trash', ['type' => $postType->type]) }}">
                    {{ __('Trash') }}
                </a>
                <a class="btn btn-primary" href="{{ route($adminRoutePrefix.'posts.create', ['type' => $postType->type]) }}">
                    {{ __('Add') }}
                </a>
            </div>
        </div>
        <div data-overview data-overview-url="{{ request()->fullUrl() }}"
             data-loading-text="{{ __('Loading…') }}"
             data-error-text="{{ __('The overview could not be loaded. Please try again.') }}">
            <div data-overview-status role="status" aria-live="polite" class="mb-2" hidden></div>
            <div data-overview-error role="alert" class="alert alert-danger" hidden>
                <span data-overview-error-message></span>
                <button type="button" data-overview-retry class="btn btn-sm btn-outline-danger ms-2">{{ __('Retry') }}</button>
            </div>
            <div data-overview-content>
                @include($laravelAdminPackage.'::cms.posts.partials.index_table')
            </div>
        </div>
    </div>
    @include($laravelAdminPackage.'::cms.posts.partials.delete_confirmation')
@endsection
