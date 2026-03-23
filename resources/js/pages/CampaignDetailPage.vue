<template>
  <AppPageHeader title="Campaigns" subtitle="Marketing campaigns">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6 mt-4" variant="flat">
    <div v-if="loading" class="text-body-2 text-medium-emphasis">Loading campaign detail...</div>
    <div v-else-if="campaign" class="d-flex flex-column ga-4">
      <div class="campaign-top-grid">
        <div class="campaign-thumb-cell">
          <div class="campaign-thumb-rect rounded">
            <v-img v-if="campaign.thumb" :src="campaign.thumb.url" :alt="campaign.title" contain width="180" height="112"
              class="campaign-thumb-image" />
            <v-icon v-else size="32" color="grey-darken-1">mdi-image-outline</v-icon>
          </div>
        </div>
        <div>
          <div class="text-h5 font-weight-bold">{{ campaign.title || '-' }}
            <v-chip size="small" label variant="tonal" :color="Boolean(campaign.is_published) ? 'success' : 'warning'">
              {{ Boolean(campaign.is_published) ? 'Published' : 'Draft' }}
            </v-chip>
          </div>
          <div class="text-body-1">{{ formatLongDate(campaign.start_date) || '-' }} - {{
            formatLongDate(campaign.end_date) || '-' }}</div>
        </div>
      </div>
    </div>
  </v-card>
    <v-card class="mt-4" variant="flat">
      <v-tabs v-model="activeTab" color="primary" bg-color="transparent">
        <v-tab v-for="tab in tabItems" :key="tab.value" :value="tab.value">
          {{ tab.label }}
        </v-tab>
      </v-tabs>
      <v-divider />
      <v-window v-model="activeTab" class="pa-4">
        <v-window-item v-for="tab in tabItems" :key="tab.value" :value="tab.value">
          <component :is="tab.component" :item="campaign" @changed="fetchCampaignDetail" />
        </v-window-item>
      </v-window>
    </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';

import { show } from '@/api/campaigns.api';
import { formatLongDate } from '@/shared/utils';
import CampaignDetailTabProducts from '@/components/campaign-detail/CampaignDetailTabProducts.vue';
import CampaignDetailTabImages from '@/components/campaign-detail/CampaignDetailTabImages.vue';
import { Campaign } from '@/types/models';

const route = useRoute();
const router = useRouter();

const activeTab = ref('products');
const tabItems = [
  { value: 'products', label: 'Products', component: CampaignDetailTabProducts },
  { value: 'images', label: 'Images', component: CampaignDetailTabImages },
];

const campaignId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const campaign = ref<Campaign | null>(null);

async function fetchCampaignDetail() {
  if (!campaignId.value) return;
  loading.value = true;
  try {
    const res = await show(campaignId.value);
    campaign.value = res?.data || (res as unknown as Campaign);
  } catch (error) {
    console.error('Failed to load campaign', error);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.campaigns.list' });
}

onMounted(fetchCampaignDetail);
</script>

<style scoped>
.campaign-top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.campaign-thumb-cell {
  display: flex;
  justify-content: center;
}

.campaign-thumb-rect {
  width: 180px;
  height: 112px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.campaign-thumb-image {
  width: 100%;
  height: 100%;
}

@media (min-width: 960px) {
  .campaign-top-grid {
    grid-template-columns: auto minmax(0, 1fr);
    gap: 20px;
  }

  .campaign-thumb-cell {
    justify-content: flex-start;
  }
}
</style>
