<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatTile from '@/Components/Viz/StatTile.vue';
import Card from '@/Components/Ui/Card.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';

defineProps({
    stats: { type: Object, required: true },
    activity: { type: Array, default: () => [] },
    recentMessages: { type: Array, default: () => [] },
    expiringContracts: { type: Array, default: () => [] },
});

const route = useRoute();

const actionTones = {
    created: 'success',
    updated: 'info',
    deleted: 'danger',
    published: 'accent',
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout title="Dashboard" subtitle="Everything at a glance.">
        <template #actions>
            <Button :href="route('admin.players.create')" size="sm" icon="plus">New player</Button>
        </template>

        <div class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <StatTile
                    label="Players"
                    :value="stats.players.total"
                    :sublabel="`${stats.players.published} published · ${stats.players.draft} draft`"
                    icon="users"
                    tone="accent"
                />
                <StatTile
                    label="Trainers"
                    :value="stats.trainers.total"
                    :sublabel="`${stats.trainers.published} published · ${stats.trainers.draft} draft`"
                    icon="whistle"
                />
                <StatTile
                    label="Team members"
                    :value="stats.team.total"
                    :sublabel="`${stats.team.published} published`"
                    icon="users"
                />
                <StatTile
                    label="News"
                    :value="stats.news.total"
                    :sublabel="`${stats.news.published} published · ${stats.news.featured} featured`"
                    icon="newspaper"
                />
                <StatTile
                    label="Messages"
                    :value="stats.messages.total"
                    :sublabel="`${stats.messages.unread} unread`"
                    icon="mail"
                    :tone="stats.messages.unread ? 'accent' : 'default'"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <Card padding="none">
                    <div class="flex items-center justify-between border-b border-border px-5 py-4">
                        <h2 class="text-sm font-semibold text-fg">Recent activity</h2>
                        <Icon name="clock" :size="15" class="text-fg-subtle" />
                    </div>

                    <ul v-if="activity.length" class="divide-y divide-border/60">
                        <li v-for="item in activity" :key="item.id" class="flex items-start gap-3 px-5 py-3.5">
                            <Badge :tone="actionTones[item.action] ?? 'neutral'" size="sm">{{ item.action }}</Badge>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-fg">{{ item.description }}</p>
                                <p class="mt-0.5 text-xs text-fg-subtle">
                                    {{ item.user ?? 'System' }} · {{ item.at }}
                                </p>
                            </div>
                        </li>
                    </ul>

                    <div v-else class="p-5">
                        <EmptyState
                            icon="clock"
                            title="No activity yet"
                            description="Changes made in the admin panel will show up here."
                        />
                    </div>
                </Card>

                <div class="space-y-6">
                    <Card padding="none">
                        <div class="flex items-center justify-between border-b border-border px-5 py-4">
                            <h2 class="text-sm font-semibold text-fg">Latest messages</h2>
                            <Link
                                :href="route('admin.messages.index')"
                                class="text-xs font-medium text-accent transition-colors hover:text-accent-hover"
                            >View all</Link>
                        </div>

                        <ul v-if="recentMessages.length" class="divide-y divide-border/60">
                            <li v-for="message in recentMessages" :key="message.id" class="px-5 py-3.5">
                                <div class="flex items-start gap-2">
                                    <span
                                        v-if="!message.is_read"
                                        class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent"
                                        title="Unread"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-fg">{{ message.name }}</p>
                                        <p class="truncate text-xs text-fg-muted">
                                            {{ message.subject || message.email }}
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-fg-subtle">{{ message.at }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <p v-else class="px-5 py-8 text-center text-sm text-fg-subtle">No messages yet.</p>
                    </Card>

                    <Card v-if="expiringContracts.length" padding="none">
                        <div class="flex items-center justify-between border-b border-border px-5 py-4">
                            <h2 class="text-sm font-semibold text-fg">Contracts expiring soon</h2>
                            <Icon name="warning" :size="15" class="text-warning" />
                        </div>

                        <ul class="divide-y divide-border/60">
                            <li v-for="player in expiringContracts" :key="player.id" class="flex items-center gap-3 px-5 py-3">
                                <img
                                    v-if="player.photo"
                                    :src="player.photo"
                                    :alt="player.name"
                                    class="h-8 w-8 shrink-0 rounded-full object-cover"
                                >
                                <span
                                    v-else
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-2 text-fg-subtle"
                                >
                                    <Icon name="users" :size="14" />
                                </span>

                                <Link
                                    :href="route('admin.players.edit', { player: player.id })"
                                    class="min-w-0 flex-1 truncate text-sm text-fg transition-colors hover:text-accent"
                                >{{ player.name }}</Link>

                                <span class="shrink-0 text-xs tabular-nums text-warning">{{ player.contract_until }}</span>
                            </li>
                        </ul>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
