<template>
  <v-btn size="small" variant="flat" color="error" @click="open">
    Delete
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
