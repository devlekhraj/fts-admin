<template>
  <v-btn icon size="x-small" variant="tonal" color="error" @click="open">
    <v-icon size="16">mdi-delete</v-icon>
  </v-btn>
</template>

<script setup lang="ts">
import PageDeleteModal from '@/components/page/PageDeleteModal.vue';
import { openModal } from '@/shared/modal';

type PageItem = {
  id?: number | string;
  title?: string | null;
  slug?: string | null;
};

const props = defineProps<{ page: PageItem }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    PageDeleteModal,
    { page: props.page },
    {
      title: 'Confirm Page Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
