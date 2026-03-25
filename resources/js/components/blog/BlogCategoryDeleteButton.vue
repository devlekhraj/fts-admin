<template>
  <v-btn icon size="x-small" variant="tonal" color="error" @click="open">
    <v-icon size="16">mdi-delete</v-icon>
  </v-btn>
</template>

<script setup lang="ts">
import BlogCategoryDeleteModal from '@/components/blog/BlogCategoryDeleteModal.vue';
import { openModal } from '@/shared/modal';

type BlogCategory = {
  id?: number | string;
  title?: string | null;
  slug?: string | null;
};

const props = defineProps<{ category: BlogCategory }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    BlogCategoryDeleteModal,
    { category: props.category },
    {
      title: 'Confirm Category Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
