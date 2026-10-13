// Query parameters can also carry future search/sort settings.
// Table markup and action dialogs remain rendered by the package's Blade views.
// Each overview gets its own request and navigation state. The data-overview-*
// attributes are the connection between this module and the Blade wrapper.
for (const overview of document.querySelectorAll('[data-overview]')) {
    const content = overview.querySelector('[data-overview-content]');
    const status = overview.querySelector('[data-overview-status]');
    const error = overview.querySelector('[data-overview-error]');
    // Resolve relative URLs against the current page. This endpoint fixes the
    // allowed origin and path; query parameters may change between requests.
    const endpoint = new URL(overview.dataset.overviewUrl, window.location.href);

    // The AbortController for the active request, also used to identify its owner.
    let pending;

    // The last valid URL requested, even if it failed. Retry uses this URL.
    let requestedUrl = endpoint.href;

    // The URL matching the visible rows. Only successful navigation changes it.
    let displayedUrl = endpoint.href;

    // A counter distinguishes overlapping navigate() calls after their awaits.
    let navigation = 0;

    // Fetch and validate a Blade fragment, then render it. Returns true only
    // when this request actually rendered; failure or supersession returns false.
    async function load(url, options = {}) {
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        const timeout = setTimeout(() => controller.abort(), 30000);

        // inert prevents mouse/keyboard interaction with rows being replaced;
        // aria-busy tells assistive technology that this region is updating.
        content.inert = true;
        content.setAttribute('aria-busy', 'true');
        status.textContent = overview.dataset.loadingText;
        status.hidden = false;
        error.hidden = true;
        try {
            const target = new URL(url, window.location.href);
            // Keep requests on this overview route and reject embedded credentials.
            // URL construction also throws for malformed URLs, handled below.
            if (target.origin !== endpoint.origin || target.pathname !== endpoint.pathname || target.username || target.password) {
                throw new Error('Invalid overview URL');
            }
            requestedUrl = target.href;
            // Laravel uses these headers to return JSON containing Blade HTML
            // instead of a full page. Keep the same-origin authentication cookies.
            const response = await fetch(target, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            // A login redirect may end in HTTP 200 but must not replace the table.
            if (!response.ok || response.redirected) throw new Error('Overview request failed');
            const result = await response.json();

            // Validate the fields used by render() before changing any markup.
            const pagination = result?.pagination;
            if (typeof result?.html !== 'string' || !Number.isInteger(pagination?.current_page) ||
                !Number.isInteger(pagination?.last_page) || pagination.current_page < 1 || pagination.last_page < 1) {
                throw new Error('Invalid overview response');
            }

            // A newer request owns the UI now; discard this older response.
            if (pending !== controller) return false;
            // Some transports may finish parsing even after their signal was aborted.
            if (controller.signal.aborted) throw new Error('Overview request aborted');
            render(result, options);
            return true;
        } catch (failure) {
            // Superseded requests must not display errors over the newer request.
            if (pending !== controller) return false;
            // Keep the existing rows and show the translated error with Retry.
            // Network errors, invalid responses and timeouts all follow this path.
            overview.querySelector('[data-overview-error-message]').textContent = overview.dataset.errorText;
            error.hidden = false;
            return false;
        } finally {
            // finally runs on success, failure and early return. Always clear this
            // request's timer, but only its owner may release the loading region.
            clearTimeout(timeout);
            if (pending === controller) {
                content.inert = false;
                content.removeAttribute('aria-busy');
                status.hidden = true;
                pending = null;
            }
        }
    }

    // Update only the DOM from a validated response: no fetch or history changes.
    function render(result, options = {}) {
        // Dispose CoreUI instances while their old elements are still present,
        // so replacing the HTML does not leave modal resources behind.
        content.querySelectorAll('.modal').forEach(modal => window.coreui?.Modal?.getInstance(modal)?.dispose());
        content.innerHTML = result.html;
        status.textContent = `${result.pagination.current_page} / ${result.pagination.last_page}`;
        content.inert = false;
        content.removeAttribute('aria-busy');
        if (options.focus) {
            // Release inert before focusing. tabindex=-1 allows programmatic
            // focus without adding the table to the normal Tab sequence.
            // Fall back to the content region if the fragment has no table.
            const table = content.querySelector('table') || content;
            table.setAttribute('tabindex', '-1');
            table.focus({ preventScroll: true });
        }
    }

    // Coordinate content and URL changes. Clicks/retries request pushHistory;
    // Back/Forward already changes the URL, so it only requests focus.
    async function navigate(url, options = {}) {
        const currentNavigation = ++navigation;
        const succeeded = await load(url, options);
        // Only the latest navigation may commit or repair browser history.
        if (currentNavigation !== navigation) return false;
        if (succeeded) {
            displayedUrl = requestedUrl;
            // Commit history only after rendering succeeds. Avoid adding the
            // same URL twice when a user clicks the current page or retries it.
            if (options.pushHistory && window.location.href !== displayedUrl) {
                window.history.pushState(null, '', displayedUrl);
            }
        } else if (window.location.href !== displayedUrl) {
            // Back/Forward changes the address before loading starts. On failure,
            // restore the URL matching the retained rows without adding an entry.
            // Also covers a failed click/retry that superseded a Back/Forward request.
            window.history.replaceState(null, '', displayedUrl);
        }
        return succeeded;
    }

    // Delegate to the stable wrapper: pagination links are replaced on every
    // render, and their click events bubble here (including keyboard activation).
    overview.addEventListener('click', event => {
        const retry = event.target.closest('[data-overview-retry]');
        if (retry) {
            event.preventDefault();
            navigate(requestedUrl, { pushHistory: true, focus: true });
            return;
        }
        const link = event.target.closest('.pagination a[href]');
        // Preserve normal browser behavior for modified/non-primary clicks and
        // events that another handler has already handled.
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        navigate(link.href, { pushHistory: true, focus: true });
    });
    // Reuse the same loading/rendering flow when the browser traverses history.
    window.addEventListener('popstate', () => navigate(window.location.href, { focus: true }));
    // Blade normally supplies the initial table, including an empty-results table.
    // Fetch only if no initial markup was supplied; avoid fetching it twice.
    if (!content.innerHTML.trim()) navigate(requestedUrl);
}
