<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>
    <div class="mt-3 d-flex align-center justify-space-around py-6">
      <div style="width: max-content;" class="d-flex align-center">
        <v-avatar size="40" color="grey-lighten-3" class="mr-3">
          <v-icon size="22" color="grey-darken-1">mdi-bank-outline</v-icon>
        </v-avatar>
        <div>
          <div class="font-weight-medium text-capitalize">{{ bank.name ?? '-' }}</div>
          <div class="text-medium-emphasis">{{ bank.code ?? '' }}</div>
        </div>
      </div>
    </div>
    <div class="text-body-2 text-medium-emphasis text-center">
      This action cannot be undone. Do you want to delete this EMI bank?
    </div>
  </v-card-text>

  <v-card-actions class="pb-4">
    <div class="w-100 d-flex align-center justify-space-around">
      <v-btn color="error" variant="tonal" :loading="loading" :disabled="loading" @click="onConfirm">
        <v-icon start>mdi-delete</v-icon>
        Delete
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteEmiBank } from '@/api/emi-banks.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type EmiBank = {
  id?: number | string;
  name?: string | null;
  code?: string | null;
};

const props = defineProps<{ bank: EmiBank }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
  error.value = '';
  if (!props.bank?.id) {
    error.value = 'Bank id is required.';
    return;
  }

  loading.value = true;
  try {
    const response = await deleteEmiBank(String(props.bank.id));
    const message = (response as any)?.data?.message ?? 'EMI bank deleted successfully.';
    snackbar.show({ message, color: 'success' });
    emit('saved', { id: props.bank.id });
    emit('close');
  } catch (err) {
    const response = (err as any)?.response;
    const responseErrors = response?.data?.errors ?? null;
    if (response?.status === 422 && responseErrors && typeof responseErrors === 'object') {
      const next: Record<string, string[]> = {};
      for (const [key, messages] of Object.entries(responseErrors)) {
        if (Array.isArray(messages)) {
          next[key] = messages.map((item) => String(item));
        } else if (messages != null) {
          next[key] = [String(messages)];
        }
      }
      const message = next.id?.[0] ?? 'Failed to delete EMI bank.';
      error.value = message;
      snackbar.show({ message, color: 'error' });
      return;
    }
    const message = getErrorMessage(err);
    error.value = message;
    snackbar.show({ message, color: 'error' });
  } finally {
    loading.value = false;
  }
}
</script>
