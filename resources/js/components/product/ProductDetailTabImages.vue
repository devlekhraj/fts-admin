<template>
  <BaseDetailTabImages
    :files="productFiles"
    usage-type="products"
    :usage-id="productId"
    directory="products"
    :edit-modal="ProductImageEditModal"
    :edit-modal-props="(file) => ({ productId, file })"
    edit-modal-title="Edit Product Image"
    empty-state-message="No images attached to this product."
    show-file-id
    @updated="emit('updated')"
  >
    <template #headers>
      <th>Primary Image</th>
    </template>
    <template #rows="{ file }">
      <td class="py-3">
        <v-chip size="small" label variant="tonal" :color="file.meta?.is_default ? 'primary' : 'default'">
          {{ file.meta?.is_default ? 'Yes' : 'No' }}
        </v-chip>
      </td>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type ProductDetailResponse } from '@/api/products.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import ProductImageEditModal from '@/components/product/ProductImageEditModal.vue';

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const productId = computed(() => props.productId ?? null);
const productFiles = computed(() => props.item?.images ?? []);
</script>
