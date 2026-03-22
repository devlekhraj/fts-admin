<template>
  <AppPageHeader title="Campaigns" subtitle="Marketing campaigns" />

  <v-card class="pa-6 mt-4" variant="flat">
    <div v-if="loading" class="text-body-2 text-medium-emphasis">Loading campaign detail...</div>
    <div v-else-if="campaign" class="d-flex flex-column ga-4">
      <div>
        <div class="text-overline text-medium-emphasis">Campaign Title</div>
        <div class="text-h5 font-weight-bold">{{ campaign.title || '-' }}</div>
      </div>
      
      <v-divider class="my-2"></v-divider>

      <v-row>
        <v-col cols="12" sm="6" md="3">
          <div class="text-overline text-medium-emphasis">Start Date</div>
          <div class="text-body-1">{{ formatLongDate(campaign.start_date) || '-' }}</div>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <div class="text-overline text-medium-emphasis">End Date</div>
          <div class="text-body-1">{{ formatLongDate(campaign.end_date) || '-' }}</div>
        </v-col>
        <v-col cols="12" sm="6" md="3">
          <div class="text-overline text-medium-emphasis">Status</div>
          <div class="mt-1">
            <v-chip size="small" label variant="tonal" :color="Boolean(campaign.is_published) ? 'success' : 'warning'">
              {{ Boolean(campaign.is_published) ? 'Published' : 'Draft' }}
            </v-chip>
          </div>
        </v-col>
      </v-row>

      <v-divider class="my-2"></v-divider>

      <div>
        <CampaignProducts :campaign="campaign" />
      </div>
    </div>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';

import { show } from '@/api/campaigns.api';
import { formatLongDate } from '@/shared/utils';
import CampaignProducts from '@/components/campaign-detail/CampaignProducts.vue';
import { Campaign } from '@/types/models';

const route = useRoute();
const router = useRouter();

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
