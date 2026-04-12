<template>
  <v-card-text class="py-6">
    <p class="mb-4">Delete API key <strong>#{{ item?.id }}</strong>? This cannot be undone.</p>
    <v-alert v-if="error" type="error" variant="tonal">
      {{ error }}
    </v-alert>
  </v-card-text>
  <v-card-actions class="pb-4">
    <div class="w-100 d-flex align-center justify-space-between px-4 pt-2">
      <v-btn variant="tonal" color="secondary" @click="emit('close')" :disabled="loading">
        Cancel
      </v-btn>
      <v-btn variant="flat" color="error" :loading="loading" :disabled="loading" @click="onDelete">
        <v-icon start>mdi-delete-outline</v-icon>
        Delete
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteApiKey, type ApiKeyItem } from '@/api/developer.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: ApiKeyItem;
}>();

const emit = defineEmits<{ (e: 'close'): void; (e: 'deleted', id: ApiKeyItem['id']): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onDelete() {
  if (!props.item?.id) return;
  loading.value = true;
  error.value = '';
  try {
    await deleteApiKey(props.item.id);
    snackbar.show({ message: 'API key deleted successfully.', color: 'success' });
    emit('deleted', props.item.id);
    emit('close');
  } catch (err: any) {
    error.value = getErrorMessage(err);
  } finally {
    loading.value = false;
  }
}
</script>
