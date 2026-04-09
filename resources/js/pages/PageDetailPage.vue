<template>
  <AppPageHeader title="Page Detail" subtitle="View page information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="top-grid">
      <div>
        <div class="text-h6">{{ pageDetail?.title || '-' }}</div>
        <div class="d-flex align-center ga-2 mt-2">
          <span class="text-body-2 text-medium-emphasis">/{{ pageDetail?.slug || '-' }}</span>
        </div>
        <div class="d-flex align-center ga-2 mt-3">
          <v-chip size="small" label variant="tonal" :color="pageDetail?.status ? 'success' : 'warning'">
            {{ pageDetail?.status ? 'Published' : 'Draft' }}
          </v-chip>
        </div>
        <div class="d-flex flex-column text-body-2 text-medium-emphasis mt-3">
          <span>Updated: {{ formatLongDate(pageDetail?.updated_at) ?? '-' }}</span>
          <span>Created: {{ formatLongDate(pageDetail?.created_at) ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading page detail...</div>
  </v-card>

  <v-card class="mt-4">
    <v-card-title>Content</v-card-title>
    <v-divider />
    <v-card-text>
      <div v-if="pageDetail?.content" class="content-text" v-html="pageDetail.content"></div>
      <div v-else class="text-medium-emphasis">No content available.</div>
    </v-card-text>
  </v-card>

  <v-card class="mt-4">
    <v-card-title>Meta</v-card-title>
    <v-divider />
    <v-card-text>
      <pre v-if="pageDetail?.meta" class="meta-block">{{ prettyMeta }}</pre>
      <div v-else class="text-medium-emphasis">No meta data.</div>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getPageDetail, type PageDetailResponse } from '@/api/pages.api';
import { formatLongDate } from '@/shared/utils';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const pageDetail = ref<PageDetailResponse | null>(null);
const pageId = computed(() => String(route.params.id ?? ''));

const prettyMeta = computed(() => {
  if (!pageDetail.value?.meta) return '';
  try {
    return JSON.stringify(pageDetail.value.meta, null, 2);
  } catch {
    return String(pageDetail.value.meta);
  }
});

async function fetchPageDetail() {
  if (!pageId.value) return;
  loading.value = true;
  try {
    pageDetail.value = await getPageDetail(pageId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.pages' });
}

onMounted(fetchPageDetail);
</script>

<style scoped>
.top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

@media (min-width: 960px) {
  .top-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 20px;
  }
}

.meta-block {
  background: #f8f9fb;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.9rem;
  white-space: pre-wrap;
}

.content-text {
  white-space: pre-line;
}
</style>
