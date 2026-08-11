<script setup>
import { useTheme } from '@/Composables/useTheme';
import { useI18n } from '@/Composables/useI18n';
import Icon from './Icon.vue';

defineProps({
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md'].includes(v) },
});

const { isDark, toggleTheme } = useTheme();
const { t } = useI18n();
</script>

<template>
    <button
        type="button"
        :class="[
            'relative inline-flex items-center justify-center rounded-lg border border-border text-fg-muted',
            'transition-colors hover:border-accent/50 hover:text-accent',
            size === 'sm' ? 'h-8 w-8' : 'h-9 w-9',
        ]"
        :title="t('theme.toggle')"
        :aria-label="t('theme.toggle')"
        @click="toggleTheme"
    >
        <Transition
            mode="out-in"
            enter-active-class="transition duration-150"
            enter-from-class="opacity-0 rotate-45 scale-75"
            leave-active-class="transition duration-100"
            leave-to-class="opacity-0 -rotate-45 scale-75"
        >
            <Icon :key="isDark ? 'moon' : 'sun'" :name="isDark ? 'moon' : 'sun'" :size="size === 'sm' ? 16 : 17" />
        </Transition>
    </button>
</template>
