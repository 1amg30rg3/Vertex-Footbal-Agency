<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import Image from '@tiptap/extension-image';
import { Placeholder } from '@tiptap/extensions';
import Icon from '@/Components/Ui/Icon.vue';
import Modal from '@/Components/Ui/Modal.vue';
import Button from '@/Components/Ui/Button.vue';
import TextInput from './TextInput.vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Write something…' },
    invalid: { type: Boolean, default: false },
    minHeight: { type: String, default: '12rem' },
    compact: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const linkModal = ref(false);
const linkUrl = ref('');
const imageModal = ref(false);
const imageUrl = ref('');

editor.value = new Editor({
    content: props.modelValue || '',
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3, 4] },
            link: {
                openOnClick: false,
                autolink: true,
                HTMLAttributes: { rel: 'noopener noreferrer', target: '_blank' },
            },
        }),
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        Image.configure({ inline: false, HTMLAttributes: { class: 'rounded-xl' } }),
        Placeholder.configure({ placeholder: () => props.placeholder }),
    ],
    editorProps: {
        attributes: {
            class: 'prose-content tiptap px-4 py-3 focus:outline-none',
            style: `min-height:${props.minHeight}`,
        },
    },
    onUpdate: ({ editor: instance }) => {
        const html = instance.getHTML();

        emit('update:modelValue', html === '<p></p>' ? '' : html);
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) return;

        const incoming = value || '';
        const current = editor.value.getHTML();

        if (incoming !== current && !(incoming === '' && current === '<p></p>')) {
            editor.value.commands.setContent(incoming, { emitUpdate: false });
        }
    },
);

onBeforeUnmount(() => editor.value?.destroy());

function openLink() {
    linkUrl.value = editor.value?.getAttributes('link').href ?? '';
    linkModal.value = true;
}

function applyLink() {
    const chain = editor.value.chain().focus().extendMarkRange('link');

    linkUrl.value.trim()
        ? chain.setLink({ href: linkUrl.value.trim() }).run()
        : chain.unsetLink().run();

    linkModal.value = false;
}

function applyImage() {
    if (imageUrl.value.trim()) {
        editor.value.chain().focus().setImage({ src: imageUrl.value.trim() }).run();
    }

    imageUrl.value = '';
    imageModal.value = false;
}

const groups = [
    [
        { name: 'bold', icon: null, text: 'B', className: 'font-bold', action: (e) => e.chain().focus().toggleBold().run(), active: 'bold' },
        { name: 'italic', text: 'I', className: 'italic font-serif', action: (e) => e.chain().focus().toggleItalic().run(), active: 'italic' },
        { name: 'underline', text: 'U', className: 'underline', action: (e) => e.chain().focus().toggleUnderline().run(), active: 'underline' },
        { name: 'strike', text: 'S', className: 'line-through', action: (e) => e.chain().focus().toggleStrike().run(), active: 'strike' },
    ],
    [
        { name: 'h2', text: 'H2', action: (e) => e.chain().focus().toggleHeading({ level: 2 }).run(), active: 'heading', attrs: { level: 2 } },
        { name: 'h3', text: 'H3', action: (e) => e.chain().focus().toggleHeading({ level: 3 }).run(), active: 'heading', attrs: { level: 3 } },
    ],
    [
        { name: 'bulletList', text: '••', action: (e) => e.chain().focus().toggleBulletList().run(), active: 'bulletList' },
        { name: 'orderedList', text: '1.', action: (e) => e.chain().focus().toggleOrderedList().run(), active: 'orderedList' },
        { name: 'blockquote', icon: 'quote', action: (e) => e.chain().focus().toggleBlockquote().run(), active: 'blockquote' },
    ],
    [
        { name: 'alignLeft', text: '⯇', action: (e) => e.chain().focus().setTextAlign('left').run(), active: null, alignment: 'left' },
        { name: 'alignCenter', text: '≡', action: (e) => e.chain().focus().setTextAlign('center').run(), active: null, alignment: 'center' },
        { name: 'alignRight', text: '⯈', action: (e) => e.chain().focus().setTextAlign('right').run(), active: null, alignment: 'right' },
    ],
];

