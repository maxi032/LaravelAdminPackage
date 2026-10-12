// Shared by post Delete and trash Destroy dialogs, including buttons added by AJAX pagination.
document.addEventListener('show.coreui.modal', event => {
    const modal = event.target;
    if (!modal.matches('[data-post-delete-modal]')) return;
    const form = modal.querySelector('[data-post-delete-form]');
    const confirm = form.querySelector('[type="submit"]');
    const title = modal.querySelector('[data-post-delete-title]');
    form.removeAttribute('action');
    confirm.disabled = true;
    title.textContent = '';
    const trigger = event.relatedTarget;
    if (!trigger?.matches('[data-post-delete]') || !trigger.dataset.deleteUrl) {
        event.preventDefault();
        return;
    }
    const action = new URL(trigger.dataset.deleteUrl, window.location.href);
    if (action.origin !== window.location.origin) {
        event.preventDefault();
        return;
    }
    form.setAttribute('action', action.href);
    title.textContent = trigger.dataset.postTitle;
    confirm.disabled = false;
});

document.addEventListener('hidden.coreui.modal', event => {
    if (!event.target.matches('[data-post-delete-modal]')) return;
    const form = event.target.querySelector('[data-post-delete-form]');
    form.removeAttribute('action');
    form.querySelector('[type="submit"]').disabled = true;
    event.target.querySelector('[data-post-delete-title]').textContent = '';
});

document.addEventListener('submit', event => {
    if (event.target.matches('[data-post-delete-form]') && !event.target.hasAttribute('action')) {
        event.preventDefault();
    }
}, true);
