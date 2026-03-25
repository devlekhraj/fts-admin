<template>
  <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="open">
    Add Brand
  </v-btn>
</template>

<script setup lang="ts">
import BrandCreateModal from '@/components/brand/BrandCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    BrandCreateModal,
    {},
    {
      title: 'Add Brand',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
