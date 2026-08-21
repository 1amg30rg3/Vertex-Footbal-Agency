<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import Icon from '@/Components/Ui/Icon.vue';
import LocaleSwitcher from '@/Components/Ui/LocaleSwitcher.vue';
import Alert from '@/Components/Ui/Alert.vue';
import Logo from '@/Components/Site/Logo.vue';

const page = usePage();
const { t } = useI18n();
const route = useRoute();

const mobileOpen = ref(false);
const scrolled = ref(false);
const progress = ref(0);

const navRef = ref(null);
const hovered = ref(-1);
const metrics = ref([]);

const site = computed(() => page.props.site ?? {});

const nav = computed(() => [
    { label: t('nav.home'), href: route('public.home'), name: 'public.home', icon: 'home' },
    { label: t('nav.players'), href: route('public.players.index'), name: 'public.players', icon: 'ball' },
    { label: t('nav.trainers'), href: route('public.trainers.index'), name: 'public.trainers', icon: 'whistle' },
    { label: t('nav.team'), href: route('public.team'), name: 'public.team', icon: 'users' },
    { label: t('nav.news'), href: route('public.news.index'), name: 'public.news', icon: 'newspaper' },
    { label: t('nav.about'), href: route('public.about'), name: 'public.about', icon: 'info' },
    { label: t('nav.contacts'), href: route('public.contacts'), name: 'public.contacts', icon: 'mail' },
]);

function isActive(item) {
    const path = new URL(page.url, window.location.origin).pathname.replace(/\/$/, '');
    const target = new URL(item.href, window.location.origin).pathname.replace(/\/$/, '');

    if (item.name === 'public.home') return path === target;

    return path === target || path.startsWith(`${target}/`);
}

const activeIndex = computed(() => nav.value.findIndex((item) => isActive(item)));

const hoverPill = computed(() => {
    const box = metrics.value[hovered.value] ?? metrics.value[activeIndex.value];

    if (!box) return { opacity: '0' };

    return {
        opacity: hovered.value >= 0 ? '1' : '0',
        width: `${box.width}px`,
        transform: `translate3d(${box.left}px, 0, 0)`,
    };
});

const activeBar = computed(() => {
    const box = metrics.value[activeIndex.value];

    if (!box) return { opacity: '0' };

    return {
        opacity: '1',
        width: `${box.width}px`,
        transform: `translate3d(${box.left}px, 0, 0)`,
    };
});

