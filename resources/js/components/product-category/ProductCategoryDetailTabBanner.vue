<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between mb-4">
      <div>
        <div class="text-subtitle-1 font-weight-medium">Category Banners</div>
        <div class="text-body-2 text-medium-emphasis">Total banners: {{ banners.length }}</div>
      </div>

      <v-btn color="primary" variant="flat" @click="onAddBanner">
        <v-icon start size="16">mdi-plus</v-icon>
        Add New Banner
      </v-btn>
    </div>

    <v-table v-if="banners.length" density="comfortable">
      <thead>
        <tr>
          <th>Banner</th>
          <th>Details</th>
          <th>Status</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="banner in banners" :key="banner.id">
          <td class="py-3">
            <div class="banner-preview rounded">
              <v-img :src="banner.url || ''" :alt="banner.title || 'Product category banner'" contain />
            </div>
          </td>
          <td class="py-3 details-col">
            <div class="text-body-2 font-weight-medium">
              {{ banner.title || `Banner #${banner.id}` }}
            </div>
           
            <div v-if="redirectUrl(banner)" class="d-flex align-center ga-2 mt-1">
              <span class="text-caption text-medium-emphasis">Redirect: {{ redirectUrl(banner) }}</span>
              <v-btn
                :href="redirectUrl(banner)"
                target="_blank"
                rel="noopener noreferrer"
                icon
                size="x-small"
                variant="tonal"
                color="primary"
              >
                <v-icon size="14">mdi-open-in-new</v-icon>
              </v-btn>
            </div>
            <div v-if="banner.size_info" class="text-caption text-medium-emphasis mt-1">
              {{ banner.size_info }}
            </div>
            <div v-if="dateRange(banner)" class="text-caption text-medium-emphasis mt-1">
              {{ dateRange(banner) }}
            </div>
          </td>
          <td class="py-3">
            <div class="d-flex flex-column ga-2">
              <v-chip size="small" label variant="tonal" :color="banner.meta?.status === 'active' ? 'success' : 'warning'">
                {{ banner.meta?.status === 'active' ? 'Active' : 'Inactive' }}
              </v-chip>
              <v-chip v-if="banner.meta?.is_default" size="small" label variant="tonal" color="primary">
                Default
              </v-chip>
            </div>
          </td>
          <td class="py-3">
            <div class="d-flex align-center justify-end ga-2">
              <v-btn variant="outlined" color="primary" @click="onEditBanner(banner)">
                Edit
              </v-btn>
              <v-btn variant="outlined" color="error" @click="onDeleteBanner(banner)">
                Delete
              </v-btn>
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div v-else class="empty-banners-state">
      <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
      <div class="text-subtitle-1 font-weight-medium mt-2">No banners found</div>
      <div class="text-body-2 text-medium-emphasis">Add a banner to get started.</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type ProductCategoryDetailResponse, type ProductCategoryBannerItem } from '@/api/product-categories.api';
import { openModal } from '@/shared/modal';
import ProductCategoryBannerFormModal from '@/components/product-category/ProductCategoryBannerFormModal.vue';
import ProductCategoryBannerDeleteModal from '@/components/product-category/ProductCategoryBannerDeleteModal.vue';

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
  categoryId?: number | string | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const banners = computed<ProductCategoryBannerItem[]>(() => {
  const bannerItems = (props.item?.banners ?? []) as ProductCategoryBannerItem[];
  if (bannerItems.length) return bannerItems;
  return [];
});

function redirectUrl(banner: ProductCategoryBannerItem) {
  return String(banner.meta?.redirect_url ?? '').trim();
}

function dateRange(banner: ProductCategoryBannerItem) {
  const start = String(banner.meta?.start_date ?? '').trim();
  const end = String(banner.meta?.end_date ?? '').trim();
  if (!start && !end) return '';
  if (start && end) return `Active from ${start} to ${end}`;
  if (start) return `Active from ${start}`;
  return `Active until ${end}`;
}

function openBannerModal(mode: 'create' | 'edit', banner?: ProductCategoryBannerItem) {
  openModal(
    ProductCategoryBannerFormModal,
    {
      categoryId: props.categoryId ?? props.item?.id ?? null,
      mode,
      banner: banner ?? null,
    },
    {
      title: mode === 'edit' ? 'Edit Banner' : 'Add New Banner',
      size: 'md',
      onSaved: () => emit('updated'),
    },
  );
}

function onAddBanner() {
  openBannerModal('create');
}

function onEditBanner(banner: ProductCategoryBannerItem) {
  openBannerModal('edit', banner);
}

function onDeleteBanner(banner: ProductCategoryBannerItem) {
  openModal(
    ProductCategoryBannerDeleteModal,
    {
      categoryId: props.categoryId ?? props.item?.id ?? null,
      banner,
    },
    {
      title: 'Confirm Banner Deletion',
      size: 'sm',
      onSaved: () => emit('updated'),
    },
  );
}
</script>

<style scoped>
.banner-preview {
  width: 160px;
  height: 88px;
  background: rgb(var(--v-theme-surface-variant));
  overflow: hidden;
}

.details-col {
  min-width: 320px;
  max-width: 460px;
  word-break: break-word;
}

.empty-banners-state {
  min-height: 220px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
</style>
