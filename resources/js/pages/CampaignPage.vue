<template>
  <AppPageHeader title="Campaigns" subtitle="Marketing campaigns" />

  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
          :items-per-page="options.itemsPerPage" @update:options="onOptions">

          <template #actions>
            <v-container fluid class="py-4">
              <v-row align="center">
                <v-col cols="12" md="auto">
                  <div class="d-flex align-center ga-3">
                    <v-text-field v-model="search" density="compact" variant="outlined" label="Search"
                      placeholder="Search by name..." prepend-inner-icon="mdi-magnify" hide-details
                      clearable style="min-width: 300px" @click:clear="handleSearch" @keyup.enter="handleSearch" />
                    <v-btn color="primary" variant="tonal" height="40" @click="handleSearch">
                      <v-icon start>mdi-magnify</v-icon>
                      Search
                    </v-btn>
                  </div>
                </v-col>

                <v-spacer></v-spacer>

                <v-col cols="12" md="auto">
                  <v-btn variant="flat" color="primary" @click="openAddModal">
                    <v-icon start>mdi-plus</v-icon>
                    add campaign
                  </v-btn>
                </v-col>
              </v-row>
              <v-row class="mt-0">
                <v-col cols="12">
                  <div class="text-medium-emphasis">
                    <span class="text-primary" style="font-size: smaller;">
                      Total: {{ total }} Items found.
                    </span>
                  </div>
                </v-col>
              </v-row>
            </v-container>
          </template>

          <template #item.title="{ item }">
            <div class="d-flex align-center ga-2">
              <v-avatar size="28" color="grey-lighten-3" rounded>
                <v-img v-if="item.thumb" :src="item.thumb.url" :alt="item.title" cover />
                <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
              </v-avatar>
              <span class="font-weight-medium">{{ item.title }}</span>
            </div>
          </template>

          <template #item.status="{ item }">
            <v-chip size="small" label variant="tonal" :color="item.status === 'active' ? 'success' : 'warning'">
              <span class="text-capitalize">{{ item.status || 'draft' }}</span>
            </v-chip>
          </template>
          <template #item.is_published="{ item }">
            <v-chip size="small" label variant="tonal" :color="item.is_published ? 'success' : 'error'">
              <span class="text-capitalize">{{ item.is_published ?'Yes' :'No' }}</span>
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

          <template #item.products_count="{ item }">
            <span class="font-weight-medium">{{ item.products_count }} products</span>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex align-center ga-1">
              <v-btn icon size="x-small" class="mr-1" variant="tonal" color="primary"
                @click="router.push({ name: 'admin.campaigns.detail', params: { id: item.id } })">
                <v-icon size="16">mdi-eye</v-icon>
              </v-btn>
              <v-btn icon size="x-small" class="mr-1" variant="tonal" color="primary"
                @click="openEditModal(item)">
                <v-icon size="16">mdi-square-edit-outline</v-icon>
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
import { show, list } from '@/api/campaigns.api';
import { formatLongDate } from '@/shared/utils';
import { useModalStore } from '@/stores/modal.store';
import CampaignForm from '@/components/campaign-detail/modal/CampaignForm.vue';
import CampaignDelete from '@/components/campaign-detail/modal/CampaignDelete.vue';

const headers = [
  { title: 'Name', key: 'title', sortable: false, minWidth: '240' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '120' },
  { title: 'Published', key: 'is_published', sortable: false, minWidth: '120' },
  { title: 'Duration', key: 'start_date', sortable: false, minWidth: '160' },
  { title: 'Products', key: 'products_count', sortable: false, minWidth: '120' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

type Campaign = {
  id: number | string;
  thumb?: { url: string; alt_text?: string } | null;
  title: string;
  slug: string;
  status: string;
  is_published: boolean;
  start_date: string | null;
  end_date: string | null;
  products_count: number;
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
const search = ref('');
const router = useRouter();
const modal = useModalStore();

async function openAddModal() {
  modal.open(
    CampaignForm,
    {},
    {
      size: 'md',
      title: 'Add Campaign',
      onSaved: () => fetchCampaigns(),
    }
  );
}

async function openEditModal(item: Campaign) {
  modal.open(
    CampaignForm,
    { item },
    {
      size: 'md',
      title: 'Edit Campaign',
      onSaved: () => fetchCampaigns(),
    }
  );
}

function handleSearch() {
  options.value.page = 1;
  fetchCampaigns();
}

function onDelete(item: Campaign) {
  modal.open(
    CampaignDelete,
    { item },
    {
      size: 'sm',
      title: 'Delete Campaign',
      onSaved: () => fetchCampaigns(),
    }
  );
}

async function fetchCampaigns() {
  loading.value = true;
  try {
    const response = (await list({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      name: search.value,
    })) as any;

    const dataList = Array.isArray(response) ? response : response?.data ?? [];
    items.value = dataList.map((campaign: any) => ({
      id: campaign.id,
      thumb: campaign.thumb ?? null,
      title: campaign.title ?? '-',
      is_published: campaign.is_published ?? false,
      slug: campaign.slug ?? '-',
      status: campaign.status ?? 'draft',
      start_date: campaign.start_date ?? null,
      end_date: campaign.end_date ?? null,
      products_count: campaign.products_count ?? 0,
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
<style scoped lang="scss">
:deep(.v-toolbar__content) {
	height: unset !important;
}
</style>
