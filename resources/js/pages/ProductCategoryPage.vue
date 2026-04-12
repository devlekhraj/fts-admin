<template>
  <AppPageHeader title="Categories" subtitle="Category list">
    <template #actions>
      <v-menu location="bottom start">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="tonal" color="primary" prepend-icon="mdi-download-outline">
            Export
          </v-btn>
        </template>
        <v-list class="export-menu-list" density="comfortable" min-width="170">
          <v-list-item v-for="option in exportOptions" :key="option.type" :title="option.title"
            :prepend-icon="option.icon" @click="onExport(option.type)" />
        </v-list>
      </v-menu>

      <ProductCategoryCreateButton @saved="onCategoryCreated" />
    </template>
  </AppPageHeader>

  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
    :items-per-page="options.itemsPerPage" @update:options="onOptions">
    <template #actions>
      <PageFilter
        v-model:search="search"
        search-label="Search categories"
        search-placeholder="Search by title or slug"
        :total="total"
        total-label="Items found."
        @search="onSearch"
        @clear="onClearSearch"
      />
    </template>
    <template #item.title="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.thumb" :src="item.thumb" :alt="item.title" class="category-thumb" />
          <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
        <span>{{ item.title }}</span>
      </div>
    </template>
    <template #item.created_at="{ item }">
      <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'warning'">
        {{ item.status ? 'Active' : 'Inactive' }}
      </v-chip>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="onView(item)">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
        <ProductCategoryDeleteButton :category="item" @deleted="onCategoryDeleted" />
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import PageFilter from '@/components/filters/PageFilter.vue';
import ProductCategoryCreateButton from '@/components/category/ProductCategoryCreateButton.vue';
import ProductCategoryDeleteButton from '@/components/category/ProductCategoryDeleteButton.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import {
  listProductCategories,
  type ProductCategoryListItem,
} from '@/api/product-categories.api';
import { formatLongDate } from '@/shared/utils';

type ProductCategory = {
  id: number;
  title: string;
  slug: string;
  thumb: string;
  status: boolean;
  created_at: string;
};

type ExportType = 'csv' | 'excel' | 'pdf';

const exportOptions: Array<{ type: ExportType; title: string; icon: string }> = [
  { type: 'csv', title: 'Export CSV', icon: 'mdi-file-delimited-outline' },
  { type: 'excel', title: 'Export Excel', icon: 'mdi-microsoft-excel' },
  { type: 'pdf', title: 'Export PDF', icon: 'mdi-file-pdf-box' },
];

const headers = [
  { title: 'Title', key: 'title', sortable: false, minWidth: '240' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '240' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '140' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

const items = ref<ProductCategory[]>([]);
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

function onExport(type: ExportType) {
  // TODO: replace with real export API/download logic.
  console.log(`Export clicked: ${type}`);
}

function onView(category: ProductCategory) {
  router.push({ name: 'admin.product.categories.detail', params: { id: category.id } });
}

async function fetchCategories() {
  loading.value = true;
  try {
    const response = await listProductCategories({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((category: ProductCategoryListItem) => ({
      id: Number(category.id),
      title: category.title ?? '-',
      slug: category.slug ?? '-',
      status: Boolean(category.status),
      created_at: category.created_at ?? '-',
      thumb: typeof category.thumb === 'string' ? category.thumb : '',
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
  fetchCategories();
}

function onSearch() {
  options.value.page = 1;
  fetchCategories();
}

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchCategories();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchCategories();
    hasLoadedOnce.value = true;
  }
});

function onCategoryCreated(payload?: unknown) {
  const created: any = payload ?? {};
  if (created?.id) {
    router.push({ name: 'admin.product.categories.detail', params: { id: created.id } });
    return;
  }
  options.value.page = 1;
  fetchCategories();
}

function onCategoryDeleted() {
  options.value.page = 1;
  fetchCategories();
}
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.category-thumb .v-img__img) {
  object-fit: contain;
}
</style>
