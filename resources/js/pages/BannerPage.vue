<template>
  <AppPageHeader title="Banners" subtitle="Promotional banners" />

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
          <template #item.name="{ item }">
            <div class="d-flex align-center ga-2">
              <v-avatar size="28" color="grey-lighten-3" rounded>
                <v-img v-if="item.thumb" :src="item.thumb" :alt="item.name" class="banner-thumb" />
                <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
              </v-avatar>
              <span>{{ item.name }}</span>
            </div>
          </template>
          <template #item.status="{ item }">
            <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'warning'">
              {{ item.status ? 'Active' : 'Inactive' }}
            </v-chip>
          </template>
          <template #item.total_images="{ item }">
            <span class="text-primary cursor-pointer">{{ item.total_images }} images</span>
          </template>
          <template #item.created_at="{ item }">
            <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
          </template>
          <template #item.action="{ item }">
            <div class="d-flex align-center ga-1">
              <v-btn icon size="x-small" variant="tonal" color="primary" @click="router.push({ name: 'admin.banners.detail', params: { id: item.id } })">
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
import { listBanners, type BannerListItem } from '@/api/banners.api';
import { formatLongDate } from '@/shared/utils';

const headers = [
  { title: 'Name', key: 'name', sortable: false, minWidth: '240' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Images', key: 'total_images', sortable: false, minWidth: '120' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Created At', key: 'created_at', sortable: false, minWidth: '180' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

type Banner = {
  id: number | string;
  name: string;
  slug: string;
  status: boolean;
  created_at: string;
  thumb: string;
  total_images: number;
};

const items = ref<Banner[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

function onDelete(banner: Banner) {
  // TODO: replace with delete confirmation + API call.
  console.log('Delete banner:', banner.slug);
}

async function fetchBanners() {
  loading.value = true;
  try {
    const response = await listBanners({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((banner: BannerListItem) => ({
      id: banner.id,
      name: banner.name ?? '-',
      slug: banner.slug ?? '-',
      status: Boolean(banner.status),
      created_at: banner.created_at ?? '-',
      thumb: typeof banner.thumb === 'string' ? banner.thumb : '',
      total_images: Number(banner.total_images ?? 0),
    }));
    total.value = Number(response?.total ?? response?.meta?.total ?? list.length);
    if (response?.meta?.current_page) {
      options.value.page = Number(response.meta.current_page);
    }
    if (response?.meta?.per_page) {
      options.value.itemsPerPage = Number(response.meta.per_page);
    }
  } finally {
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  if (!hasLoadedOnce.value) {
    hasLoadedOnce.value = true;
  }
  fetchBanners();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchBanners();
    hasLoadedOnce.value = true;
  }
});
</script>

<style scoped>
:deep(.banner-thumb .v-img__img) {
  object-fit: contain;
}
</style>
