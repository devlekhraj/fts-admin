<template>
  <AppPageHeader title="Category Detail" subtitle="View product category information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="category-top-grid">
      <div class="category-thumb-cell">
        <v-avatar size="112" rounded="lg" color="grey-lighten-3">
          <v-img v-if="categoryDetail?.thumb" :src="String(categoryDetail.thumb)" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-folder-outline</v-icon>
        </v-avatar>
      </div>
      <div>
        <div class="text-h6">{{ categoryDetail?.title || '-' }}</div>

        <div class="d-flex align-center ga-2 mt-2">
          <span class="text-body-2 text-medium-emphasis">{{ categoryUrl || '-' }}</span>
          <v-btn
            v-if="categoryUrl"
            :href="categoryUrl"
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

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading category detail...</div>
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
          :item="categoryDetail"
          :category-id="categoryId"
          @updated="fetchCategoryDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getProductCategory, type ProductCategoryDetailResponse } from '@/api/product-categories.api';
import ProductCategoryDetailTabDescription from '@/components/product-category/ProductCategoryDetailTabDescription.vue';
import ProductCategoryDetailTabImages from '@/components/product-category/ProductCategoryDetailTabImages.vue';
import ProductCategoryDetailTabOverview from '@/components/product-category/ProductCategoryDetailTabOverview.vue';
import ProductCategoryDetailTabSeo from '@/components/product-category/ProductCategoryDetailTabSeo.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
  { value: 'overview', label: 'Overview', icon: 'mdi-view-dashboard-outline', component: ProductCategoryDetailTabOverview },
  { value: 'description', label: 'Description', icon: 'mdi-text-box-outline', component: ProductCategoryDetailTabDescription },
  { value: 'images', label: 'Images', icon: 'mdi-image-multiple-outline', component: ProductCategoryDetailTabImages },
  { value: 'seo', label: 'SEO', icon: 'mdi-magnify', component: ProductCategoryDetailTabSeo },
];

const categoryId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const categoryDetail = ref<ProductCategoryDetailResponse | null>(null);
const siteOrigin = typeof window !== 'undefined' ? window.location.origin : '';

const categoryUrl = computed(() => {
  const slug = String(categoryDetail.value?.slug ?? '').trim();
  if (!slug) return '';
  if (/^https?:\/\//i.test(slug)) return slug;
  const normalized = slug.startsWith('/') ? slug : `/${slug}`;
  return `${siteOrigin}${normalized}`;
});

async function fetchCategoryDetail() {
  if (!categoryId.value) return;
  loading.value = true;
  try {
    categoryDetail.value = await getProductCategory(categoryId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.product.categories' });
}

onMounted(fetchCategoryDetail);
</script>

<style scoped>
.category-top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.category-thumb-cell {
  display: flex;
  justify-content: center;
}

@media (min-width: 960px) {
  .category-top-grid {
    grid-template-columns: 128px minmax(0, 1fr);
    gap: 20px;
  }

  .category-thumb-cell {
    justify-content: flex-start;
  }
}
</style>