function isActive(button) {
    if (!editor.value) return false;
    if (button.alignment) return editor.value.isActive({ textAlign: button.alignment });
    if (!button.active) return false;

    return editor.value.isActive(button.active, button.attrs);
}

function visibleGroups() {
    return props.compact ? groups.slice(0, 3) : groups;
}
</script>

<template>
    <div
        :class="[
            'overflow-hidden rounded-lg border bg-bg-elevated transition-colors focus-within:ring-2 focus-within:ring-accent-ring',
            invalid ? 'border-danger' : 'border-border focus-within:border-accent',
        ]"
    >
        <div class="flex flex-wrap items-center gap-1 border-b border-border bg-surface-2 px-2 py-1.5">
            <template v-for="(group, index) in visibleGroups()" :key="index">
                <div class="flex items-center gap-0.5">
                    <button
                        v-for="button in group"
                        :key="button.name"
                        type="button"
                        :title="button.name"
                        :class="[
                            'inline-flex h-7 min-w-7 items-center justify-center rounded-md px-1.5 text-xs transition-colors',
                            isActive(button)
                                ? 'bg-accent text-accent-fg'
                                : 'text-fg-muted hover:bg-surface-3 hover:text-fg',
                            button.className,
                        ]"
                        @click="button.action(editor)"
                    >
                        <Icon v-if="button.icon" :name="button.icon" :size="14" />
                        <span v-else>{{ button.text }}</span>
                    </button>
                </div>
                <span v-if="index < visibleGroups().length - 1" class="mx-0.5 h-4 w-px bg-border" />
            </template>

            <span class="mx-0.5 h-4 w-px bg-border" />

            <button
                type="button"
                title="Link"
                :class="[
                    'inline-flex h-7 w-7 items-center justify-center rounded-md transition-colors',
                    editor?.isActive('link') ? 'bg-accent text-accent-fg' : 'text-fg-muted hover:bg-surface-3 hover:text-fg',
                ]"
                @click="openLink"
            >
                <Icon name="link" :size="14" />
            </button>

            <button
                v-if="!compact"
                type="button"
                title="Image"
                class="inline-flex h-7 w-7 items-center justify-center rounded-md text-fg-muted transition-colors hover:bg-surface-3 hover:text-fg"
                @click="imageModal = true"
            >
                <Icon name="image" :size="14" />
            </button>

            <div class="ml-auto flex items-center gap-0.5">
                <button
                    type="button"
                    title="Undo"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-fg-muted transition-colors hover:bg-surface-3 hover:text-fg disabled:opacity-40"
                    :disabled="!editor?.can().undo()"
                    @click="editor.chain().focus().undo().run()"
                >
                    <Icon name="arrowLeft" :size="14" />
                </button>
                <button
                    type="button"
                    title="Redo"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-fg-muted transition-colors hover:bg-surface-3 hover:text-fg disabled:opacity-40"
                    :disabled="!editor?.can().redo()"
                    @click="editor.chain().focus().redo().run()"
                >
                    <Icon name="arrowRight" :size="14" />
                </button>
            </div>
        </div>

        <EditorContent :editor="editor" />

        <Modal :open="linkModal" title="Insert link" size="sm" @close="linkModal = false">
            <TextInput v-model="linkUrl" placeholder="https://example.com" autofocus @keydown.enter.prevent="applyLink" />
            <p class="mt-2 text-xs text-fg-subtle">Leave empty to remove the link.</p>
            <template #footer>
                <Button variant="ghost" @click="linkModal = false">Cancel</Button>
                <Button @click="applyLink">Apply</Button>
            </template>
        </Modal>

        <Modal :open="imageModal" title="Insert image" size="sm" @close="imageModal = false">
            <TextInput v-model="imageUrl" placeholder="https://…/photo.jpg" @keydown.enter.prevent="applyImage" />
            <p class="mt-2 text-xs text-fg-subtle">
                Paste the URL of an already-uploaded image. Use the media fields on the form for new uploads.
            </p>
            <template #footer>
                <Button variant="ghost" @click="imageModal = false">Cancel</Button>
                <Button @click="applyImage">Insert</Button>
            </template>
        </Modal>
    </div>
</template>
