<template>
  <v-card-text class="py-6">
    
    <div class="text-body-2 text-medium-emphasis mb-6">
      Please provide a reason. This will be stored in the activity log.
    </div>

    <v-textarea
      v-model="reason"
      label="Reason"
      variant="outlined"
      density="comfortable"
      hide-details
      auto-grow
      rows="2"
      maxlength="400"
      :disabled="loading"
    />
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end ga-2">
    <v-btn variant="text" @click="emit('close')" :disabled="loading">Close</v-btn>
    <v-btn color="error" variant="flat" :disabled="loading || reason.trim().length === 0" @click="submit">
      <template #prepend>
        <v-progress-circular v-if="loading" indeterminate size="16" width="2" color="white" />
        <v-icon v-else>mdi-cancel</v-icon>
      </template>
      Yes, Cancel
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { updateStatus } from '@/api/emi-requests.api';
import { useSnackbar } from '@/composables/snackbar';

const props = defineProps<{ id: string | number }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved'): void }>();

const { showSuccess, showError } = useSnackbar();

const loading = ref(false);
const reason = ref('');

async function submit() {
  if (!props.id || loading.value) return;
  const text = reason.value.trim();
  if (!text) return;

  loading.value = true;
  try {
    const res = await updateStatus(props.id, 4, text);
    if (!res?.success) {
      showError('Failed to cancel request');
      return;
    }
    showSuccess('Request cancelled');
    emit('saved');
    emit('close');
  } catch (e) {
    console.error(e);
    showError('Failed to cancel request');
  } finally {
    loading.value = false;
  }
}
</script>

