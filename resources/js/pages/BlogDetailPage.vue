<template>
  <AppPageHeader title="Blog Detail" subtitle="View blog information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <!-- <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading blog detail...</div> -->
    <div class="blog-top-grid">
      <div class="blog-thumb-cell">
        <v-avatar size="112" rounded="lg" color="grey-lighten-3">
          <v-img v-if="blogDetail?.thumb" :src="String(blogDetail.thumb)" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
      </div>
      <div>
        <div class="text-h6">{{ blogDetail?.title || '-' }}</div>

        <div class="d-flex align-center ga-2 mt-2">
          <span class="text-body-2 text-medium-emphasis">{{ blogUrl || '-' }}</span>
          <v-btn
            v-if="blogUrl"
            :href="blogUrl"
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
          :item="blogDetail"
          :blog-id="blogId"
          @updated="fetchBlogDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getBlogDetail, type BlogDetailResponse } from '@/api/blogs.api';
import BlogDetailTabContent from '@/components/blog/BlogDetailTabContent.vue';
import BlogDetailTabImages from '@/components/blog/BlogDetailTabImages.vue';
import BlogDetailTabOverview from '@/components/blog/BlogDetailTabOverview.vue';
import BlogDetailTabSeo from '@/components/blog/BlogDetailTabSeo.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
  { value: 'overview', label: 'Overview', icon: 'mdi-view-dashboard-outline', component: BlogDetailTabOverview },
  { value: 'content', label: 'Content', icon: 'mdi-text-box-outline', component: BlogDetailTabContent },
  { value: 'images', label: 'Images', icon: 'mdi-image-multiple-outline', component: BlogDetailTabImages },
  { value: 'seo', label: 'SEO', icon: 'mdi-magnify', component: BlogDetailTabSeo },
];

const blogId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const blogDetail = ref<BlogDetailResponse | null>(null);
const siteOrigin = typeof window !== 'undefined' ? window.location.origin : '';

const blogUrl = computed(() => {
  const slug = String(blogDetail.value?.slug ?? '').trim();
  if (!slug) return '';
  if (/^https?:\/\//i.test(slug)) return slug;
  const normalized = slug.startsWith('/') ? slug : `/${slug}`;
  return `${siteOrigin}${normalized}`;
});

async function fetchBlogDetail() {
  if (!blogId.value) return;
  loading.value = true;
  try {
    blogDetail.value = await getBlogDetail(blogId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.blogs.list' });
}

onMounted(fetchBlogDetail);
</script>

<style scoped>
.blog-top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.blog-thumb-cell {
  display: flex;
  justify-content: center;
}

@media (min-width: 960px) {
  .blog-top-grid {
    grid-template-columns: 128px minmax(0, 1fr);
    gap: 20px;
  }

  .blog-thumb-cell {
    justify-content: flex-start;
  }
}
</style>
