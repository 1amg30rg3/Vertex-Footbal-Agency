<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'ghost', 'outline', 'danger', 'link'].includes(v),
    },
    size: { type: String, default: 'md', validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v) },
    href: { type: String, default: null },
    as: { type: String, default: null },
    method: { type: String, default: 'get' },
    type: { type: String, default: 'button' },
    icon: { type: String, default: null },
    iconRight: { type: String, default: null },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    block: { type: Boolean, default: false },
    square: { type: Boolean, default: false },
});

const variants = {
    primary: 'bg-accent text-accent-fg hover:bg-accent-hover shadow-sm',
    secondary: 'bg-surface-2 text-fg hover:bg-surface-3 border border-border',
    outline: 'border border-border-strong text-fg hover:bg-surface-2 hover:border-accent',
    ghost: 'text-fg-muted hover:text-fg hover:bg-surface-2',
    danger: 'bg-danger text-white hover:opacity-90',
    link: 'text-accent hover:text-accent-hover underline underline-offset-4 px-0',
};

const sizes = {
    xs: 'text-xs gap-1.5 h-7 px-2.5 rounded-md',
    sm: 'text-sm gap-1.5 h-9 px-3 rounded-lg',
    md: 'text-sm gap-2 h-10 px-4 rounded-lg',
    lg: 'text-base gap-2 h-12 px-6 rounded-xl',
};

const squareSizes = { xs: 'h-7 w-7', sm: 'h-9 w-9', md: 'h-10 w-10', lg: 'h-12 w-12' };

const classes = computed(() => [
    'inline-flex items-center justify-center font-medium tracking-tight',
    'transition-[background-color,border-color,color,opacity,transform] duration-150',
    'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
    'disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98]',
    'whitespace-nowrap select-none',
    sizes[props.size],
    props.square ? `${squareSizes[props.size]} px-0` : '',
    variants[props.variant],
    props.block ? 'w-full' : '',
]);

const component = computed(() => {
    if (props.as) return props.as;

    return props.href ? Link : 'button';
});

const isLink = computed(() => !props.as && !!props.href);

const iconSize = computed(() => ({ xs: 14, sm: 16, md: 17, lg: 19 })[props.size]);
</script>

<template>
    <component
        :is="component"
        :href="href || undefined"
        :method="isLink ? method : undefined"
        :as="isLink && method !== 'get' ? 'button' : undefined"
        :type="!href ? type : undefined"
        :disabled="disabled || loading || undefined"
        :class="classes"
        :aria-busy="loading || undefined"
    >
        <svg v-if="loading" class="animate-spin" :width="iconSize" :height="iconSize" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25" />
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        </svg>
        <Icon v-else-if="icon" :name="icon" :size="iconSize" />

        <slot />

        <Icon v-if="iconRight && !loading" :name="iconRight" :size="iconSize" />
    </component>
</template>
