<template>
  <v-btn size="small" variant="flat" color="error" @click="open">
     Delete
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
