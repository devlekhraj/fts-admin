<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between mb-4">
      <div>
        <div class="text-subtitle-1 font-weight-medium">Brand Banners</div>
        <div class="text-body-2 text-medium-emphasis">Total banners: {{ demoBanners.length }}</div>
      </div>

      <v-btn color="primary" variant="flat" @click="onAddBanner">
        <v-icon start size="16">mdi-plus</v-icon>
        Add New Banner
      </v-btn>
    </div>

    <v-table v-if="demoBanners.length" density="comfortable">
      <thead>
        <tr>
          <th>Banner</th>
          <th>Details</th>
          <th>Status</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="banner in demoBanners" :key="banner.id">
          <td class="py-3">
            <div class="banner-preview rounded">
              <v-img :src="banner.image" cover :alt="banner.title" />
            </div>
          </td>
          <td class="py-3 details-col">
            <div class="text-body-2 font-weight-medium">{{ banner.title }}</div>
            <div class="text-caption text-medium-emphasis mt-1">{{ banner.altText }}</div>
            <div class="d-flex align-center ga-2 mt-1">
              <span class="text-caption text-medium-emphasis">Redirect: {{ banner.link }}</span>
              <v-btn :href="banner.link" target="_blank" rel="noopener noreferrer" icon size="x-small" variant="tonal"
                color="primary">
                <v-icon size="14">mdi-open-in-new</v-icon>
              </v-btn>
            </div>
          </td>
          <td class="py-3">
            <v-chip size="small" label variant="tonal" :color="banner.status ? 'success' : 'warning'">
              {{ banner.status ? 'Active' : 'Inactive' }}
            </v-chip>
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
import { ref } from 'vue';
import { type ProductBrandDetailResponse } from '@/api/products.api';
import BrandBannerFormModal from '@/components/brand/BrandBannerFormModal.vue';
import { openModal } from '@/shared/modal';
import { useSnackbarStore } from '@/stores/snackbar.store';

type DemoBanner = {
  id: number;
  title: string;
  altText: string;
  image: string;
  link: string;
  status: boolean;
};

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
  brandId?: number | string | null;
}>();

const snackbar = useSnackbarStore();

const demoBanners = ref<DemoBanner[]>([
  {
    id: 1,
    title: 'Summer Brand Offer',
    altText: 'Summer offer banner for this brand',
    image: 'https://picsum.photos/seed/brand-banner-1/640/320',
    link: 'https://example.com/summer-offer',
    status: true,
  },
  {
    id: 2,
    title: 'Featured Collection',
    altText: 'Featured collection promotional banner',
    image: 'https://picsum.photos/seed/brand-banner-2/640/320',
    link: 'https://example.com/featured-collection',
    status: false,
  },
]);

function onAddBanner() {
  openModal(
    BrandBannerFormModal,
    { brandId: props.brandId ?? props.item?.id ?? null },
    {
      title: 'Add New Banner',
      size: 'md',
    },
  );
}

function onEditBanner(banner: DemoBanner) {
  snackbar.show({ message: `Edit banner action for ${banner.title} will be connected later.`, color: 'info' });
}

function onDeleteBanner(banner: DemoBanner) {
  snackbar.show({ message: `Delete banner action for ${banner.title} will be connected later.`, color: 'info' });
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
