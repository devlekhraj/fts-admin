<template>
  <AppPageHeader title="Brand Detail" subtitle="View brand information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="brand-top-grid">
      <div class="brand-thumb-cell">
        <v-avatar size="112" rounded="lg" color="grey-lighten-3">
          <v-img v-if="brandDetail?.logo" :src="String(brandDetail.logo)" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
      </div>
      <div>
        <div class="text-h6">{{ brandDetail?.name || '-' }}</div>

        <div class="d-flex align-center ga-2 mt-2">
          <span class="text-body-2 text-medium-emphasis">{{ brandUrl || '-' }}</span>
          <v-btn
            v-if="brandUrl"
            :href="brandUrl"
            target="_blank"
            rel="noopener noreferrer"
            icon
            size="x-small"
            variant="tonal"
            color="primary">
            <v-icon size="16">mdi-open-in-new</v-icon>
          </v-btn>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading brand detail...</div>
  </v-card>

  <v-card class="mt-4">
    <v-tabs v-model="activeTab" color="primary">
      <v-tab v-for="tab in tabItems" :key="tab.value" :value="tab.value">
        <v-icon start size="16">{{ tab.icon }}</v-icon>
        {{ tab.label }}
      </v-tab>
    </v-tabs>
    <v-divider />

    <v-window v-model="activeTab">
      <v-window-item v-for="tab in tabItems" :key="tab.value" :value="tab.value">
        <component
          :is="tab.component"
          :item="brandDetail"
          :brand-id="brandId"
          @updated="fetchBrandDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getBrandDetail, type ProductBrandDetailResponse } from '@/api/products.api';
import BrandDetailTabDescription from '@/components/brand/BrandDetailTabDescription.vue';
import BrandDetailTabImages from '@/components/brand/BrandDetailTabImages.vue';
import BrandDetailTabOverview from '@/components/brand/BrandDetailTabOverview.vue';
import BrandDetailTabSeo from '@/components/brand/BrandDetailTabSeo.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
  { value: 'overview', label: 'Overview', icon: 'mdi-view-dashboard-outline', component: BrandDetailTabOverview },
  { value: 'description', label: 'Description', icon: 'mdi-text-box-outline', component: BrandDetailTabDescription },
  { value: 'images', label: 'Images', icon: 'mdi-image-multiple-outline', component: BrandDetailTabImages },
  { value: 'seo', label: 'SEO', icon: 'mdi-magnify', component: BrandDetailTabSeo },
];

const brandId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const brandDetail = ref<ProductBrandDetailResponse | null>(null);
const siteOrigin = typeof window !== 'undefined' ? window.location.origin : '';

const brandUrl = computed(() => {
  const slug = String(brandDetail.value?.slug ?? '').trim();
  if (!slug) return '';
  if (/^https?:\/\//i.test(slug)) return slug;
  const normalized = slug.startsWith('/') ? slug : `/${slug}`;
  return `${siteOrigin}${normalized}`;
});

async function fetchBrandDetail() {
  if (!brandId.value) return;
  loading.value = true;
  try {
    brandDetail.value = await getBrandDetail(brandId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.product.brands' });
}

onMounted(fetchBrandDetail);
</script>

<style scoped>
.brand-top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.brand-thumb-cell {
  display: flex;
  justify-content: center;
}

@media (min-width: 960px) {
  .brand-top-grid {
    grid-template-columns: 128px minmax(0, 1fr);
    gap: 20px;
  }

  .brand-thumb-cell {
    justify-content: flex-start;
  }
}
</style>
