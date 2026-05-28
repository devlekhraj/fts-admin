<template>
  <v-card-text class="py-6">
  
    <div class="text-body-2 text-medium-emphasis">
      This action cannot be undone. Continue?
    </div>
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end">
    <v-btn variant="text" @click="emit('close')" :disabled="loading">Cancel</v-btn>
    <v-btn
      color="error"
      variant="flat"
      :disabled="loading"
      @click="submit"
    >
      <template #prepend>
        <v-progress-circular v-if="loading" indeterminate size="16" width="2" color="white" />
        <v-icon v-else>mdi-delete-outline</v-icon>
      </template>
      Yes, Delete
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteRequest } from '@/api/emi-requests.api';
import { useSnackbar } from '@/composables/snackbar';

const props = defineProps<{ id: string | number }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const { showSuccess, showError } = useSnackbar();

async function submit() {
  if (!props.id || loading.value) return;
  loading.value = true;
  try {
    await deleteRequest(String(props.id));
    showSuccess('Request deleted');
    emit('saved', { id: props.id });
    emit('close');
  } catch (error) {
    showError('Failed to delete request');
    console.error(error);
  } finally {
    loading.value = false;
  }
}
</script>
