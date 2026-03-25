<template>
  <v-btn icon size="x-small" variant="tonal" color="error" @click="open">
    <v-icon size="16">mdi-delete</v-icon>
  </v-btn>
</template>

<script setup lang="ts">
import ProductCategoryDeleteModal from '@/components/category/ProductCategoryDeleteModal.vue';
import { openModal } from '@/shared/modal';

type Category = {
  id?: number | string;
  title?: string | null;
  slug?: string | null;
};

const props = defineProps<{ category: Category }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    ProductCategoryDeleteModal,
    { category: props.category },
    {
      title: 'Confirm Category Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
