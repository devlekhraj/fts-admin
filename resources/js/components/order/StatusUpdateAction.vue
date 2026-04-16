<template>
  <v-btn color="primary" variant="flat" append-icon="mdi-chevron-down" @click="open">Update Status
  </v-btn>
</template>

<script setup lang="ts">
import { openModal } from '@/shared/modal';
import OrderStatusModal from '@/components/order/StatusUpdateModal.vue';

const props = defineProps<{
  orderId: string | number;
  currentStatus?: string;
}>();

const emit = defineEmits<{
  (e: 'selected', payload: { status: string; orderId: string | number }): void;
}>();

function open() {
  openModal(
    OrderStatusModal,
    { orderId: props.orderId, currentStatus: props.currentStatus },
    {
      title: 'Update Order Status',
      size: 'sm',
      onSaved: (payload?: unknown) => {
        const status = typeof payload === 'string' ? payload : props.currentStatus ?? '';
        if (!status) return;
        emit('selected', { status, orderId: props.orderId });
      },
    },
  );
}
</script>

<style scoped>
.status-action {
  display: flex;
}
</style>
