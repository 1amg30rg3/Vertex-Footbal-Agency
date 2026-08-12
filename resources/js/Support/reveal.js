
let observer = null;

function prefersReducedMotion() {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
    );
}

function options(value) {
    if (typeof value === 'number') return { delay: value };

    return value && typeof value === 'object' ? value : {};
}

function getObserver() {
    observer ??= new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;

                observer.unobserve(entry.target);

                requestAnimationFrame(() =>
                    requestAnimationFrame(() => entry.target.setAttribute('data-reveal', 'shown')),
                );
            }
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0 },
    );

    return observer;
}

export const reveal = {
    created(el, binding) {
        if (typeof IntersectionObserver === 'undefined' || prefersReducedMotion()) return;

        const { delay = 0, shift = null } = options(binding.value);

        if (delay) el.style.setProperty('--reveal-delay', `${delay}ms`);
        if (shift) el.style.setProperty('--reveal-shift', shift);

        el.setAttribute('data-reveal', '');
    },

    mounted(el) {
        if (el.hasAttribute('data-reveal')) getObserver().observe(el);
    },

    unmounted(el) {
        observer?.unobserve(el);
    },
};
