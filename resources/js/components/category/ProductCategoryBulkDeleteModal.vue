<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>

    <div class="mt-3 d-flex align-center justify-space-around py-6">
      <div style="width: max-content;" class="d-flex align-center">
        <v-avatar size="40" color="error-lighten-4" class="mr-3">
          <v-icon size="22" color="error">mdi-delete-outline</v-icon>
        </v-avatar>
        <div>
          <div class="font-weight-medium">Bulk delete selected categories</div>
          <div class="text-medium-emphasis">{{ count }} {{ count === 1 ? 'category' : 'categories' }} selected</div>
        </div>
      </div>
    </div>

    <div class="text-body-2 text-medium-emphasis text-center">
      This action will delete the selected categories. Continue?
    </div>
  </v-card-text>

  <v-card-actions class="pb-4">
    <div class="w-100 d-flex align-center justify-space-around">
      <v-btn variant="text" :disabled="loading" @click="emit('close')">
        Cancel
      </v-btn>
      <v-btn color="error" variant="flat" :disabled="loading" @click="onConfirm">
        <template #prepend>
          <v-progress-circular
            v-if="loading"
            indeterminate
            size="16"
            width="2"
            color="white"
          />
          <v-icon v-else>mdi-delete</v-icon>
        </template>
        {{ loading ? 'Deleting...' : 'Delete Selected' }}
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  count: number;
  onConfirm: () => Promise<void> | void;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
  if (loading.value) return;

  error.value = '';
  loading.value = true;
  try {
    await props.onConfirm();
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
