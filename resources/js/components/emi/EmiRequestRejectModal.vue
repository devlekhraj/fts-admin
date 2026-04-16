<template>
  <v-card-text class="py-6">
    <div class="text-body-1 font-weight-medium mb-2">
      Confirm rejection of this EMI request
    </div>
    <div class="text-body-2 text-medium-emphasis">
      This will mark the request as <strong>rejected</strong>. Continue?
    </div>
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end">
    <v-btn variant="text" @click="emit('close')" :disabled="loading">Cancel</v-btn>
    <v-btn
      color="error"
      variant="flat"
      :loading="loading"
      :disabled="loading"
      @click="submit"
    >
      Yes, Reject
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { approve } from '@/api/emi-requests.api';
import { useSnackbar } from '@/composables/snackbar';

const props = defineProps<{ id: string | number }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const { showSuccess, showError } = useSnackbar();

async function submit() {
  if (!props.id || loading.value) return;
  loading.value = true;
  try {
    await approve(String(props.id), { status: 'rejected' });
    showSuccess('Request rejected');
    emit('saved', { id: props.id, status: 'rejected' });
    emit('close');
  } catch (error) {
    showError('Failed to reject request');
    console.error(error);
  } finally {
    loading.value = false;
  }
}
</script>
