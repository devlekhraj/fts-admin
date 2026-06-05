<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>

  
    <div class="text-body-2 text-medium-emphasis text-center">
      This action cannot be undone. Do you want to delete this brand banner?
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
import { deleteBrandBanner } from '@/api/brands.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type BrandBannerItem = {
  id?: number | string;
  title?: string | null;
  url?: string | null;
  meta?: {
    redirect_url?: string | null;
  } | null;
};

const props = defineProps<{
  brandId: number | string | null;
  banner: BrandBannerItem;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
  error.value = '';

  if (!props.brandId || !String(props.brandId).trim()) {
    error.value = 'Brand id is required.';
    return;
  }

  if (!props.banner?.id || !String(props.banner.id).trim()) {
    error.value = 'Banner id is required.';
    return;
  }

  loading.value = true;
  try {
    const response = await deleteBrandBanner(props.brandId, props.banner.id);
    const message = (response as any)?.data?.message ?? 'Brand banner deleted successfully.';
    snackbar.show({ message, color: 'success' });
    emit('saved', { id: props.banner.id, action: 'deleted' });
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
      const message = next.id?.[0] ?? 'Failed to delete brand banner.';
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
