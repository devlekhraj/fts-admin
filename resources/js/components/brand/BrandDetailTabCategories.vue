<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
      <div>
        <div class="text-subtitle-1 font-weight-medium">Brand Categories ({{ categories.length }})</div>
        <div class="text-body-2 text-medium-emphasis">Categories assigned to this brand.</div>
      </div>
      <v-btn prepend-icon="mdi-plus" variant="flat" color="primary" @click="openAssignModal">
        Assign Categories
      </v-btn>
    </div>

    <v-alert v-if="categories.length === 0" type="info" variant="tonal" density="comfortable">
      No categories assigned to this brand.
    </v-alert>

    <v-table v-else density="comfortable" class="border rounded">
      <thead>
        <tr>
          <th>Category</th>
          <th>Slug</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(category,index) in categories" :key="index">
          <td class="py-3">
            <div class="text-body-1 font-weight-medium">
              {{ category.title || '-' }}
            </div>
            <div v-if="category.seq_no != null" class="text-caption text-medium-emphasis">
              Sort order #{{ category.seq_no }}
            </div>
          </td>
          <td class="py-3 text-body-2 text-medium-emphasis">
            {{ category.slug || '-' }}
          </td>
          <td class="py-3">
            <v-chip
              size="small"
              label
              variant="tonal"
              :color="category.status ? 'success' : 'warning'"
            >
              {{ category.status ? 'Active' : 'Inactive' }}
            </v-chip>
          </td>
        </tr>
      </tbody>
    </v-table>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { ProductBrandDetailResponse } from '@/api/products.api';
import { openModal } from '@/shared/modal';
import BrandCategoryAssignModal from '@/components/brand/BrandCategoryAssignModal.vue';

type BrandCategoryItem = NonNullable<ProductBrandDetailResponse['categories']>[number];

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
}>();

const categories = computed<BrandCategoryItem[]>(() => {
  return Array.isArray(props.item?.categories) ? props.item.categories : [];
});

function openAssignModal() {
  openModal(
    BrandCategoryAssignModal,
    {
      brandId: props.item?.id,
      initialSelectedIds: categories.value.map((category) => category.id).filter((id): id is string | number => id !== null && id !== undefined),
    },
    {
      title: 'Assign Categories',
      size: 'lg',
      onSaved: () => emit('updated'),
    },
  );
}

const emit = defineEmits<{
  (e: 'updated'): void;
}>();
</script>