function measure() {
    if (!navRef.value) return;

    const origin = navRef.value.getBoundingClientRect().left;

    metrics.value = [...navRef.value.querySelectorAll('[data-nav-item]')].map((el) => {
        const rect = el.getBoundingClientRect();

        return { left: rect.left - origin, width: rect.width };
    });
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

let observer = null;

function onScroll() {
    scrolled.value = window.scrollY > 8;

    const runway = document.documentElement.scrollHeight - window.innerHeight;

    progress.value = runway > 0 ? Math.min(window.scrollY / runway, 1) : 0;
}

function onResize() {
    measure();
}

function onKeydown(event) {
    if (event.key === 'Escape') mobileOpen.value = false;
}

watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

watch(() => page.url, () => nextTick(measure));

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
    document.addEventListener('keydown', onKeydown);
    onScroll();

    nextTick(measure);
    document.fonts?.ready.then(measure).catch(() => {});

    if (navRef.value) {
        observer = new ResizeObserver(measure);
        observer.observe(navRef.value);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onResize);
    document.removeEventListener('keydown', onKeydown);
    observer?.disconnect();
    document.body.style.overflow = '';
});

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
                'sticky top-0 z-40 border-b transition-[background-color,border-color,box-shadow] duration-300',
                scrolled
                    ? 'border-border bg-bg/80 shadow-[0_1px_24px_-12px_rgba(0,0,0,0.6)] backdrop-blur-xl'
                    : 'border-transparent bg-bg',
            ]"
        >
            <div
                :class="[
                    'mx-auto flex max-w-7xl items-center gap-4 px-4 transition-[height] duration-300 sm:px-6 lg:px-8',
                    scrolled ? 'h-16 xl:h-[4.5rem]' : 'h-16 xl:h-20',
                ]"
            >
                <Link
                    :href="route('public.home')"
                    class="group flex min-w-0 items-center"
                    :aria-label="site.name"
                >
                    <img
                        v-if="site.logo"
                        :src="site.logo"
                        :alt="site.name"
                        :class="['w-auto origin-left transition-transform duration-300 group-hover:scale-[1.03]', scrolled ? 'h-8' : 'h-9']"
                    >
                    <Logo
                        v-else
                        :title="site.name"
                        :class="['w-auto max-w-[13rem] origin-left transition-transform duration-300 group-hover:scale-[1.03] sm:max-w-none', scrolled ? 'h-8' : 'h-9']"
                    />
                </Link>

                <nav
                    ref="navRef"
                    class="relative ml-auto hidden items-center gap-0.5 xl:flex"
                    :aria-label="t('nav.menu')"
                    @mouseleave="hovered = -1"
                >
                    <span
                        class="nav-slide pointer-events-none absolute inset-y-1 left-0 rounded-xl bg-surface-2"
                        :style="hoverPill"
                        aria-hidden="true"
                    />
                    <span
                        class="nav-slide pointer-events-none absolute -bottom-px left-0 h-0.5 rounded-full bg-accent"
                        :style="activeBar"
                        aria-hidden="true"
                    />

                    <Link
                        v-for="(item, index) in nav"
                        :key="item.name"
                        :href="item.href"
                        data-nav-item
                        :aria-current="isActive(item) ? 'page' : undefined"
                        :class="[
                            'group relative inline-flex items-center gap-1.5 whitespace-nowrap rounded-xl px-2.5 py-2.5',
                            'text-[11px] font-semibold uppercase tracking-[0.1em] transition-colors duration-200',
                            isActive(item) ? 'text-accent' : 'text-fg-muted hover:text-fg',
                        ]"
                        @mouseenter="hovered = index"
                        @focus="hovered = index"
                        @blur="hovered = -1"
                    >
                        <Icon
                            :name="item.icon"
                            :size="14"
                            :class="[
                                'transition-transform duration-300 group-hover:-translate-y-px',
                                isActive(item) ? '' : 'opacity-70',
                            ]"
                        />
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="ml-auto flex shrink-0 items-center gap-2 xl:ml-4">
                    <span class="mr-1 hidden h-6 w-px bg-border xl:block" aria-hidden="true" />

                    <LocaleSwitcher />

                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border text-fg-muted transition-colors hover:border-accent/50 hover:text-accent xl:hidden"
                        :aria-expanded="mobileOpen"
                        :aria-label="t('nav.menu')"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <span class="relative block h-3.5 w-4.5" aria-hidden="true">
                            <span
                                :class="[
                                    'absolute left-0 h-0.5 w-full rounded-full bg-current transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]',
                                    mobileOpen ? 'top-1/2 -translate-y-1/2 rotate-45' : 'top-0',
                                ]"
                            />
                            <span
                                :class="[
                                    'absolute left-0 h-0.5 w-full rounded-full bg-current transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]',
                                    mobileOpen ? 'top-1/2 -translate-y-1/2 -rotate-45' : 'bottom-0',
                                ]"
                            />
                        </span>
                    </button>
                </div>
            </div>

            <div class="absolute inset-x-0 bottom-0 h-px overflow-hidden" aria-hidden="true">
                <div
                    class="h-full origin-left bg-gradient-to-r from-accent/40 via-accent to-accent-hover transition-opacity duration-300"
                    :style="{ transform: `scaleX(${progress})`, opacity: scrolled ? 1 : 0 }"
                />
            </div>

            <Transition
                enter-active-class="transition-[opacity,translate] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                enter-from-class="opacity-0 -translate-y-3"
                leave-active-class="transition-[opacity,translate] duration-200 ease-in"
                leave-to-class="opacity-0 -translate-y-3"
            >
                <nav
                    v-if="mobileOpen"
                    class="relative z-10 border-t border-border bg-bg-elevated shadow-[0_20px_40px_-24px_rgba(0,0,0,0.7)] xl:hidden"
                    :aria-label="t('nav.menu')"
                >
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6">
                        <div class="grid gap-1 sm:grid-cols-2">
                            <Link
                                v-for="(item, index) in nav"
                                :key="item.name"
                                :href="item.href"
                                :aria-current="isActive(item) ? 'page' : undefined"
                                :style="{ animationDelay: `${index * 35}ms` }"
                                :class="[
                                    'nav-stagger group relative flex items-center gap-3 overflow-hidden rounded-xl px-3.5 py-3',
                                    'text-sm font-medium transition-colors duration-200',
                                    isActive(item) ? 'bg-accent-soft text-accent' : 'text-fg-muted hover:bg-surface-2 hover:text-fg',
                                ]"
                            >
                                <span
                                    v-if="isActive(item)"
                                    class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-accent"
                                    aria-hidden="true"
                                />
                                <Icon
                                    :name="item.icon"
                                    :size="17"
                                    :class="isActive(item) ? '' : 'text-fg-subtle transition-colors group-hover:text-accent'"
                                />
                                <span class="flex-1">{{ item.label }}</span>
                                <Icon
                                    name="chevronRight"
                                    :size="15"
                                    class="opacity-40 transition-transform duration-300 group-hover:translate-x-0.5"
                                />
                            </Link>
                        </div>

                        <div
                            class="nav-stagger mt-4 border-t border-border pt-4"
                            :style="{ animationDelay: `${nav.length * 35}ms` }"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-fg-subtle">
                                {{ site.tagline || t('footer.tagline') }}
                            </p>

                            <div v-if="site.socials?.length" class="mt-4 flex items-center gap-1.5">
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
                    </div>
                </nav>
            </Transition>

            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
            >
                <button
                    v-if="mobileOpen"
                    type="button"
                    class="fixed inset-0 top-16 -z-10 cursor-default bg-overlay backdrop-blur-sm xl:hidden"
                    :aria-label="t('common.close')"
                    tabindex="-1"
                    @click="mobileOpen = false"
                />
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
                <div class="grid gap-10 md:grid-cols-[1fr_auto]">
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
