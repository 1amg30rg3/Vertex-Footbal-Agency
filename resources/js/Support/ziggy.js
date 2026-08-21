import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

function ziggy() {
    if (typeof Ziggy !== 'undefined') return Ziggy;

    return typeof globalThis !== 'undefined' ? globalThis.Ziggy : undefined;
}

function config(override) {
    if (override) return override;

    const base = ziggy();

    if (!base) return undefined;

    const locale = usePage()?.props?.contentLocale;

    return locale ? { ...base, defaults: { ...base.defaults, locale } } : base;
}

export const ZiggyVue = {
    install(app) {
        const helper = (name, params, absolute, override) =>
            route(name, params, absolute, config(override));

        app.config.globalProperties.route = helper;
        app.provide('route', helper);

        if (typeof window !== 'undefined') {
            window.route = helper;
        }
    },
};

export const useRoute = () => (name, params, absolute) => route(name, params, absolute, config());

export { route };
