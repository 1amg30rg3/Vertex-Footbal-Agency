import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '@/Support/ziggy';
import { initTheme } from '@/Composables/useTheme';
import { createI18n } from '@/Composables/useI18n';

const appName = import.meta.env.VITE_APP_NAME || 'VERTEX Football Agency';

initTheme();

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createI18n(props.initialPage.props))
            .mount(el);
    },
    progress: {
        color: '#cd942c',
        showSpinner: false,
    },
});
