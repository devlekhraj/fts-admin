<template>
  <v-btn size="small" variant="tonal" color="error" @click="open">
    Delete
  </v-btn>
</template>

<script setup lang="ts">
import EmiBankDeleteModal from '@/components/emi/EmiBankDeleteModal.vue';
import { openModal } from '@/shared/modal';

type EmiBank = {
  id?: number | string;
  name?: string | null;
  code?: string | null;
};

const props = defineProps<{ bank: EmiBank }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    EmiBankDeleteModal,
    { bank: props.bank },
    {
      title: 'Confirm EMI Bank Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
