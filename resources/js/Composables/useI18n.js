import { computed, inject, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';

const I18N_KEY = Symbol('i18n');

function makeStore(initialProps) {
    const state = reactive({
        locale: initialProps.locale ?? 'ka',
        messages: initialProps.translations ?? {},
    });

    return state;
}

function lookup(messages, key) {
    return key.split('.').reduce((carry, part) => {
        if (carry && typeof carry === 'object' && part in carry) {
            return carry[part];
        }

        return undefined;
    }, messages);
}

function interpolate(line, replacements) {
    return Object.entries(replacements).reduce(
        (carry, [token, value]) =>
            carry
                .replace(new RegExp(`:${token}\\b`, 'g'), value)
                .replace(new RegExp(`\\{${token}\\}`, 'g'), value),
        line,
    );
}

export function createI18n(initialProps) {
    const state = makeStore(initialProps);

    return {
        install(app) {
            app.provide(I18N_KEY, state);
            app.config.globalProperties.$t = (key, replacements) =>
                translate(state, key, replacements);
        },
    };
}

function translate(state, key, replacements = {}) {
    const line = lookup(state.messages, key);

    if (typeof line !== 'string') {
        return key.split('.').pop().replace(/[_-]/g, ' ');
    }

    return interpolate(line, replacements);
}

export function useI18n() {
    const page = usePage();
    const state = inject(I18N_KEY, null);

    const locale = computed(() => page.props.locale ?? state?.locale ?? 'ka');
    const messages = computed(() => page.props.translations ?? state?.messages ?? {});

    const t = (key, replacements = {}) =>
        translate({ locale: locale.value, messages: messages.value }, key, replacements);

    const choice = (key, count, replacements = {}) => {
        const line = t(key, { ...replacements, count });
        const [singular, plural] = line.split('|');

        return count === 1 ? singular : (plural ?? singular);
    };

    /**
     * Resolve a key whose translation is a list of strings — long-form copy is
     * stored as an array of paragraphs so the markup stays out of the lang file.
     */
    const list = (key, replacements = {}) => {
        const value = lookup(messages.value, key);

        if (!Array.isArray(value)) {
            return typeof value === 'string' ? [interpolate(value, replacements)] : [];
        }

        return value
            .filter((line) => typeof line === 'string')
            .map((line) => interpolate(line, replacements));
    };

    return {
        t,
        choice,
        list,
        locale,
        locales: computed(() => page.props.locales ?? []),
        defaultLocale: computed(() => page.props.defaultLocale ?? 'ka'),
        currentLocale: computed(() =>
            (page.props.locales ?? []).find((item) => item.code === locale.value),
        ),
    };
}

export function useTranslatable() {
    const { locale, defaultLocale } = useI18n();

    const pick = (field, fallbackLocale = null) => {
        if (field == null) return '';
        if (typeof field === 'string') return field;

        const order = [locale.value, fallbackLocale, defaultLocale.value, 'en'].filter(Boolean);

        for (const code of order) {
            if (field[code]) return field[code];
        }

        return Object.values(field).find((value) => !!value) ?? '';
    };

    return { pick };
}
