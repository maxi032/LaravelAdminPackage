import { Sidebar, Tooltip } from '@coreui/coreui';
import ScrollBarHelper from '@coreui/coreui/js/src/util/scrollbar.js';

const sidebar = document.getElementById('sidebar');

if (sidebar) {
    const desktop = window.matchMedia('(min-width: 768px)'); // CoreUI's md mobile breakpoint.
    const toggles = [...document.querySelectorAll('[data-sidebar-toggle]')];
    const instance = Sidebar.getOrCreateInstance(sidebar);
    const tooltips = [...sidebar.querySelectorAll('.nav-link')].map(link => {
        const label = link.querySelector('.sidebar-link-label')?.textContent.trim() || link.getAttribute('aria-label');
        if (!label) return null;
        const tooltip = Tooltip.getOrCreateInstance(link, {
            title: label,
            placement: 'right',
            container: 'body',
            trigger: 'hover focus',
        });
        return tooltip;
    }).filter(Boolean);

    const sync = () => {
        const collapsed = sidebar.classList.contains('sidebar-narrow');
        const expanded = desktop.matches ? !collapsed : sidebar.classList.contains('show');
        toggles.forEach(button => {
            button.setAttribute('aria-expanded', String(expanded));
            button.setAttribute('aria-label', desktop.matches
                ? (expanded ? button.dataset.labelCollapse : button.dataset.labelExpand)
                : (expanded ? (button.dataset.labelHide || button.dataset.labelCollapse) : (button.dataset.labelShow || button.dataset.labelExpand)));
            button.classList.toggle('sidebar-is-collapsed', desktop.matches && collapsed);
        });
        tooltips.forEach(tooltip => {
            tooltip.hide();
            if (desktop.matches && collapsed) tooltip.enable();
            else tooltip.disable();
        });
    };

    const save = () => {
        try {
            localStorage.setItem('laravel-admin.sidebar', sidebar.classList.contains('sidebar-narrow') ? 'collapsed' : 'expanded');
        } catch (error) {
            // Toggling still works when browser storage is blocked.
        }
        sync();
    };

    toggles.forEach(button => button.addEventListener('click', () => {
        if (desktop.matches) {
            instance.toggleNarrow();
            save();
        } else {
            instance.toggle();
            sync();
        }
    }));

    // Reveal nested menus in the full sidebar; CoreUI still owns their accordion behavior.
    sidebar.addEventListener('click', event => {
        if (desktop.matches && sidebar.classList.contains('sidebar-narrow') &&
            event.target.closest('.post-type-nav > button, .nav-group-toggle')) {
            instance.reset();
            save();
        }
    }, true);

    ['shown.coreui.sidebar', 'hidden.coreui.sidebar'].forEach(name => sidebar.addEventListener(name, sync));
    desktop.addEventListener('change', () => {
        // CoreUI 4.3 initializes the backdrop only for the initial viewport and
        // does not clear mobile scroll locking on desktop. Keep this compatibility
        // adjustment here; disposing Sidebar would leave its window resize listener behind.
        instance._backdrop.dispose();
        instance._backdrop = instance._initializeBackDrop();
        if (desktop.matches) {
            sidebar.classList.remove('show');
            instance._removeClickOutListener();
            new ScrollBarHelper().reset();
        }
        sync();
    });
    sync();
}
