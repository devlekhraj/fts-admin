<template>
  <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="open">
    Create Category
  </v-btn>
</template>

<script setup lang="ts">
import BlogCategoryCreateModal from '@/components/blog/BlogCategoryCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    BlogCategoryCreateModal,
    {},
    {
      title: 'Create Blog Category',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
