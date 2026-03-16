<template>
  <BaseDetailTabImages
    :files="brandFiles"
    usage-type="product_brands"
    :usage-id="item?.id ?? null"
    directory="brands"
    :edit-modal="BrandImageEditModal"
    :edit-modal-props="(file) => ({ brandId: item?.id ?? null, file })"
    edit-modal-title="Edit Brand Image"
    empty-state-message="No files attached to this brand."
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
import { type ProductBrandDetailResponse } from '@/api/products.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import BrandImageEditModal from '@/components/brand/BrandImageEditModal.vue';

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const brandFiles = computed(() => props.item?.files ?? []);
</script>
