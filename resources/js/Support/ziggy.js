import { route } from 'ziggy-js';

export const ZiggyVue = {
    install(app) {
        const helper = (name, params, absolute, config) =>
            route(name, params, absolute, config ?? window.Ziggy);

        app.config.globalProperties.route = helper;
        app.provide('route', helper);

        if (typeof window !== 'undefined') {
            window.route = helper;
        }
    },
};

export const useRoute = () => (name, params, absolute) =>
    route(name, params, absolute, window.Ziggy);

export { route };
