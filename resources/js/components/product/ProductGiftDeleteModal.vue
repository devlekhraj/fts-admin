<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>

    <div class="mt-3 d-flex align-center justify-space-around py-4">
      <div style="width: max-content;" class="d-flex align-center">
        <v-avatar size="44" color="grey-lighten-3" class="mr-3" rounded="lg">
          <v-img v-if="gift.thumb" :src="String(gift.thumb)" cover />
          <v-icon v-else size="22" color="grey-darken-1">mdi-gift-outline</v-icon>
        </v-avatar>
        <div>
          <div class="font-weight-medium">{{ gift.name ?? '-' }}</div>
          <div class="text-medium-emphasis">ID: {{ gift.id }}</div>
        </div>
      </div>
    </div>

    <div class="text-body-2 text-medium-emphasis text-center">
      Are you sure you want to remove this gift item?
    </div>
  </v-card-text>

  <v-card-actions class="pb-4">
    <div class="w-100 d-flex align-center justify-space-around">
      <v-btn color="error" variant="tonal" :disabled="loading" @click="onConfirm">
        <template v-if="!loading" #prepend>
          <v-icon>mdi-delete</v-icon>
        </template>
        <template v-else #prepend>
          <v-progress-circular indeterminate size="18" width="2" />
        </template>
        Remove
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteProductGift, type ProductGiftItem } from '@/api/product-gifts.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  productId: string | number;
  gift: ProductGiftItem;
}>();

const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
  error.value = '';
  if (!props.productId) {
    error.value = 'Product id is required.';
    return;
  }
  if (!props.gift?.id) {
    error.value = 'Gift id is required.';
    return;
  }

  loading.value = true;
  try {
    await deleteProductGift(props.productId, props.gift.id);
    snackbar.show({ message: 'Gift item removed successfully.', color: 'success' });
    emit('saved', { id: props.gift.id });
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
