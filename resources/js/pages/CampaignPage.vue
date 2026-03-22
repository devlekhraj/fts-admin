<template>
  <AppPageHeader title="Campaigns" subtitle="Marketing campaigns" />

  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <AppDataTable
          :headers="headers"
          :items="items"
          :total="total"
          :loading="loading"
          :page="options.page"
          :items-per-page="options.itemsPerPage"
          @update:options="onOptions">
          
          <template #item.title="{ item }">
            <span class="font-weight-medium">{{ item.title }}</span>
          </template>

          <template #item.status="{ item }">
            <v-chip size="small" label variant="tonal" :color="item.status === 'active' ? 'success' : 'warning'">
              <span class="text-capitalize">{{ item.status || 'draft' }}</span>
            </v-chip>
          </template>

          <template #item.start_date="{ item }">
            <div>
              <div v-if="!item.start_date">-</div>
              <div v-else>
                <div>{{ formatLongDate(item.start_date) }} - {{ formatLongDate(item.end_date) }}</div>
              </div>
            </div>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex align-center ga-1">
              <v-btn icon size="x-small" variant="tonal" color="primary" @click="router.push({ name: 'admin.campaigns.detail', params: { id: item.id } })">
                <v-icon size="16">mdi-eye</v-icon>
              </v-btn>
              <v-btn icon size="x-small" variant="tonal" color="error" @click="onDelete(item)">
                <v-icon size="16">mdi-delete</v-icon>
              </v-btn>
            </div>
          </template>

        </AppDataTable>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list } from '@/api/campaigns.api';
import { formatLongDate } from '@/shared/utils';

const headers = [
  { title: 'Name', key: 'title', sortable: false, minWidth: '240' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '120' },
  { title: 'Duration', key: 'start_date', sortable: false, minWidth: '160' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

type Campaign = {
  id: number | string;
  title: string;
  slug: string;
  status: string;
  start_date: string | null;
  end_date: string | null;
};

const items = ref<Campaign[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

function onDelete(campaign: Campaign) {
  console.log('Delete campaign:', campaign.slug);
}

async function fetchCampaigns() {
  loading.value = true;
  try {
    const response = (await list({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    })) as any;

    const dataList = Array.isArray(response) ? response : response?.data ?? [];
    items.value = dataList.map((campaign: any) => ({
      id: campaign.id,
      title: campaign.title ?? '-',
      slug: campaign.slug ?? '-',
      status: campaign.status ?? 'draft',
      start_date: campaign.start_date ?? null,
      end_date: campaign.end_date ?? null,
    }));
    total.value = Number(response?.total ?? response?.meta?.total ?? dataList.length);
    
    if (response?.meta?.current_page) {
      options.value.page = Number(response.meta.current_page);
    }
    if (response?.meta?.per_page) {
      options.value.itemsPerPage = Number(response.meta.per_page);
    }
  } catch (error) {
    console.error('Failed to fetch campaigns', error);
  } finally {
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  if (!hasLoadedOnce.value) {
    hasLoadedOnce.value = true;
  }
  fetchCampaigns();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchCampaigns();
    hasLoadedOnce.value = true;
  }
});
</script>
