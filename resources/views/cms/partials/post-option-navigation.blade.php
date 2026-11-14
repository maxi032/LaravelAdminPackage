<li class="nav-title">{{ __('Settings') }}</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs($adminRoutePrefix.'settings.post-types.*') ? 'active' : '' }}"
       href="{{ route($adminRoutePrefix.'settings.post-types.index') }}"
       @if(request()->routeIs($adminRoutePrefix.'settings.post-types.*')) aria-current="page" @endif>
        <span class="nav-icon cil-view-module" aria-hidden="true"></span>
        <span class="sidebar-link-label">{{ __('Post types') }}</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs($adminRoutePrefix.'settings.post-options.*') ? 'active' : '' }}"
       href="{{ route($adminRoutePrefix.'settings.post-options.index') }}"
       @if(request()->routeIs($adminRoutePrefix.'settings.post-options.*')) aria-current="page" @endif>
        <span class="nav-icon cil-view-quilt" aria-hidden="true"></span>
        <span class="sidebar-link-label">{{ __('Post options') }}</span>
    </a>
</li>
