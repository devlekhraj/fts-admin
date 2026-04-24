<template>
  <v-btn color="primary" variant="flat" @click="open">
    <v-icon start>mdi-plus</v-icon>
    Add Bank
  </v-btn>
</template>

<script setup lang="ts">
import EmiBankFormModal from '@/components/emi/EmiBankFormModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    EmiBankFormModal,
    { mode: 'create' },
    {
      title: 'Add EMI Bank',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
