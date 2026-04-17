<template>
  <v-btn size="small" variant="flat" color="error" @click="open">
    Delete
  </v-btn>
</template>

<script setup lang="ts">
import BlogDeleteModal from '@/components/blog/BlogDeleteModal.vue';
import { openModal } from '@/shared/modal';

type Blog = {
  id?: number | string;
  title?: string | null;
  slug?: string | null;
};

const props = defineProps<{ blog: Blog }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    BlogDeleteModal,
    { blog: props.blog },
    {
      title: 'Confirm Blog Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
