@once
@push('footer-scripts')
    <style>
        .post-count-popover {
            --post-count-width: 32rem;
            position: fixed;
            inset: auto;
            margin: 0;
            width: min(var(--post-count-width), calc(100vw - 1rem));
            max-height: min(18rem, 40vh);
            overflow-wrap: anywhere;
        }

        .post-count-popover:popover-open {
            display: flex;
            flex-direction: column;
        }
    </style>
    <script type="module">
        let closeTimer;

        document.addEventListener('pointerover', event => {
            if (event.pointerType !== 'mouse') return;
            const trigger = event.target.closest('[data-post-count]');
            if (trigger || event.target.closest('[data-post-count-url]')) clearTimeout(closeTimer);
            if (trigger && !trigger.nextElementSibling.matches(':popover-open')) trigger.nextElementSibling.showPopover();
        });

        document.addEventListener('pointerout', event => {
            if (event.pointerType !== 'mouse') return;
            const trigger = event.target.closest('[data-post-count]');
            const panel = trigger?.nextElementSibling ?? event.target.closest('[data-post-count-url]');
            if (!panel || panel.contains(event.relatedTarget)) return;
            clearTimeout(closeTimer);
            closeTimer = setTimeout(() => {
                if (panel.isConnected && !panel.matches(':hover, :focus-within') && !panel.previousElementSibling.matches(':hover, :focus-visible')) {
                    panel.hidePopover();
                }
            }, 200);
        });

        document.addEventListener('focusin', event => {
            const trigger = event.target.closest('[data-post-count]:focus-visible');
            if (!trigger) return;
            const panel = trigger.nextElementSibling;
            if (!panel.contains(event.relatedTarget) && !panel.matches(':popover-open')) panel.showPopover();
        });

        document.addEventListener('beforetoggle', async event => {
            const panel = event.target;
            if (event.newState !== 'open' || !panel.matches('[data-post-count-url]')) return;

            const anchor = panel.previousElementSibling.getBoundingClientRect();
            panel.style.left = `clamp(.5rem, ${anchor.left}px, max(.5rem, calc(100vw - var(--post-count-width) - .5rem)))`;
            const above = anchor.bottom > window.innerHeight / 2;
            panel.style.top = above ? 'auto' : `${anchor.bottom}px`;
            panel.style.bottom = above ? `${window.innerHeight - anchor.top}px` : 'auto';

            if (panel.dataset.loaded) return;
            panel.dataset.loaded = 'true';
            try {
                const response = await fetch(panel.dataset.postCountUrl);
                if (!response.ok || response.redirected) throw new Error('Could not load associated posts');
                panel.innerHTML = await response.text();
            } catch {
                panel.querySelector('[role="status"]').textContent = panel.dataset.errorText;
                delete panel.dataset.loaded;
            }
        }, true);

        document.addEventListener('input', event => {
            const search = event.target.closest('[data-post-search]');
            if (!search) return;

            const panel = search.closest('[data-post-count-url]');
            if (!panel) return;

            const query = search.value.trim().replace(/\s+/g, ' ').toLocaleLowerCase();
            let hasMatches = false;
            panel.querySelectorAll('[data-post-search-item]').forEach(item => {
                const text = item.textContent.replace(/\s+/g, ' ').toLocaleLowerCase();
                item.hidden = !text.includes(query);
                hasMatches ||= !item.hidden;
            });
            panel.querySelector('[data-post-search-empty]').hidden = hasMatches;
        });
    </script>
@endpush
@endonce
