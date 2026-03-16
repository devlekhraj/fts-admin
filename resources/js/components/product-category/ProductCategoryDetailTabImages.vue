<template>
  <BaseDetailTabImages
    :files="categoryFiles"
    usage-type="product_categories"
    :usage-id="item?.id ?? null"
    directory="product-category"
    :edit-modal="ProductCategoryImageEditModal"
    :edit-modal-props="(file) => ({ categoryId: item?.id ?? null, file })"
    edit-modal-title="Edit Product Category Image"
    empty-state-message="No files attached to this category."
    show-file-id
    @updated="emit('updated')"
  >
    <template #headers>
      <th>Primary Image</th>
    </template>
    <template #rows="{ file }">
      <td class="py-3">
        <v-chip
          size="small"
          label
          variant="tonal"
          :color="file.meta?.is_default ? 'primary' : 'default'">
          {{ file.meta?.is_default ? 'Yes' : 'No' }}
        </v-chip>
      </td>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type ProductCategoryDetailResponse } from '@/api/product-categories.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import ProductCategoryImageEditModal from '@/components/product-category/ProductCategoryImageEditModal.vue';

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const categoryFiles = computed(() => props.item?.files ?? []);
</script>
