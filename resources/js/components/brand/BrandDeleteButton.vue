<template>
  <v-btn icon size="x-small" variant="tonal" color="error" @click="open">
    <v-icon size="16">mdi-delete</v-icon>
  </v-btn>
</template>

<script setup lang="ts">
import BrandDeleteModal from '@/components/brand/BrandDeleteModal.vue';
import { openModal } from '@/shared/modal';

type Brand = {
  id?: number | string;
  name?: string | null;
  slug?: string | null;
};

const props = defineProps<{ brand: Brand }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    BrandDeleteModal,
    { brand: props.brand },
    {
      title: 'Confirm Brand Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
