<template>
  <AppPageHeader title="Product Detail" subtitle="View product information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="product-top-grid">
      <div class="product-thumb-cell">
        <v-avatar size="112" rounded="lg" color="grey-lighten-3">
          <v-img v-if="productDetail?.overview?.thumb" :src="String(productDetail.overview.thumb)" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
      </div>
      <div>
        <div class="text-h6">{{ productDetail?.overview?.name || '-' }}</div>

        <div class="d-flex align-center ga-2 mt-2">
          <span class="text-body-2 text-medium-emphasis">{{ productUrl || '-' }}</span>
          <v-btn
            v-if="productUrl"
            :href="productUrl"
            target="_blank"
            rel="noopener noreferrer"
            icon
            size="x-small"
            variant="tonal"
            color="primary">
            <v-icon size="16">mdi-open-in-new</v-icon>
          </v-btn>
        </div>

        <div class="d-flex align-center ga-2 mt-3">
          <v-chip size="small" label variant="tonal" :color="productDetail?.overview?.status ? 'primary' : 'warning'">
            {{ productDetail?.overview?.status ? 'Active' : 'Inactive' }}
          </v-chip>
          <v-chip size="small" label variant="tonal" :color="productDetail?.overview?.emi_enabled ? 'success' : 'grey'">
            <v-icon start size="14">{{ productDetail?.overview?.emi_enabled ? 'mdi-check-circle' : 'mdi-close-circle' }}</v-icon>
            {{ productDetail?.overview?.emi_enabled ? 'EMI Enabled' : 'EMI Disabled' }}
          </v-chip>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading product detail...</div>
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
          :item="productDetail"
          :product-id="productId"
          @updated="fetchProductDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getProductDetail, type ProductDetailResponse } from '@/api/products.api';
import ProductDetailTabAttributes from '@/components/product/ProductDetailTabAttributes.vue';
import ProductDetailTabDescription from '@/components/product/ProductDetailTabDescription.vue';
import ProductDetailTabFreeGift from '@/components/product/ProductDetailTabFreeGift.vue';
import ProductDetailTabFaqs from '@/components/product/ProductDetailTabFaqs.vue';
import ProductDetailTabImages from '@/components/product/ProductDetailTabImages.vue';
import ProductDetailTabOverview from '@/components/product/ProductDetailTabOverview.vue';
import ProductDetailTabPriceStock from '@/components/product/ProductDetailTabPriceStock.vue';
import ProductDetailTabSchemaJsonld from '@/components/product/ProductDetailTabSchemaJsonld.vue';
import ProductDetailTabSeo from '@/components/product/ProductDetailTabSeo.vue';
import ProductDetailTabVariants from '@/components/product/ProductDetailTabVariants.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
  { value: 'overview', label: 'Overview', icon: 'mdi-view-dashboard-outline', component: ProductDetailTabOverview },
  { value: 'description', label: 'Description', icon: 'mdi-text-box-outline', component: ProductDetailTabDescription },
  { value: 'price_stock', label: 'Price & Stock', icon: 'mdi-cash-multiple', component: ProductDetailTabPriceStock },
  { value: 'attributes', label: 'Attributes', icon: 'mdi-tune-variant', component: ProductDetailTabAttributes },
  { value: 'variants', label: 'Variants', icon: 'mdi-shape-outline', component: ProductDetailTabVariants },
  { value: 'images', label: 'Images', icon: 'mdi-image-multiple-outline', component: ProductDetailTabImages },
  { value: 'faqs', label: 'FAQs', icon: 'mdi-help-circle-outline', component: ProductDetailTabFaqs },
  { value: 'free_gift', label: 'Free Gift', icon: 'mdi-gift-outline', component: ProductDetailTabFreeGift },
  { value: 'seo', label: 'SEO', icon: 'mdi-magnify', component: ProductDetailTabSeo },
  { value: 'schema_jsonld', label: 'Schema | JSON-LD', icon: 'mdi-code-json', component: ProductDetailTabSchemaJsonld },
];

const productId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const productDetail = ref<ProductDetailResponse | null>(null);
const siteOrigin = typeof window !== 'undefined' ? window.location.origin : '';

const productUrl = computed(() => {
  const slug = String(productDetail.value?.overview?.slug ?? '').trim();
  if (!slug) return '';
  if (/^https?:\/\//i.test(slug)) return slug;
  const normalized = slug.startsWith('/') ? slug : `/${slug}`;
  return `${siteOrigin}${normalized}`;
});

async function fetchProductDetail() {
  if (!productId.value) return;
  loading.value = true;
  try {
    productDetail.value = await getProductDetail(productId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.product.list' });
}

onMounted(fetchProductDetail);
</script>

<style scoped>
.product-top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.product-thumb-cell {
  display: flex;
  justify-content: center;
}

@media (min-width: 960px) {
  .product-top-grid {
    grid-template-columns: 128px minmax(0, 1fr);
    gap: 20px;
  }

  .product-thumb-cell {
    justify-content: flex-start;
  }
}
</style>
