<template>
  <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="open">
    Create Blog
  </v-btn>
</template>

<script setup lang="ts">
import BlogCreateModal from '@/components/blog/BlogCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    BlogCreateModal,
    {},
    {
      title: 'Create Blog',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
