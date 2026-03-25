<template>
  <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="open">
    Add Category
  </v-btn>
</template>

<script setup lang="ts">
import ProductCategoryCreateModal from '@/components/category/ProductCategoryCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    ProductCategoryCreateModal,
    {},
    {
      title: 'Add Product Category',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
