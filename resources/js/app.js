import './bootstrap';

import Alpine from 'alpinejs';
import { appearancePreview } from './appearance-preview';

window.Alpine = Alpine;
window.appearancePreview = appearancePreview;

Alpine.start();

const processingLabel = (button) => button.dataset.processingLabel || button.textContent.trim();
const originalButtonContent = new WeakMap();

const lockForm = (form, submitter) => {
    if (form.dataset.submitting === 'true') {
        return false;
    }

    form.dataset.submitting = 'true';
    form.setAttribute('aria-busy', 'true');

    const buttons = Array.from(form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]'));
    const target = submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
        ? submitter
        : buttons[0];

    if (target?.name) {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = target.name;
        field.value = target.value;
        field.dataset.submitField = 'true';
        form.append(field);
    }

    buttons.forEach((button) => {
        button.disabled = true;
    });

    if (target instanceof HTMLButtonElement) {
        const label = processingLabel(target);
        originalButtonContent.set(target, Array.from(target.childNodes));
        const spinner = document.createElement('span');
        const text = document.createElement('span');
        spinner.className = 'ui-spinner';
        spinner.setAttribute('aria-hidden', 'true');
        text.textContent = label;
        target.replaceChildren(spinner, text);
    }

    return true;
};

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) {
        return;
    }

    if (!lockForm(event.target, event.submitter)) {
        event.preventDefault();
    }
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[aria-busy="true"]').forEach((form) => {
        delete form.dataset.submitting;
        form.removeAttribute('aria-busy');
        form.querySelectorAll('[data-submit-field]').forEach((field) => field.remove());
        form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((button) => {
            button.disabled = false;
            const content = originalButtonContent.get(button);
            if (content) {
                button.replaceChildren(...content);
                originalButtonContent.delete(button);
            }
        });
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('[role="dialog"][aria-modal="true"]').forEach((dialog) => {
            dialog.dispatchEvent(new CustomEvent('ui:dismiss', { bubbles: true }));
        });
        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    const dialogs = Array.from(document.querySelectorAll('[role="dialog"][aria-modal="true"]'))
        .filter((dialog) => dialog.getClientRects().length > 0);

    if (dialogs.length === 0) {
        return;
    }

    const dialog = dialogs.at(-1);
    const focusable = Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
        .filter((element) => element.getClientRects().length > 0);

    if (focusable.length === 0) {
        event.preventDefault();
        dialog.focus();
        return;
    }

    const first = focusable[0];
    const last = focusable.at(-1);

    if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && (document.activeElement === last || document.activeElement === dialog)) {
        event.preventDefault();
        first.focus();
    }
});
