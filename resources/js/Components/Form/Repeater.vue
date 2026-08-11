<script setup>
import { ref } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';
import Button from '@/Components/Ui/Button.vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    label: { type: String, default: null },
    hint: { type: String, default: null },
    addLabel: { type: String, default: 'Add row' },
    emptyLabel: { type: String, default: 'Nothing here yet.' },
    newRow: { type: Function, required: true },
    max: { type: Number, default: 100 },
    sortable: { type: Boolean, default: true },
    boxed: { type: Boolean, default: true },
    collapsible: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'add', 'remove']);

const draggingIndex = ref(null);
const overIndex = ref(null);
const collapsed = ref(new Set());

function update(rows) {
    emit('update:modelValue', rows);
}

function add() {
    if (props.modelValue.length >= props.max) return;

    const rows = [...props.modelValue, props.newRow()];
    update(rows);
    emit('add', rows.length - 1);
}

function remove(index) {
    const rows = [...props.modelValue];
    rows.splice(index, 1);
    update(rows);
    emit('remove', index);
}

function move(from, to) {
    if (to < 0 || to >= props.modelValue.length || from === to) return;

    const rows = [...props.modelValue];
    const [row] = rows.splice(from, 1);
    rows.splice(to, 0, row);
    update(rows);
}

function onDragStart(index, event) {
    draggingIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(index));
}

function onDragOver(index) {
    overIndex.value = index;
}

function onDrop(index) {
    if (draggingIndex.value !== null) move(draggingIndex.value, index);

    draggingIndex.value = null;
    overIndex.value = null;
}

function toggle(index) {
    const next = new Set(collapsed.value);
    next.has(index) ? next.delete(index) : next.add(index);
    collapsed.value = next;
}
</script>

<template>
    <div class="space-y-3">
        <div v-if="label || hint" class="flex flex-wrap items-baseline justify-between gap-2">
            <div>
                <p v-if="label" class="text-sm font-medium text-fg">{{ label }}</p>
                <p v-if="hint" class="mt-0.5 text-xs text-fg-subtle">{{ hint }}</p>
            </div>
            <span class="text-xs tabular-nums text-fg-subtle">{{ modelValue.length }}</span>
        </div>

        <p
            v-if="!modelValue.length"
            class="rounded-xl border border-dashed border-border px-4 py-6 text-center text-sm text-fg-subtle"
        >
            {{ emptyLabel }}
        </p>

        <ul v-else class="space-y-3">
            <li
                v-for="(row, index) in modelValue"
                :key="row.id ?? row._key ?? index"
                :class="[
                    'relative transition-colors',
                    boxed ? 'rounded-xl border bg-surface-2 p-4' : '',
                    boxed && overIndex === index && draggingIndex !== index ? 'border-accent' : 'border-border',
                    draggingIndex === index ? 'opacity-50' : '',
                ]"
                @dragover.prevent="onDragOver(index)"
                @drop.prevent="onDrop(index)"
            >
                <div class="mb-3 flex items-center gap-2">
                    <button
                        v-if="sortable"
                        type="button"
                        draggable="true"
                        class="cursor-grab rounded-md p-1 text-fg-subtle transition-colors hover:bg-surface-3 hover:text-fg active:cursor-grabbing"
                        title="Drag to reorder"
                        @dragstart="onDragStart(index, $event)"
                        @dragend="draggingIndex = null"
                    >
                        <Icon name="drag" :size="16" />
                    </button>

                    <span class="text-xs font-semibold tabular-nums text-fg-subtle">
                        {{ String(index + 1).padStart(2, '0') }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <slot name="summary" :row="row" :index="index" />
                    </div>

                    <div class="flex items-center gap-0.5">
                        <button
                            v-if="collapsible"
                            type="button"
                            class="rounded-md p-1 text-fg-subtle transition-colors hover:bg-surface-3 hover:text-fg"
                            @click="toggle(index)"
                        >
                            <Icon :name="collapsed.has(index) ? 'chevronDown' : 'chevronUp'" :size="15" />
                        </button>
                        <button
                            v-if="sortable"
                            type="button"
                            class="rounded-md p-1 text-fg-subtle transition-colors hover:bg-surface-3 hover:text-fg disabled:opacity-30"
                            :disabled="index === 0"
                            title="Move up"
                            @click="move(index, index - 1)"
                        >
                            <Icon name="chevronUp" :size="15" />
                        </button>
                        <button
                            v-if="sortable"
                            type="button"
                            class="rounded-md p-1 text-fg-subtle transition-colors hover:bg-surface-3 hover:text-fg disabled:opacity-30"
                            :disabled="index === modelValue.length - 1"
                            title="Move down"
                            @click="move(index, index + 1)"
                        >
                            <Icon name="chevronDown" :size="15" />
                        </button>
                        <button
                            type="button"
                            class="rounded-md p-1 text-fg-subtle transition-colors hover:bg-danger-soft hover:text-danger"
                            title="Remove"
                            @click="remove(index)"
                        >
                            <Icon name="trash" :size="15" />
                        </button>
                    </div>
                </div>

                <div v-show="!collapsible || !collapsed.has(index)">
                    <slot :row="row" :index="index" />
                </div>
            </li>
        </ul>

        <Button
            v-if="modelValue.length < max"
            variant="outline"
            size="sm"
            icon="plus"
            @click="add"
        >
            {{ addLabel }}
        </Button>
    </div>
</template>
