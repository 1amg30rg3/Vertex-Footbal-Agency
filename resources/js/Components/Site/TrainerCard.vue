<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({ trainer: { type: Object, required: true } });

const { t } = useI18n();
const route = useRoute();

const href = computed(() => route('public.trainers.show', { trainer: props.trainer.slug }));
</script>

<template>
    <Link
        :href="href"
        class="group flex gap-4 rounded-2xl border border-border bg-surface p-4 transition-[border-color,transform] duration-300 hover:-translate-y-0.5 hover:border-accent/60 sm:p-5"
    >
        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-surface-2 sm:h-24 sm:w-24">
            <img
                v-if="trainer.photo"
                :src="trainer.photo"
                :alt="trainer.full_name"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            >
            <div v-else class="flex h-full w-full items-center justify-center text-fg-subtle">
                <Icon name="whistle" :size="26" />
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <p v-if="trainer.role" class="text-[11px] font-semibold uppercase tracking-wider text-accent">
                {{ trainer.role }}
            </p>
            <h3 class="mt-0.5 text-base font-semibold tracking-tight text-fg sm:text-lg">{{ trainer.full_name }}</h3>
            <p v-if="trainer.excerpt" class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-fg-muted">
                {{ trainer.excerpt }}
            </p>
            <span class="mt-2.5 inline-flex items-center gap-1 text-xs font-medium text-accent transition-transform group-hover:translate-x-0.5">
                {{ t('common.view_profile') }}
                <Icon name="arrowRight" :size="13" />
            </span>
        </div>
    </Link>
</template>
