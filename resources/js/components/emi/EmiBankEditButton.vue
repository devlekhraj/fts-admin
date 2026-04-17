<template>
  <v-btn size="small" variant="flat" color="primary" @click="open">
    Edit 
  </v-btn>
</template>

<script setup lang="ts">
import EmiBankFormModal from '@/components/emi/EmiBankFormModal.vue';
import { openModal } from '@/shared/modal';

type EmiBank = {
  id?: number | string;
  name?: string | null;
  code?: string | null;
  logo?: string | null;
  logo_url?: string | null;
};

const props = defineProps<{ bank: EmiBank }>();
const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    EmiBankFormModal,
    { mode: 'edit', bank: props.bank },
    {
      title: 'Edit EMI Bank',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
