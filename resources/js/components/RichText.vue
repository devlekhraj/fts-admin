<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import { BulletList, OrderedList, ListItem } from '@tiptap/extension-list';
import { Placeholder } from '@tiptap/extensions';

const props = defineProps<{
  modelValue?: string;
  disabled?: boolean;
  placeholder?: string;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', v: string): void;
}>();

const placeholderText = computed(() => props.placeholder || 'Write something...');

const editor = useEditor({
  content: props.modelValue ?? '',
  editable: !props.disabled,
  extensions: [
    Document,
    Paragraph,
    Text,
    Bold,
    Italic,
    Underline,
    Link.configure({ openOnClick: false }),
    BulletList,
    OrderedList,
    ListItem,
    Placeholder.configure({
      placeholder: placeholderText.value,
    }),
  ],
  onUpdate: ({ editor: editorInstance }) => {
    emit('update:modelValue', editorInstance.getHTML());
  },
});

watch(
  () => props.modelValue,
  (value) => {
    if (!editor.value) return;
    const next = value ?? '';
    if (next !== editor.value.getHTML()) {
      editor.value.commands.setContent(next, { emitUpdate: false });
    }
  },
);

watch(
  () => props.disabled,
  (value) => {
    editor.value?.setEditable(!value);
  },
);

function setLink() {
  if (!editor.value) return;
  const previousUrl = editor.value.getAttributes('link').href as string | undefined;
  const url = window.prompt('Enter URL', previousUrl || '');
  if (url === null) return;
  if (url.trim() === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }
  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run();
}

onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>

<template>
  <div class="rich-text">
    <div class="rich-text__toolbar">
      <v-btn
        size="small"
        variant="text"
        icon="mdi-format-bold"
        :disabled="disabled"
        :color="editor?.isActive('bold') ? 'primary' : undefined"
        @click="editor?.chain().focus().toggleBold().run()"
      />
      <v-btn
        size="small"
        variant="text"
        icon="mdi-format-italic"
        :disabled="disabled"
        :color="editor?.isActive('italic') ? 'primary' : undefined"
        @click="editor?.chain().focus().toggleItalic().run()"
      />
      <v-btn
        size="small"
        variant="text"
        icon="mdi-format-underline"
        :disabled="disabled"
        :color="editor?.isActive('underline') ? 'primary' : undefined"
        @click="editor?.chain().focus().toggleUnderline().run()"
      />
      <v-btn
        size="small"
        variant="text"
        icon="mdi-format-list-bulleted"
        :disabled="disabled"
        :color="editor?.isActive('bulletList') ? 'primary' : undefined"
        @click="editor?.chain().focus().toggleBulletList().run()"
      />
      <v-btn
        size="small"
        variant="text"
        icon="mdi-format-list-numbered"
        :disabled="disabled"
        :color="editor?.isActive('orderedList') ? 'primary' : undefined"
        @click="editor?.chain().focus().toggleOrderedList().run()"
      />
      <v-btn
        size="small"
        variant="text"
        icon="mdi-link-variant"
        :disabled="disabled"
        :color="editor?.isActive('link') ? 'primary' : undefined"
        @click="setLink"
      />
      <v-btn
        size="small"
        variant="text"
        icon="mdi-format-clear"
        :disabled="disabled"
        @click="editor?.chain().focus().unsetAllMarks().clearNodes().run()"
      />
    </div>
    <EditorContent :editor="editor" class="rich-text__content" />
  </div>
</template>

<style scoped>
.rich-text {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  background: rgb(var(--v-theme-surface));
}

.rich-text__toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  padding: 6px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.rich-text__content :deep(.tiptap) {
  min-height: 180px;
  padding: 12px;
  outline: none;
}

.rich-text__content :deep(.tiptap p.is-editor-empty:first-child::before) {
  content: attr(data-placeholder);
  color: rgba(var(--v-theme-on-surface), 0.45);
  float: left;
  pointer-events: none;
  height: 0;
}
</style>
