<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>

    <div class="py-4 text-center pt-5 pb-0 mb-0">
      <v-icon color="error" size="64" class="mb-4">mdi-alert-circle-outline</v-icon>
      <h3 class="text-h6 font-weight-bold">Delete FAQ?</h3>
      <p class="text-body-2 text-medium-emphasis mt-2">
        Are you sure you want to delete this FAQ? This action cannot be undone.
      </p>
    </div>
  </v-card-text>

  <v-card-actions class="pa-4 pt-0">
    <v-btn variant="text" :disabled="loading" @click="emit('close')">Cancel</v-btn>
    <v-spacer />
    <v-btn color="error" variant="flat" :disabled="loading" @click="onConfirm">
      <template v-if="!loading" #prepend>
        <v-icon>mdi-delete</v-icon>
      </template>
      <template v-else #prepend>
        <v-progress-circular indeterminate size="18" width="2" />
      </template>
      Delete
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteFaq, type FaqListItem } from '@/api/faqs.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  faq: FaqListItem;
}>();

const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
  error.value = '';
  const id = props.faq?.id;
  if (id === null || id === undefined || id === '') {
    error.value = 'FAQ id is required.';
    return;
  }

  loading.value = true;
  try {
    const resp = await deleteFaq(id);
    snackbar.show({ message: resp?.message || 'FAQ deleted successfully.', color: 'success' });
    emit('saved', { id });
    emit('close');
  } catch (err) {
    const message = getErrorMessage(err);
    error.value = message;
    snackbar.show({ message, color: 'error' });
  } finally {
    loading.value = false;
  }
}
</script>
