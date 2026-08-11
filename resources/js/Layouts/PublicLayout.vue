<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import Icon from '@/Components/Ui/Icon.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';
import LocaleSwitcher from '@/Components/Ui/LocaleSwitcher.vue';
import Alert from '@/Components/Ui/Alert.vue';
import Logo from '@/Components/Site/Logo.vue';

const page = usePage();
const { t } = useI18n();
const route = useRoute();

const mobileOpen = ref(false);
const scrolled = ref(false);

const site = computed(() => page.props.site ?? {});

const nav = computed(() => [
    { label: t('nav.home'), href: route('public.home'), name: 'public.home' },
    { label: t('nav.players'), href: route('public.players.index'), name: 'public.players' },
    { label: t('nav.trainers'), href: route('public.trainers.index'), name: 'public.trainers' },
    { label: t('nav.team'), href: route('public.team'), name: 'public.team' },
    { label: t('nav.news'), href: route('public.news.index'), name: 'public.news' },
    { label: t('nav.about'), href: route('public.about'), name: 'public.about' },
    { label: t('nav.contacts'), href: route('public.contacts'), name: 'public.contacts' },
]);

function isActive(item) {
    const path = new URL(page.url, window.location.origin).pathname.replace(/\/$/, '');
    const target = new URL(item.href, window.location.origin).pathname.replace(/\/$/, '');

    if (item.name === 'public.home') return path === target;

    return path === target || path.startsWith(`${target}/`);
}

const socialIcons = {
    instagram: 'instagram',
    facebook: 'facebook',
    linkedin: 'linkedin',
    twitter: 'twitter',
    x: 'twitter',
    youtube: 'youtube',
};

const flash = ref(null);

watch(
    () => page.props.flash,
    (value) => {
        flash.value = value?.success ? { tone: 'success', text: value.success } : null;

        if (flash.value) setTimeout(() => (flash.value = null), 6000);
    },
    { immediate: true, deep: true },
);

function onScroll() {
    scrolled.value = window.scrollY > 8;
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

router.on('navigate', () => (mobileOpen.value = false));

const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-bg">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-accent-fg"
        >{{ t('common.skip_to_content') }}</a>

        <header
            :class="[
                'sticky top-0 z-40 border-b transition-colors duration-200',
                scrolled ? 'border-border bg-bg/85 backdrop-blur-lg' : 'border-transparent bg-bg',
            ]"
        >
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:h-[4.5rem] lg:px-8">
                <Link :href="route('public.home')" class="flex min-w-0 items-center" :aria-label="site.name">
                    <img v-if="site.logo" :src="site.logo" :alt="site.name" class="h-9 w-auto">
                    <Logo v-else :title="site.name" class="h-9 w-auto max-w-[13rem] sm:max-w-none" />
                </Link>

                <nav class="ml-auto hidden items-center gap-1 lg:flex" :aria-label="t('nav.menu')">
                    <Link
                        v-for="item in nav"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'relative rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                            isActive(item) ? 'text-accent' : 'text-fg-muted hover:text-fg',
                        ]"
                    >
                        {{ item.label }}
                        <span
                            v-if="isActive(item)"
                            class="absolute inset-x-3 -bottom-0.5 h-0.5 rounded-full bg-accent"
                        />
                    </Link>
                </nav>

                <div class="ml-auto flex shrink-0 items-center gap-2 lg:ml-2">
                    <LocaleSwitcher />
                    <ThemeToggle />

                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border text-fg-muted transition-colors hover:border-accent/50 hover:text-accent lg:hidden"
                        :aria-expanded="mobileOpen"
                        :aria-label="t('nav.menu')"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <Icon :name="mobileOpen ? 'close' : 'menu'" :size="18" />
                    </button>
                </div>
            </div>

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <nav v-if="mobileOpen" class="border-t border-border bg-bg-elevated lg:hidden" :aria-label="t('nav.menu')">
                    <div class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6">
                        <Link
                            v-for="item in nav"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                'flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                                isActive(item) ? 'bg-accent-soft text-accent' : 'text-fg-muted hover:bg-surface-2 hover:text-fg',
                            ]"
                        >
                            {{ item.label }}
                            <Icon name="chevronRight" :size="15" />
                        </Link>
                    </div>
                </nav>
            </Transition>
        </header>

        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div v-if="flash" class="fixed inset-x-0 top-20 z-50 mx-auto max-w-md px-4">
                <Alert :tone="flash.tone" dismissible @dismiss="flash = null">{{ flash.text }}</Alert>
            </div>
        </Transition>

        <main id="main" class="flex-1 [&>section:last-child]:mb-0">
            <slot />
        </main>

        <footer class="border-t border-border bg-bg-elevated">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="grid gap-10 md:grid-cols-[1.5fr_1fr_1fr]">
                    <div>
                        <Link :href="route('public.home')" class="flex items-center" :aria-label="site.name">
                            <img v-if="site.logo" :src="site.logo" :alt="site.name" class="h-10 w-auto">
                            <Logo v-else :title="site.name" class="h-10 w-auto" />
                        </Link>

                        <p class="mt-4 max-w-sm text-sm uppercase leading-relaxed tracking-wider text-accent">
                            {{ site.tagline || t('footer.tagline') }}
                        </p>

                        <div v-if="site.socials?.length" class="mt-5 flex items-center gap-1.5">
                            <a
                                v-for="link in site.socials"
                                :key="link.url"
                                :href="link.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-lg border border-border p-2 text-fg-muted transition-colors hover:border-accent hover:text-accent"
                                :aria-label="link.platform"
                            >
                                <Icon :name="socialIcons[link.platform] ?? 'link'" :size="16" />
                            </a>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-fg-subtle">
                            {{ t('footer.quick_links') }}
                        </h2>
                        <ul class="mt-4 space-y-2.5">
                            <li v-for="item in nav.slice(1)" :key="item.name">
                                <Link :href="item.href" class="text-sm text-fg-muted transition-colors hover:text-accent">
                                    {{ item.label }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-fg-subtle">
                            {{ t('footer.contact') }}
                        </h2>
                        <ul class="mt-4 space-y-2.5 text-sm text-fg-muted">
                            <li v-if="site.address" class="flex items-start gap-2">
                                <Icon name="location" :size="15" class="mt-0.5 text-fg-subtle" />
                                <span>{{ site.address }}</span>
                            </li>
                            <li v-if="site.phone" class="flex items-center gap-2">
                                <Icon name="phone" :size="15" class="text-fg-subtle" />
                                <a :href="`tel:${site.phone}`" class="transition-colors hover:text-accent">{{ site.phone }}</a>
                            </li>
                            <li v-if="site.email" class="flex items-center gap-2">
                                <Icon name="mail" :size="15" class="text-fg-subtle" />
                                <a :href="`mailto:${site.email}`" class="transition-colors hover:text-accent">{{ site.email }}</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-border pt-6 sm:flex-row">
                    <p class="text-xs text-fg-subtle">
                        © {{ year }} {{ site.copyright || site.name }}. {{ t('footer.rights') }}
                    </p>
                    <LocaleSwitcher compact align="left" />
                </div>
            </div>
        </footer>
    </div>
</template>
