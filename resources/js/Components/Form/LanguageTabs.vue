<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    modelValue: { type: Object, default: () => ({}) },
    label: { type: String, default: null },
    hint: { type: String, default: null },
    required: { type: Boolean, default: false },
    errors: { type: Object, default: () => ({}) },
    name: { type: String, default: '' },
    copyable: { type: Boolean, default: true },
});

const page = usePage();

const locales = computed(() => page.props.locales ?? []);
const defaultLocale = computed(() => page.props.defaultLocale ?? 'ka');

const active = ref(defaultLocale.value);

function hasContent(code) {
    const value = props.modelValue?.[code];

    if (typeof value !== 'string') return !!value;

    return value.replace(/<[^>]*>/g, '').trim().length > 0;
}

function errorFor(code) {
    return props.errors?.[`${props.name}.${code}`];
}

const localeErrors = computed(() =>
    Object.fromEntries(locales.value.map((item) => [item.code, errorFor(item.code)])),
);

const activeError = computed(() => localeErrors.value[active.value]);

/** Locales carrying an error that the editor is not currently looking at. */
const otherErrors = computed(() =>
    locales.value.filter((item) => item.code !== active.value && localeErrors.value[item.code]),
);

const hasAnyError = computed(() => locales.value.some((item) => localeErrors.value[item.code]));

function fillFromDefault() {
    const source = props.modelValue?.[defaultLocale.value];

    if (!source) return;

    const next = { ...props.modelValue };

    locales.value.forEach((item) => {
        if (!hasContent(item.code)) next[item.code] = source;
    });

    emit('update:modelValue', next);
}

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-2">
        <div v-if="label || copyable" class="flex flex-wrap items-center justify-between gap-2">
            <label v-if="label" class="text-sm font-medium text-fg">
                {{ label }}
                <span v-if="required" class="text-danger">*</span>
            </label>

            <button
                v-if="copyable"
                type="button"
                class="inline-flex items-center gap-1 text-[11px] font-medium text-fg-subtle transition-colors hover:text-accent"
                title="Copy the default-language value into every empty language"
                @click="fillFromDefault"
            >
                <Icon name="copy" :size="12" />
                Fill empty languages
            </button>
        </div>

        <div
            :class="[
                'flex flex-wrap items-center gap-1 rounded-xl border bg-surface-2 p-1',
                hasAnyError ? 'border-danger' : 'border-border',
            ]"
        >
            <button
                v-for="item in locales"
                :key="item.code"
                type="button"
                :class="[
                    'inline-flex h-7 items-center gap-1.5 rounded-lg px-2 text-xs font-medium transition-colors',
                    active === item.code && localeErrors[item.code]
                        ? 'bg-danger text-white'
                        : active === item.code
                          ? 'bg-accent text-accent-fg'
                          : localeErrors[item.code]
                            ? 'bg-danger-soft text-danger hover:bg-danger-soft'
                            : 'text-fg-muted hover:bg-surface-3 hover:text-fg',
                ]"
                :title="item.native"
                @click="active = item.code"
            >
                <span class="text-[13px] leading-none">{{ item.flag }}</span>
                <span class="tabular-nums">{{ item.label }}</span>
                <span
                    v-if="localeErrors[item.code]"
                    class="h-1.5 w-1.5 rounded-full bg-current"
                />
                <span
                    v-else-if="hasContent(item.code)"
                    :class="['h-1.5 w-1.5 rounded-full', active === item.code ? 'bg-current opacity-60' : 'bg-success']"
                />
            </button>
        </div>

        <div>
            <slot :locale="active" :is-default="active === defaultLocale" />
        </div>

        <p v-if="activeError" class="text-xs text-danger">{{ activeError }}</p>

        <p v-if="otherErrors.length" class="flex flex-wrap items-center gap-1.5 text-xs text-danger">
            <Icon name="alert" :size="12" />
            <span>Also needs attention in</span>
            <button
                v-for="item in otherErrors"
                :key="item.code"
                type="button"
                class="rounded bg-danger-soft px-1.5 py-0.5 font-semibold underline underline-offset-2"
                :title="localeErrors[item.code]"
                @click="active = item.code"
            >{{ item.label }}</button>
        </p>

        <p v-if="!activeError && !otherErrors.length && hint" class="text-xs text-fg-subtle">{{ hint }}</p>
    </div>
</template>
