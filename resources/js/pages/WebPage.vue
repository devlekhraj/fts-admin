<template>
  <AppPageHeader title="Pages" subtitle="Manage static web pages">
    <template #actions>
      <PageCreateButton @saved="() => fetchPages()" />
    </template>
  </AppPageHeader>

  <v-container fluid class="px-0">
    <v-row>
      <v-col cols="12">
        <AppDataTable
          :headers="headers"
          :items="items"
          :total="total"
          :loading="loading"
          :page="options.page"
          :items-per-page="options.itemsPerPage"
          :search="search"
          @update:options="onOptions">

          <template #actions>
            <v-container fluid class="py-4">
              <v-row align="center">
                <v-col cols="12" md="auto">
                  <div class="d-flex align-center ga-3">
                    <v-text-field
                      v-model="search"
                      density="compact"
                      variant="outlined"
                      label="Search"
                      placeholder="Search title or slug..."
                      prepend-inner-icon="mdi-magnify"
                      hide-details
                      clearable
                      style="min-width: 320px"
                      @click:clear="handleSearch"
                      @keyup.enter="handleSearch">
                    </v-text-field>
                    <v-btn color="primary" variant="tonal" height="40" @click="handleSearch">
                      <v-icon start>mdi-magnify</v-icon>
                      Search
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-container>
          </template>

          <template #item.status="{ item }">
            <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'warning'">
              {{ item.status ? 'Published' : 'Draft' }}
            </v-chip>
          </template>

          <template #item.updated_at="{ item }">
            <span class="caption-text">{{ formatLongDate(item.updated_at) ?? '-' }}</span>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex justify-end ga-2">
              <v-btn
                size="small"
                variant="tonal"
                color="primary"
                class="text-none"
                @click="onView(item)">
                Detail
              </v-btn>
              <PageDeleteButton :page="item" @deleted="onDeleted" />
            </div>
          </template>
        </AppDataTable>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listPages, type PageListItem, type PageListResponse } from '@/api/pages.api';
import { formatLongDate } from '@/shared/utils';
import { useRouter } from 'vue-router';
import PageDeleteButton from '@/components/page/PageDeleteButton.vue';
import PageCreateButton from '@/components/page/PageCreateButton.vue';

const headers = [
  { title: 'Title', key: 'title', sortable: false, minWidth: '240' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '200' },
  // { title: 'Status', key: 'status', sortable: false, minWidth: '120' },
  { title: 'Updated At', key: 'updated_at', sortable: false, minWidth: '180' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '160', align: 'end' as const },
];

const items = ref<PageListItem[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

async function fetchPages(params?: { page?: number; per_page?: number; search?: string }) {
  loading.value = true;
  try {
    const response: PageListResponse = await listPages({
      page: params?.page ?? options.value.page,
      per_page: params?.per_page ?? options.value.itemsPerPage,
      search: params?.search ?? (search.value || undefined),
    });

    const list = Array.isArray(response) ? (response as unknown as PageListItem[]) : response?.data ?? [];
    items.value = list.map((page: PageListItem) => ({
      ...page,
      status: Boolean(page.status),
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
    hasLoadedOnce.value = true;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  if (!hasLoadedOnce.value) {
    // Skip the initial emit from the data table; the first fetch runs on mount.
    return;
  }
  fetchPages({
    page: next.page,
    per_page: next.itemsPerPage,
  });
}

function handleSearch() {
  options.value.page = 1;
  fetchPages({
    page: 1,
    per_page: options.value.itemsPerPage,
    search: search.value,
  });
}

function onView(item: PageListItem) {
  router.push({ name: 'admin.pages.detail', params: { id: item.id } });
}

function onDeleted() {
  options.value.page = 1;
  fetchPages({
    page: options.value.page,
    per_page: options.value.itemsPerPage,
    search: search.value,
  });
}

onMounted(() => {
  fetchPages();
});
</script>
