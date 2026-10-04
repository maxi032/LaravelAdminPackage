<aside class="sidebar sidebar-dark sidebar-fixed" id="sidebar" aria-label="{{ __('Admin navigation') }}">
    <a class="sidebar-brand d-none d-md-flex text-decoration-none" href="{{ route($adminRoutePrefix.'dashboard') }}">
        {{ config('app.name', 'Laravel') }}
    </a>
    <ul class="sidebar-nav" id="post-type-navigation">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs($adminRoutePrefix.'dashboard') ? 'active' : '' }}"
               href="{{ route($adminRoutePrefix.'dashboard') }}"
               @if(request()->routeIs($adminRoutePrefix.'dashboard')) aria-current="page" @endif>
                <span class="nav-icon cil-speedometer" aria-hidden="true"></span>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="nav-title">{{ __('Content') }}</li>
        @forelse($sidebarPostTypes as $item)
            <li class="nav-item">
                <div class="d-flex post-type-nav">
                    <a class="nav-link flex-grow-1 {{ $item['active'] ? 'active' : '' }}"
                       href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $item['type']]) }}"
                       @if($item['active'] && request()->routeIs($adminRoutePrefix.'posts.type.list')) aria-current="page" @endif>
                        <span class="nav-icon {{ $item['icon'] }}" aria-hidden="true"></span>
                        {{ $item['label'] }}
                    </a>
                    <button class="nav-link border-0 px-3 {{ $item['active'] ? 'active' : '' }}" type="button"
                            data-coreui-toggle="collapse" data-coreui-target="#{{ $item['menuId'] }}"
                            aria-controls="{{ $item['menuId'] }}" aria-expanded="{{ $item['active'] ? 'true' : 'false' }}"
                            aria-label="{{ __('Toggle :type menu', ['type' => $item['label']]) }}">
                        <svg class="post-type-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="m9 6 6 6-6 6" />
                        </svg>
                    </button>
                </div>
                <ul class="list-unstyled collapse {{ $item['active'] ? 'show' : '' }}" id="{{ $item['menuId'] }}" data-coreui-parent="#post-type-navigation">
                    <li class="nav-item">
                        <a class="nav-link ps-5" href="{{ route($adminRoutePrefix.'posts.type.list', ['type' => $item['type']]) }}">
                            <span class="nav-icon cil-list" aria-hidden="true"></span>{{ \Illuminate\Support\Facades\Lang::has($item['type'].'.all') ? __($item['type'].'.all') : __('All :type', ['type' => $item['label']]) }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ps-5 {{ $item['active'] && request()->routeIs($adminRoutePrefix.'posts.create') ? 'active' : '' }}"
                           href="{{ route($adminRoutePrefix.'posts.create', ['type' => $item['type']]) }}">
                            <span class="nav-icon cil-plus" aria-hidden="true"></span>{{ __('Add :type', ['type' => $item['singularLabel']]) }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ps-5 {{ $item['active'] && request()->routeIs($adminRoutePrefix.'categories.type.list') ? 'active' : '' }}"
                           href="{{ route($adminRoutePrefix.'categories.type.list', ['type' => $item['type']]) }}">
                            <span class="nav-icon cil-folder" aria-hidden="true"></span>{{ __('Categories') }}
                        </a>
                    </li>
                </ul>
            </li>
        @empty
            <li class="nav-item px-3 py-2">{{ __('No post types available.') }}</li>
        @endforelse
    </ul>
    <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"
            aria-label="{{ __('Collapse or expand sidebar') }}" aria-controls="sidebar"></button>
</aside>

@once
    @push('footer-scripts')
        <style>
            .sidebar .post-type-chevron {
                transform: rotate(0deg);
                transition: transform 200ms ease-in-out;
            }

            .sidebar .post-type-nav > button[aria-expanded="true"] .post-type-chevron {
                transform: rotate(90deg);
            }

            @media (prefers-reduced-motion: reduce) {
                .sidebar .post-type-chevron {
                    transition: none;
                }
            }

            .sidebar .post-type-nav:is(:hover, :focus-within) > .nav-link {
                color: var(--cui-sidebar-nav-link-hover-color);
                background: var(--cui-sidebar-nav-link-hover-bg);
            }

            .sidebar .post-type-nav:is(:hover, :focus-within) > .nav-link .nav-icon {
                color: var(--cui-sidebar-nav-link-hover-icon-color);
            }
        </style>
        <script type="module">
            let sidebarNavigationPending = false;
            const accordion = document.getElementById('post-type-navigation');
            const menus = accordion ? [...accordion.querySelectorAll(':scope > .nav-item > ul.collapse')] : [];
            let pendingMenu = null;

            if (window.coreui?.Collapse) {
                menus.forEach(menu => {
                    window.coreui.Collapse.getOrCreateInstance(menu, { parent: accordion, toggle: false });

                    menu.addEventListener('show.coreui.collapse', event => {
                        // Wait for sibling transitions before CoreUI looks for open panels.
                        if (menus.some(other => other !== menu && other.classList.contains('collapsing'))) {
                            event.preventDefault();
                            pendingMenu = menu;
                        } else {
                            pendingMenu = null;
                        }
                    });

                    const openPendingMenu = () => {
                        if (!pendingMenu || menus.some(other => other.classList.contains('collapsing'))) {
                            return;
                        }
                        const nextMenu = pendingMenu;
                        pendingMenu = null;
                        window.coreui.Collapse.getOrCreateInstance(nextMenu).show();
                    };
                    menu.addEventListener('shown.coreui.collapse', openPendingMenu);
                    menu.addEventListener('hidden.coreui.collapse', openPendingMenu);
                });
            }

            document.querySelectorAll('#sidebar .post-type-nav > a').forEach(link => {
                link.addEventListener('click', event => {
                    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey ||
                        event.shiftKey || event.altKey || link.hasAttribute('download') ||
                        (link.target && link.target !== '_self')) {
                        return;
                    }

                    if (sidebarNavigationPending) {
                        event.preventDefault();
                        return;
                    }

                    const button = link.parentElement.querySelector('button[aria-controls]');
                    const menu = button && document.getElementById(button.getAttribute('aria-controls'));
                    if (!menu || !window.coreui?.Collapse ||
                        (menu.classList.contains('show') && !menu.classList.contains('collapsing')) ||
                        window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        return;
                    }

                    event.preventDefault();
                    sidebarNavigationPending = true;
                    const navigate = () => {
                        window.clearTimeout(fallback);
                        menu.removeEventListener('shown.coreui.collapse', navigate);
                        window.location.assign(link.href);
                    };
                    // Continue navigation even if a transition event is interrupted.
                    const fallback = window.setTimeout(navigate, 1000);
                    menu.addEventListener('shown.coreui.collapse', navigate, { once: true });
                    window.coreui.Collapse.getOrCreateInstance(menu, { toggle: false }).show();
                });
            });
        </script>
    @endpush
@endonce
