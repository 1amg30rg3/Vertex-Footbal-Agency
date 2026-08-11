<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import Icon from './Icon.vue';

defineProps({
    compact: { type: Boolean, default: false },
    align: { type: String, default: 'right', validator: (v) => ['left', 'right'].includes(v) },
});

const page = usePage();
const { locale, locales, t } = useI18n();

const open = ref(false);
const root = ref(null);

const current = computed(() => locales.value.find((item) => item.code === locale.value) ?? locales.value[0]);

function urlFor(code) {
    const url = new URL(page.url, window.location.origin);
    const segments = url.pathname.split('/').filter(Boolean);
    const codes = locales.value.map((item) => item.code);

    if (segments.length && codes.includes(segments[0])) {
        segments[0] = code;
    } else {
        segments.unshift(code);
    }

    return `/${segments.join('/')}${url.search}`;
}

function choose(code) {
    open.value = false;

    if (code === locale.value) return;

    document.cookie = `site_locale=${code};path=/;max-age=31536000;samesite=lax`;
    router.visit(urlFor(code), { preserveScroll: true });
}

function onOutside(event) {
    if (open.value && root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', onOutside));
onBeforeUnmount(() => document.removeEventListener('click', onOutside));
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-border px-2.5 text-sm font-medium text-fg-muted transition-colors hover:border-accent/50 hover:text-accent"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :aria-label="t('common.language')"
            @click="open = !open"
        >
            <span class="text-base leading-none">{{ current?.flag }}</span>
            <span v-if="!compact" class="tabular-nums">{{ current?.label }}</span>
            <Icon name="chevronDown" :size="14" :class="['transition-transform', open ? 'rotate-180' : '']" />
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            leave-active-class="transition duration-100 ease-in"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <ul
                v-if="open"
                role="listbox"
                :class="[
                    'absolute z-40 mt-2 min-w-[11rem] overflow-hidden rounded-xl border border-border bg-bg-elevated p-1 shadow-xl',
                    align === 'right' ? 'right-0' : 'left-0',
                ]"
            >
                <li v-for="item in locales" :key="item.code">
                    <button
                        type="button"
                        role="option"
                        :aria-selected="item.code === locale"
                        :class="[
                            'flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-sm transition-colors',
                            item.code === locale
                                ? 'bg-accent-soft text-accent font-medium'
                                : 'text-fg-muted hover:bg-surface-2 hover:text-fg',
                        ]"
                        @click="choose(item.code)"
                    >
                        <span class="text-base leading-none">{{ item.flag }}</span>
                        <span class="flex-1">{{ item.native }}</span>
                        <span class="text-[11px] tabular-nums opacity-60">{{ item.label }}</span>
                    </button>
                </li>
            </ul>
        </Transition>
    </div>
</template>
