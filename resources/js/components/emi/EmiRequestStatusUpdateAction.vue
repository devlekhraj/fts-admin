<template>
  <v-btn
    v-if="showApprove"
    color="success"
    variant="flat"
    prepend-icon="mdi-check-bold"
    @click="openApprove"
  >
    Approve Request
  </v-btn>

  <v-btn
    v-if="showProcessing"
    color="info"
    variant="flat"
    prepend-icon="mdi-progress-clock"
    @click="openStatusConfirm('processing')"
  >
    Processing
  </v-btn>

  <v-btn
    v-if="showBankApproval"
    color="info"
    variant="flat"
    prepend-icon="mdi-bank"
    @click="openStatusConfirm('bank_approval')"
  >
    Bank Approval
  </v-btn>

  <v-btn
    v-if="showDispatched"
    color="info"
    variant="flat"
    prepend-icon="mdi-truck"
    @click="openStatusConfirm('dispatched')"
  >
    Dispatched
  </v-btn>

  <v-btn
    v-if="showDelivered"
    color="info"
    variant="flat"
    prepend-icon="mdi-package-variant-closed"
    @click="openStatusConfirm('delivered')"
  >
    Delivered
  </v-btn>

  <v-btn
    v-if="showCompleted"
    color="success"
    variant="flat"
    prepend-icon="mdi-check-circle-outline"
    @click="openStatusConfirm('completed')"
  >
    Completed
  </v-btn>

  <v-btn
    v-if="showCancel"
    color="error"
    variant="tonal"
    prepend-icon="mdi-cancel"
    @click="openStatusConfirm('cancelled')"
  >
    Cancel
  </v-btn>

  <v-btn
    v-if="showDelete"
    color="error"
    variant="outlined"
    prepend-icon="mdi-delete-outline"
    @click="openDelete"
  >
    Delete
  </v-btn>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { openModal } from '@/shared/modal';
import { updateStatus } from '@/api/emi-requests.api';
import { useSnackbar } from '@/composables/snackbar';

import EmiRequestApproveModal from '@/components/emi/EmiRequestApproveModal.vue';
import EmiRequestCancelModal from '@/components/emi/EmiRequestCancelModal.vue';
import EmiRequestDeleteModal from '@/components/emi/EmiRequestDeleteModal.vue';
import EmiRequestStatusConfirmModal from '@/components/emi/EmiRequestStatusConfirmModal.vue';

const props = defineProps<{
  id: string | number;
  statusLabel?: string;
}>();

const emit = defineEmits<{ (e: 'success'): void }>();

const { showSuccess, showError } = useSnackbar();

const status = computed(() => String(props.statusLabel ?? '').trim());

// Flow: Pending -> Processing -> Bank Approval -> Approved -> Dispatched -> Delivered -> Completed
const showProcessing = computed(() => status.value === 'Pending');
const showBankApproval = computed(() => status.value === 'Processing');
const showApprove = computed(() => status.value === 'Bank Approval');
const showDispatched = computed(() => status.value === 'Approved');
const showDelivered = computed(() => status.value === 'Dispatched');
const showCompleted = computed(() => status.value === 'Delivered');
// Reject removed; use Cancel instead
const showDelete = computed(() => status.value !== 'Completed');
const showCancel = computed(() => status.value !== 'Completed' && status.value !== 'Cancelled');

function openApprove() {
  openModal(
    EmiRequestApproveModal,
    { id: props.id },
    {
      title: 'Approve EMI Request',
      size: 'sm',
      onSaved: () => emit('success'),
    },
  );
}

function openDelete() {
  const allowedDeleteStatuses = ['Pending', 'Cancelled'];
  if (!allowedDeleteStatuses.includes(status.value)) {
    showError('Only pending or cancelled EMI requests can be deleted.');
    return;
  }
  openModal(
    EmiRequestDeleteModal,
    { id: props.id },
    {
      title: 'Delete EMI Request',
      size: 'sm',
      onSaved: () => emit('success'),
    },
  );
}

type StatusAction = 'processing' | 'bank_approval' | 'dispatched' | 'delivered' | 'completed' | 'finished' | 'cancelled';

function openStatusConfirm(action: StatusAction) {
  if (action === 'cancelled') {
    openModal(
      EmiRequestCancelModal,
      { id: props.id },
      { title: 'Cancel EMI Request', size: 'md', onSaved: () => emit('success') },
    );
    return;
  }

  const maps: Record<StatusAction, { code: number; title: string; message: string; color: string; icon: string; label: string }> = {
    processing: { code: 1, title: 'Mark as Processing', message: 'This will mark the EMI request as Processing. Continue?', color: 'info', icon: 'mdi-progress-clock', label: 'Yes, Processing' },
    bank_approval: { code: 5, title: 'Mark as Bank Approval', message: 'This will mark the EMI request as Bank Approval. Continue?', color: 'info', icon: 'mdi-bank', label: 'Yes, Bank Approval' },
    dispatched: { code: 6, title: 'Mark as Dispatched', message: 'This will mark the EMI request as Dispatched. Continue?', color: 'info', icon: 'mdi-truck', label: 'Yes, Dispatched' },
    delivered: { code: 7, title: 'Mark as Delivered', message: 'This will mark the EMI request as Delivered. Continue?', color: 'info', icon: 'mdi-package-variant-closed', label: 'Yes, Delivered' },
    completed: { code: 8, title: 'Mark as Completed', message: 'This will mark the EMI request as Completed. Continue?', color: 'success', icon: 'mdi-check-circle-outline', label: 'Yes, Completed' },
    finished: { code: 3, title: 'Mark as Finished', message: 'This will mark the EMI request as Finished. Continue?', color: 'primary', icon: 'mdi-flag-checkered', label: 'Yes, Finished' },
    cancelled: { code: 4, title: 'Cancel EMI Request', message: 'This will mark the EMI request as Cancelled. Continue?', color: 'error', icon: 'mdi-cancel', label: 'Yes, Cancel' },
  };

  const meta = maps[action];

  openModal(
    EmiRequestStatusConfirmModal,
    {
      heading: meta.title,
      message: meta.message,
      confirmLabel: meta.label,
      color: meta.color,
      icon: meta.icon,
      onSubmit: async () => {
        try {
          const res = await updateStatus(props.id, meta.code);
          if (!res?.success) {
            showError('Failed to update status');
            return false;
          }
          showSuccess('Status updated');
          emit('success');
          return true;
        } catch (e) {
          console.error(e);
          showError('Failed to update status');
          return false;
        }
      },
    },
    { title: meta.title, size: 'sm' },
  );
}
</script>
