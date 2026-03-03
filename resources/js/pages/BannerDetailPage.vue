<template>
  <AppPageHeader title="Banner Detail" subtitle="View banner information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="banner-top-grid">
      <div class="banner-thumb-cell">
        <v-avatar size="112" rounded="lg" color="grey-lighten-3">
          <v-img v-if="bannerDetail?.thumb" :src="bannerDetail.thumb" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
      </div>
      <div>
        <!-- <div class="text-overline text-medium-emphasis">Banner Name</div> -->
        <div class="text-h6">{{ bannerDetail?.name || '-' }}</div>

        <!-- <div class="text-overline text-medium-emphasis mt-3">Banner URL</div> -->
        <div class="d-flex align-center ga-2">
          <span class="text-body-2 text-medium-emphasis">{{ bannerUrl || '-' }}</span>
          <v-btn
            v-if="bannerUrl"
            :href="bannerUrl"
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

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading banner detail...</div>
  </v-card>

  <v-card class="mt-4">
    <v-tabs v-model="activeTab" color="primary">
      <v-tab v-for="tab in tabItems" :key="tab.value" :value="tab.value">{{ tab.label }}</v-tab>
    </v-tabs>
    <v-divider />
    <v-window v-model="activeTab">
      <v-window-item v-for="tab in tabItems" :key="tab.value" :value="tab.value">
        <component :is="tab.component" :item="bannerDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getBannerDetail, type BannerDetailResponse } from '@/api/banners.api';
import BannerDetailTabImages from '@/components/banner/BannerDetailTabImages.vue';
import BannerDetailTabOverview from '@/components/banner/BannerDetailTabOverview.vue';
import BannerDetailTabSeo from '@/components/banner/BannerDetailTabSeo.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('images');
const tabItems = [
  { value: 'images', label: 'Images', component: BannerDetailTabImages },
  { value: 'overview', label: 'Overview', component: BannerDetailTabOverview },
  { value: 'seo', label: 'SEO', component: BannerDetailTabSeo },
];

const bannerId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const bannerDetail = ref<BannerDetailResponse | null>(null);
const siteOrigin = typeof window !== 'undefined' ? window.location.origin : '';
const bannerUrl = computed(() => {
  const slug = String(bannerDetail.value?.slug ?? '').trim();
  if (!slug) return '';
  if (/^https?:\/\//i.test(slug)) return slug;
  const normalized = slug.startsWith('/') ? slug : `/${slug}`;
  return `${siteOrigin}${normalized}`;
});

async function fetchBannerDetail() {
  if (!bannerId.value) return;
  loading.value = true;
  try {
    bannerDetail.value = await getBannerDetail(bannerId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.banners.list' });
}

onMounted(fetchBannerDetail);
</script>

<style scoped>
.banner-top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.banner-thumb-cell {
  display: flex;
  justify-content: center;
}

@media (min-width: 960px) {
  .banner-top-grid {
    grid-template-columns: 128px minmax(0, 1fr);
    gap: 20px;
  }

  .banner-thumb-cell {
    justify-content: flex-start;
  }
}

</style>
