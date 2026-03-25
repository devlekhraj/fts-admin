<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>
    <div class="mt-3 d-flex align-center justify-space-around py-6">
      <div style="width: max-content;" class="d-flex align-center">
        <v-avatar size="40" color="grey-lighten-3" class="mr-3">
          <v-icon size="22" color="grey-darken-1">mdi-shape-outline</v-icon>
        </v-avatar>
        <div>
          <div class="font-weight-medium text-capitalize">{{ category.title ?? '-' }}</div>
          <div class="text-medium-emphasis">{{ category.slug ?? '' }}</div>
        </div>
      </div>
    </div>
    <div class="text-body-2 text-medium-emphasis text-center">
      This action will soft delete the category. Continue?
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
import { deleteProductCategory } from '@/api/product-categories.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type Category = {
  id?: number | string;
  title?: string | null;
  slug?: string | null;
};

const props = defineProps<{ category: Category }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
  error.value = '';
  if (!props.category?.id) {
    error.value = 'Category id is required.';
    return;
  }

  loading.value = true;
  try {
    const response = await deleteProductCategory(String(props.category.id));
    const message = (response as any)?.data?.message ?? 'Product category deleted successfully.';
    snackbar.show({ message, color: 'success' });
    emit('saved', { id: props.category.id });
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
      const message = next.id?.[0] ?? 'Failed to delete category.';
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
