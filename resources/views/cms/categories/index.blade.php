@extends($laravelAdminPackage.'::layouts.admin_layout')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">{{ __(':type categories', ['type' => $categoryType->type === 'faq' ? 'FAQ' : \Illuminate\Support\Str::headline($categoryType->type)]) }}</h1>
            <div class="d-flex gap-2">
                @if($hasPostType)
                <a class="btn btn-outline-primary" href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $categoryType->type]) }}">{{ __('View posts') }}</a>
                @endif
                <a class="btn btn-outline-secondary" href="{{ route($adminRoutePrefix.'categories.trash', ['type' => $categoryType->type]) }}">{{ __('Trash') }}</a>
                <a class="btn btn-primary" href="{{ route($adminRoutePrefix.'categories.create', ['type' => $categoryType->type]) }}">{{ __('Add') }}</a>
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
                @include($laravelAdminPackage.'::cms.categories.partials.index_table')
            </div>
        </div>
    </div>
@endsection
