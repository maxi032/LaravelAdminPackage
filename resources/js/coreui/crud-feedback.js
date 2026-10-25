// Normal form navigation and AJAX actions share the same pending feedback.
const pending = new Map();

function statusMessage(text) {
    const message = document.createElement('span');
    message.className = 'crud-status d-inline-flex align-items-center gap-2';
    message.setAttribute('role', 'status');
    message.setAttribute('aria-live', 'polite');
    const spinner = document.createElement('span');
    spinner.className = 'spinner-border spinner-border-sm';
    spinner.setAttribute('aria-hidden', 'true');
    message.append(spinner, document.createTextNode(text));
    return message;
}

function begin(region, text, button, overlay = true) {
    if (pending.has(region)) return false;
    const buttons = [...region.querySelectorAll('button')].map(element => [element, element.disabled]);
    const previous = { busy: region.getAttribute('aria-busy'), label: button?.innerHTML, buttons };
    const message = statusMessage(text);
    if (button) {
        button.replaceChildren(message);
        // A native form may still need its submitter's value.
        button.setAttribute('aria-disabled', 'true');
    }
    if (overlay && region.tagName !== 'TR') {
        previous.cover = document.createElement('div');
        previous.cover.className = 'crud-overlay';
        previous.cover.append(statusMessage(text));
        region.append(previous.cover);
    } else if (!button) {
        (region.tagName === 'TR' ? region.querySelector('[data-crud-form]') : region).append(message);
        previous.message = message;
    }
    buttons.forEach(([element]) => {
        if (element !== button) element.disabled = true;
    });
    region.classList.add('crud-busy');
    region.setAttribute('aria-busy', 'true');
    previous.button = button;
    previous.timer = setTimeout(() => region.classList.add('crud-busy-revealed'), 300);
    pending.set(region, previous);
    return true;
}

function end(region) {
    const state = pending.get(region);
    if (!state) return;
    clearTimeout(state.timer);
    state.cover?.remove();
    state.message?.remove();
    if (state.button) {
        state.button.innerHTML = state.label;
        state.button.removeAttribute('aria-disabled');
    }
    state.buttons.forEach(([element, disabled]) => { element.disabled = disabled; });
    if (state.busy === null) region.removeAttribute('aria-busy');
    else region.setAttribute('aria-busy', state.busy);
    region.classList.remove('crud-busy', 'crud-busy-revealed');
    pending.delete(region);
}

// Block repeated actions and edits without disabling successful form fields.
for (const eventName of ['click', 'keydown', 'beforeinput']) {
    document.addEventListener(eventName, event => {
        if ([...pending.keys()].some(region => region.contains(event.target))) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
}

document.addEventListener('hide.coreui.modal', event => {
    if ([...pending.keys()].some(region => event.target.contains(region))) event.preventDefault();
});

document.addEventListener('submit', event => {
    const form = event.target.closest('[data-crud-form]');
    if (!form || event.defaultPrevented) return;
    const region = form.closest(form.dataset.loadingRegion) || form.querySelector(form.dataset.loadingRegion) || form;
    if (pending.has(region)) {
        event.preventDefault();
        return;
    }
    form.querySelectorAll('.editor').forEach(element => element.cmsEditor?.updateSourceElement());
    begin(region, form.dataset.loadingText, event.submitter);
});

window.addEventListener('pageshow', () => {
    [...pending.keys()].forEach(end);
    document.querySelectorAll('[data-status-toggle]').forEach(toggle => {
        toggle.disabled = false;
    });
});

function notify(text, error = false) {
    let container = document.querySelector('[data-crud-notifications]');
    if (!container) {
        container = document.createElement('div');
        container.dataset.crudNotifications = '';
        container.className = 'toast-container crud-notifications position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.append(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast position-static m-0 mb-2 text-white bg-${error ? 'danger' : 'success'}`;
    toast.setAttribute('role', error ? 'alert' : 'status');
    toast.setAttribute('aria-live', error ? 'assertive' : 'polite');
    const body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = text;
    toast.append(body);
    container.append(toast);
    const instance = window.coreui.Toast.getOrCreateInstance(toast, { delay: error ? 8000 : 5000 });
    toast.addEventListener('hidden.coreui.toast', () => { instance.dispose(); toast.remove(); }, { once: true });
    instance.show();
}

document.addEventListener('change', event => {
    const toggle = event.target.closest('[data-status-toggle]');
    if (!toggle) return;
    const region = toggle.closest('.form-check');
    const oldValue = !toggle.checked;
    if (!begin(region, toggle.dataset.loadingText, null, false)) {
        toggle.checked = oldValue;
        return;
    }
    toggle.disabled = true;
    window.jQuery.ajax({
        type: 'POST',
        url: toggle.dataset.statusUrl,
        data: { id: toggle.dataset.id, status: toggle.checked ? 1 : 0 },
        timeout: 30000,
        success(data) {
            if (typeof data.success === 'string' && data.success) {
                if (toggle.hasAttribute('data-post-status')) {
                    toggle.dataset.postStatus = toggle.checked ? '1' : '0';
                    const label = toggle.checked ? toggle.dataset.activeLabel : toggle.dataset.inactiveLabel;
                    toggle.labels.forEach(element => { element.textContent = label; });
                    toggle.setAttribute('aria-label', label);
                }
                notify(data.success);
            }
            else {
                toggle.checked = oldValue;
                notify(toggle.dataset.errorText, true);
            }
        },
        error() {
            toggle.checked = oldValue;
            notify(toggle.dataset.errorText, true);
        },
        complete() {
            toggle.disabled = false;
            end(region);
        },
    });
});
