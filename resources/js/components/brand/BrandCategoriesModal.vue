<template>
  <v-card-text class="py-6">
    <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
      <div>
        <div class="text-subtitle-1 font-weight-medium">
          {{ brandName }}
        </div>
        <div class="text-body-2 text-medium-emphasis">
          {{ categories.length }} categories assigned
        </div>
      </div>
      <v-chip size="small" label variant="tonal" color="primary">
        Total: {{ categories.length }}
      </v-chip>
    </div>

    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>

    <v-skeleton-loader v-if="loading" type="table" />

    <v-alert v-else-if="categories.length === 0" type="info" variant="tonal">
      No categories assigned to this brand.
    </v-alert>

    <v-table v-else density="comfortable" class="border rounded">
      <thead>
        <tr>
          <th>Category</th>
          <th>Slug</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="category in categories" :key="String(category.id ?? category.slug ?? category.title)">
          <td class="py-3">
            <div class="text-body-2 font-weight-medium">
              {{ category.title || '-' }}
            </div>
          </td>
          <td class="py-3 text-body-2 text-medium-emphasis">
            {{ category.slug || '-' }}
          </td>
        </tr>
      </tbody>
    </v-table>
  </v-card-text>

  <v-divider />

  <v-card-actions class="px-6 pb-4">
    <v-spacer />
    <v-btn variant="outlined" color="primary" @click="emit('close')">
      Close
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { getBrandDetail, type ProductBrandDetailResponse } from '@/api/products.api';

type BrandCategoryItem = NonNullable<ProductBrandDetailResponse['categories']>[number];

const props = defineProps<{
  brand: {
    id?: number | string | null;
    name?: string | null;
    categories_count?: number | null;
  };
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const loading = ref(false);
const error = ref('');
const categories = ref<BrandCategoryItem[]>([]);

const brandName = computed(() => props.brand?.name?.trim() || 'Brand Categories');

async function fetchCategories() {
  const brandId = String(props.brand?.id ?? '').trim();
  if (!brandId) {
    error.value = 'Brand id is required.';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const detail = await getBrandDetail(brandId);
    categories.value = Array.isArray(detail?.categories) ? detail.categories : [];
  } catch (exception: any) {
    error.value = exception?.response?.data?.message || 'Failed to load brand categories.';
  } finally {
    loading.value = false;
  }
}

onMounted(fetchCategories);
</script>
