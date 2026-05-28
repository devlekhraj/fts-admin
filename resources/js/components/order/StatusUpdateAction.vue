<template>
  <v-btn
    v-if="showConfirm"
    color="success"
    variant="flat"
    prepend-icon="mdi-check-circle"
    @click="openConfirmModal('confirm')"
  >
    Confirm
  </v-btn>
  <v-btn
    v-if="showDispatch"
    color="info"
    variant="flat"
    prepend-icon="mdi-truck"
    @click="openConfirmModal('dispatch')"
  >
    Dispatch
  </v-btn>
  <v-btn
    v-if="showComplete"
    color="success"
    variant="flat"
    prepend-icon="mdi-check-circle-outline"
    @click="openCompleteModal"
  >
    Complete
  </v-btn>
  <v-btn
    v-if="showCancel"
    color="error"
    variant="flat"
    prepend-icon="mdi-cancel"
    @click="openCancelModal"
  >
    Cancel
  </v-btn>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { openModal } from '@/shared/modal';
import { updateOrderStatus } from '@/api/orders.api';
import OrderActionConfirmModal from '@/components/order/OrderActionConfirmModal.vue';
import OrderDispatchConfirmModal from '@/components/order/OrderDispatchConfirmModal.vue';
import OrderCancelConfirmModal from '@/components/order/OrderCancelConfirmModal.vue';
import OrderCompleteConfirmModal from '@/components/order/OrderCompleteConfirmModal.vue';

const props = defineProps<{
  orderId: string | number;
  currentStatus?: string;
}>();

const emit = defineEmits<{
  (e: 'success', payload: { message: string }): void;
}>();

const normalizedStatus = computed(() => String(props.currentStatus ?? '').trim());
const showConfirm = computed(() => ['Draft', 'Placed'].includes(normalizedStatus.value));
const showDispatch = computed(() => normalizedStatus.value === 'Confirmed');
const showComplete = computed(() => normalizedStatus.value === 'Dispatched');
const showCancel = computed(() => !['Completed', 'Canceled'].includes(normalizedStatus.value));

async function submitAction(action: 'confirm' | 'dispatch' | 'complete' | 'cancel', notes?: string): Promise<boolean> {
  const statusCodeMap: Record<typeof action, number> = {
    confirm: 2,
    dispatch: 3,
    complete: 4,
    cancel: 5,
  };

  try {
    const result = await updateOrderStatus(props.orderId, statusCodeMap[action], notes ?? null);

    const successMap: Record<typeof action, string> = {
      confirm: 'Order confirmed',
      dispatch: 'Order dispatched',
      complete: 'Order completed',
      cancel: 'Order canceled',
    };
    emit('success', { message: successMap[action] });
    return true;
  } catch (e) {
    console.error('Failed to update order status', e);
    // Errors are handled by the page (request/response interceptor/snackbar)
    return false;
  }
}

function openConfirmModal(action: 'confirm' | 'dispatch') {
  openModal(
    action === 'confirm' ? OrderActionConfirmModal : OrderDispatchConfirmModal,
    {
      orderId: props.orderId,
      action,
      onSubmit: (payload: { action: 'confirm' | 'dispatch'; note?: string }) => submitAction(payload.action, payload.note),
    },
    {
      title: action === 'confirm' ? 'Confirm Order' : 'Dispatch Order',
      size: 'sm',
    },
  );
}

function openCompleteModal() {
  openModal(
    OrderCompleteConfirmModal,
    {
      orderId: props.orderId,
      action: 'complete',
      onSubmit: (payload: { action: 'complete' }) => submitAction(payload.action),
    },
    {
      title: 'Complete Order',
      size: 'sm',
    },
  );
}

function openCancelModal() {
  openModal(
    OrderCancelConfirmModal,
    {
      orderId: props.orderId,
      onSubmit: (payload: { action: 'cancel'; reason: string }) => submitAction(payload.action, payload.reason),
    },
    {
      title: 'Cancel Order',
      size: 'sm',
    },
  );
}
</script>

<style scoped>
.status-action {
  display: flex;
}
</style>
