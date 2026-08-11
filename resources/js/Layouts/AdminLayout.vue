<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import Icon from '@/Components/Ui/Icon.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';
import Alert from '@/Components/Ui/Alert.vue';
import Button from '@/Components/Ui/Button.vue';
import Logo from '@/Components/Site/Logo.vue';

const props = defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});

const page = usePage();
const route = useRoute();

const sidebarOpen = ref(false);

const user = computed(() => page.props.auth?.user ?? null);
const site = computed(() => page.props.site ?? {});
const defaultLocale = computed(() => page.props.locales?.find((l) => l.code === page.props.defaultLocale));

const sections = computed(() => [
    {
        label: 'Overview',
        items: [{ label: 'Dashboard', icon: 'dashboard', href: route('admin.dashboard'), match: '/admin' }],
    },
    {
        label: 'Content',
        items: [
            { label: 'Players', icon: 'users', href: route('admin.players.index'), match: '/admin/players' },
            { label: 'Trainers', icon: 'whistle', href: route('admin.trainers.index'), match: '/admin/trainers' },
            { label: 'Agency team', icon: 'users', href: route('admin.team.members.index'), match: '/admin/team' },
            { label: 'News', icon: 'newspaper', href: route('admin.news.index'), match: '/admin/news' },
        ],
    },
    {
        label: 'System',
        items: [
            {
                label: 'Messages',
                icon: 'mail',
                href: route('admin.messages.index'),
                match: '/admin/messages',
                badge: page.props.unreadCount || null,
            },
            { label: 'Settings', icon: 'settings', href: route('admin.settings.edit'), match: '/admin/settings' },
        ],
    },
]);

function isActive(item) {
    const path = new URL(page.url, window.location.origin).pathname.replace(/\/$/, '');

    if (item.match === '/admin') return path === '/admin';

    return path === item.match || path.startsWith(`${item.match}/`);
}

const flash = ref([]);

watch(
    () => page.props.flash,
    (value) => {
        const next = [];

        if (value?.success) next.push({ id: Math.random(), tone: 'success', text: value.success });
        if (value?.info) next.push({ id: Math.random(), tone: 'warning', text: value.info });
        if (value?.error) next.push({ id: Math.random(), tone: 'danger', text: value.error });

        flash.value = next;

        next
            .filter((item) => item.tone !== 'danger')
            .forEach((item) => {
                setTimeout(() => {
                    flash.value = flash.value.filter((entry) => entry.id !== item.id);
                }, 5000);
            });
    },
    { immediate: true, deep: true },
);

router.on('navigate', () => (sidebarOpen.value = false));

function logout() {
    router.post(route('admin.logout'));
}
</script>

<template>
    <div class="min-h-screen bg-bg">
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-border bg-bg-elevated transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-border px-5">
                <img v-if="site.logo" :src="site.logo" :alt="site.name" class="h-8 w-auto shrink-0">
                <Logo v-else variant="mark" :title="site.name" class="h-8 w-8 shrink-0" />

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold tracking-tight text-fg">{{ site.name }}</p>
                    <p class="text-[10px] uppercase tracking-wider text-fg-subtle">Admin panel</p>
                </div>

                <button
                    type="button"
                    class="ml-auto rounded-lg p-1.5 text-fg-subtle hover:bg-surface-2 hover:text-fg lg:hidden"
                    @click="sidebarOpen = false"
                >
                    <Icon name="close" :size="18" />
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto scrollbar-thin px-3 py-5">
                <div v-for="section in sections" :key="section.label">
                    <p class="px-2.5 pb-2 text-[10px] font-semibold uppercase tracking-wider text-fg-subtle">
                        {{ section.label }}
                    </p>
                    <ul class="space-y-0.5">
                        <li v-for="item in section.items" :key="item.href">
                            <Link
                                :href="item.href"
                                :class="[
                                    'flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors',
                                    isActive(item)
                                        ? 'bg-accent-soft text-accent'
                                        : 'text-fg-muted hover:bg-surface-2 hover:text-fg',
                                ]"
                            >
                                <Icon :name="item.icon" :size="16" />
                                <span class="flex-1 truncate">{{ item.label }}</span>
                                <span
                                    v-if="item.badge"
                                    class="rounded-full bg-danger px-1.5 text-[10px] font-semibold text-white"
                                >{{ item.badge }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="shrink-0 border-t border-border p-3">
                <a
                    :href="`/${page.props.defaultLocale}`"
                    target="_blank"
                    rel="noopener"
                    class="mb-2 flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-fg-muted transition-colors hover:bg-surface-2 hover:text-fg"
                >
                    <Icon name="externalLink" :size="16" />
                    View site
                </a>

                <div class="flex items-center gap-2.5 rounded-lg bg-surface-2 px-2.5 py-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent text-xs font-semibold text-accent-fg">
                        {{ (user?.name || '?').slice(0, 2).toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-fg">{{ user?.name }}</p>
                        <p class="truncate text-[11px] capitalize text-fg-subtle">{{ user?.role }}</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-3 hover:text-danger"
                        title="Log out"
                        @click="logout"
                    >
                        <Icon name="logout" :size="16" />
                    </button>
                </div>
            </div>
        </aside>

        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-overlay backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <div class="lg:pl-64">
            <header class="sticky top-0 z-30 border-b border-border bg-bg/85 backdrop-blur-lg">
                <div class="flex min-h-16 flex-wrap items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        class="rounded-lg border border-border p-2 text-fg-muted transition-colors hover:text-fg lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <Icon name="menu" :size="18" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 v-if="title" class="truncate text-lg font-semibold tracking-tight text-fg">{{ title }}</h1>
                        <p v-if="subtitle" class="truncate text-xs text-fg-muted">{{ subtitle }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <slot name="actions" />
                        <ThemeToggle />
                    </div>
                </div>
            </header>

            <div v-if="flash.length" class="space-y-2 px-4 pt-4 sm:px-6 lg:px-8">
                <Alert
                    v-for="item in flash"
                    :key="item.id"
                    :tone="item.tone"
                    dismissible
                    @dismiss="flash = flash.filter((entry) => entry.id !== item.id)"
                >{{ item.text }}</Alert>
            </div>

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
