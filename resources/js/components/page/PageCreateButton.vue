<template>
  <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="open">
    Create Page
  </v-btn>
</template>

<script setup lang="ts">
import PageCreateModal from '@/components/page/PageCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    PageCreateModal,
    {},
    {
      title: 'Create Page',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
