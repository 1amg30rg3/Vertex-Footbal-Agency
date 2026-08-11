<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';
import Spinner from '@/Components/Ui/Spinner.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    rowKey: { type: String, default: 'id' },
    sort: { type: String, default: null },
    direction: { type: String, default: 'asc' },
    loading: { type: Boolean, default: false },
    emptyTitle: { type: String, default: 'Nothing here yet' },
    emptyDescription: { type: String, default: null },
    reorderable: { type: Boolean, default: false },
});

const emit = defineEmits(['sort', 'reorder', 'row-click']);

let draggingId = null;

const alignments = { left: 'text-left', center: 'text-center', right: 'text-right' };

const gridColumns = computed(() => props.columns.length + (props.reorderable ? 1 : 0));

function toggleSort(column) {
    if (!column.sortable) return;

    const nextDirection = props.sort === column.key && props.direction === 'asc' ? 'desc' : 'asc';

    emit('sort', { sort: column.key, direction: nextDirection });
}

function onDragStart(row, event) {
    draggingId = row[props.rowKey];
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(draggingId));
}

function onDrop(target) {
    if (draggingId == null || draggingId === target[props.rowKey]) return;

    const ids = props.rows.map((row) => row[props.rowKey]);
    const from = ids.indexOf(draggingId);
    const to = ids.indexOf(target[props.rowKey]);

    ids.splice(to, 0, ids.splice(from, 1)[0]);
    draggingId = null;

    emit('reorder', ids);
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-border bg-surface">
        <div class="relative overflow-x-auto scrollbar-thin">
            <table class="w-full min-w-[40rem] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-2 text-left">
                        <th v-if="reorderable" class="w-10" />
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-4 py-3 text-xs font-semibold uppercase tracking-wide text-fg-subtle',
                                alignments[column.align ?? 'left'],
                                column.width,
                            ]"
                        >
                            <button
                                v-if="column.sortable"
                                type="button"
                                class="inline-flex items-center gap-1 transition-colors hover:text-fg"
                                @click="toggleSort(column)"
                            >
                                {{ column.label }}
                                <Icon
                                    :name="sort === column.key && direction === 'desc' ? 'chevronDown' : 'chevronUp'"
                                    :size="12"
                                    :class="sort === column.key ? 'text-accent' : 'opacity-40'"
                                />
                            </button>
                            <span v-else>{{ column.label }}</span>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="loading">
                        <td :colspan="gridColumns" class="px-4 py-12">
                            <div class="flex justify-center"><Spinner /></div>
                        </td>
                    </tr>

                    <tr v-else-if="!rows.length">
                        <td :colspan="gridColumns" class="p-6">
                            <EmptyState :title="emptyTitle" :description="emptyDescription">
                                <slot name="empty-action" />
                            </EmptyState>
                        </td>
                    </tr>

                    <tr
                        v-for="row in rows"
                        v-else
                        :key="row[rowKey]"
                        class="border-b border-border/60 transition-colors last:border-0 hover:bg-surface-2"
                        @dragover.prevent
                        @drop.prevent="reorderable && onDrop(row)"
                    >
                        <td v-if="reorderable" class="pl-3">
                            <span
                                draggable="true"
                                class="inline-flex cursor-grab rounded p-1 text-fg-subtle transition-colors hover:text-fg active:cursor-grabbing"
                                title="Drag to reorder"
                                @dragstart="onDragStart(row, $event)"
                            >
                                <Icon name="drag" :size="15" />
                            </span>
                        </td>

                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="['px-4 py-3 align-middle', alignments[column.align ?? 'left'], column.class]"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                <span :class="column.muted ? 'text-fg-muted' : 'text-fg'">
                                    {{ row[column.key] ?? '—' }}
                                </span>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="$slots.footer" class="border-t border-border px-4 py-3">
            <slot name="footer" />
        </div>
    </div>
</template>
