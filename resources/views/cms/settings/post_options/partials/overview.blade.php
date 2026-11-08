<div data-overview data-overview-url="{{ request()->fullUrl() }}"
     data-loading-text="{{ __('Loading…') }}"
     data-error-text="{{ __('The overview could not be loaded. Please try again.') }}">
    <div data-overview-status role="status" aria-live="polite" class="px-3 py-2" hidden></div>
    <div data-overview-error role="alert" class="alert alert-danger m-3" hidden>
        <span data-overview-error-message></span>
        <button type="button" data-overview-retry class="btn btn-sm btn-outline-danger ms-2">{{ __('Retry') }}</button>
    </div>
    <div data-overview-content>
        @include($laravelAdminPackage.'::cms.settings.post_options.partials.'.$table)
    </div>
</div>
