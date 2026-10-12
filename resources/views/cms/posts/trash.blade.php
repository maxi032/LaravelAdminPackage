@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">{{ __(':type trash', ['type' => __($postType->type)]) }}</h1>
            <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $postType->type]) }}">{{ __('View posts') }}</a>
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
                @include($laravelAdminPackage.'::cms.posts.partials.trash_table')
            </div>
        </div>
    </div>
    @include($laravelAdminPackage.'::cms.posts.partials.destroy_confirmation')
@endsection
