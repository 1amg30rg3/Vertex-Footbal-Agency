import { nextTick } from 'vue';

const isVisible = (node) => node.offsetParent !== null;

/**
 * Bring the first validation error into view after a rejected save. The admin
 * forms are long enough that an error five sections down is otherwise invisible.
 *
 * The summary banner at the top of the form is itself red, so it is skipped —
 * otherwise every failed save would "scroll" to where you already are.
 */
export function scrollToFirstError() {
    nextTick(() => {
        const fields = [...document.querySelectorAll('[aria-invalid="true"]')].filter(isVisible);

        const messages = [...document.querySelectorAll('.text-danger, .border-danger')]
            .filter((node) => !node.closest('[role="alert"]'))
            .filter(isVisible);

        const target = fields[0] ?? messages[0];

        if (!target) return;

        target.scrollIntoView({ behavior: 'smooth', block: 'center' });

        const focusable = target.matches('input, textarea, select')
            ? target
            : target.closest('div')?.querySelector('input, textarea, select');

        if (focusable) setTimeout(() => focusable.focus({ preventScroll: true }), 450);
    });
}
