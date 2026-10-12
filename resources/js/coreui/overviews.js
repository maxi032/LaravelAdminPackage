// A single transport for overview pagination and future search/sort parameters.
// Table markup and action dialogs continue to be rendered by the package's Blade views.
for (const overview of document.querySelectorAll('[data-overview]')) {
    const content = overview.querySelector('[data-overview-content]');
    const status = overview.querySelector('[data-overview-status]');
    const error = overview.querySelector('[data-overview-error]');
    let pending;
    let requestedUrl = overview.dataset.overviewUrl;
    let displayedUrl = requestedUrl;

    async function load(url, pushHistory = false, focus = false) {
        const target = new URL(url, window.location.href);
        const endpoint = new URL(overview.dataset.overviewUrl, window.location.href);
        if (target.origin !== endpoint.origin || target.pathname !== endpoint.pathname) return;
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        requestedUrl = target.href;
        const timeout = setTimeout(() => controller.abort(), 30000);
        content.inert = true;
        content.setAttribute('aria-busy', 'true');
        status.textContent = overview.dataset.loadingText;
        status.hidden = false;
        error.hidden = true;
        try {
            const response = await fetch(target, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            if (!response.ok || response.redirected) throw new Error('Overview request failed');
            const result = await response.json();
            if (typeof result.html !== 'string' || !result.pagination) throw new Error('Invalid overview response');
            if (pending !== controller) return;
            content.querySelectorAll('.modal').forEach(modal => window.coreui?.Modal.getInstance(modal)?.dispose());
            content.innerHTML = result.html;
            displayedUrl = target.href;
            if (pushHistory) window.history.pushState(null, '', target);
            status.textContent = `${result.pagination.current_page} / ${result.pagination.last_page}`;
            if (focus) {
                const table = content.querySelector('table') || content;
                table.setAttribute('tabindex', '-1');
                // Focus after releasing the inert loading region below.
                content.inert = false;
                table.focus({ preventScroll: true });
            }
        } catch (failure) {
            if (pending !== controller) return;
            overview.querySelector('[data-overview-error-message]').textContent = overview.dataset.errorText;
            error.hidden = false;
            // Back/forward failures must not leave the URL describing a different page.
            if (!pushHistory) window.history.replaceState(null, '', displayedUrl);
        } finally {
            clearTimeout(timeout);
            if (pending === controller) {
                content.inert = false;
                content.removeAttribute('aria-busy');
                status.hidden = true;
                pending = null;
            }
        }
    }

    overview.addEventListener('click', event => {
        const retry = event.target.closest('[data-overview-retry]');
        if (retry) {
            load(requestedUrl, true, true);
            return;
        }
        const link = event.target.closest('.pagination a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        load(link.href, true, true);
    });
    window.addEventListener('popstate', () => load(window.location.href, false, true));
    load(requestedUrl);
}
