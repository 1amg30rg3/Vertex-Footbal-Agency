<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: null },
    description: { type: String, default: null },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md', 'lg', 'xl', 'full'].includes(v) },
    closeOnBackdrop: { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

const panel = ref(null);
let previouslyFocused = null;

const sizes = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-[min(72rem,95vw)]',
};

const panelClasses = computed(() => [
    'relative w-full rounded-2xl border border-border bg-bg-elevated shadow-2xl',
    'max-h-[90vh] overflow-y-auto scrollbar-thin',
    sizes[props.size],
]);

function close() {
    emit('close');
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        close();

        return;
    }

    if (event.key === 'Tab' && panel.value) {
        const focusable = panel.value.querySelectorAll(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])',
        );

        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
}

watch(
    () => props.open,
    async (open) => {
        if (open) {
            previouslyFocused = document.activeElement;
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', onKeydown);
            await nextTick();
            panel.value?.querySelector('[autofocus]')?.focus() ?? panel.value?.focus();
        } else {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            previouslyFocused?.focus?.();
        }
    },
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-100 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-overlay backdrop-blur-sm" @click="closeOnBackdrop && close()" />

                <Transition
                    appear
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 translate-y-3 scale-[0.98]"
                    leave-active-class="transition duration-100 ease-in"
                    leave-to-class="opacity-0 scale-[0.98]"
                >
                    <div
                        v-if="open"
                        ref="panel"
                        :class="panelClasses"
                        role="dialog"
                        aria-modal="true"
                        :aria-label="title || undefined"
                        tabindex="-1"
                    >
                        <div v-if="title || $slots.header" class="flex items-start gap-4 border-b border-border px-6 py-4">
                            <div class="min-w-0 flex-1">
                                <slot name="header">
                                    <h2 class="text-lg font-semibold tracking-tight text-fg">{{ title }}</h2>
                                    <p v-if="description" class="mt-1 text-sm text-fg-muted">{{ description }}</p>
                                </slot>
                            </div>
                            <button
                                type="button"
                                class="-mr-1.5 -mt-0.5 rounded-lg p-1.5 text-fg-subtle transition hover:bg-surface-2 hover:text-fg"
                                @click="close"
                            >
                                <Icon name="close" :size="18" />
                                <span class="sr-only">Close</span>
                            </button>
                        </div>

                        <div class="px-6 py-5">
                            <slot />
                        </div>

                        <div v-if="$slots.footer" class="flex flex-wrap justify-end gap-3 border-t border-border px-6 py-4">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
