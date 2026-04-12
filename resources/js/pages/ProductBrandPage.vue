<template>
  <AppPageHeader title="Brands" subtitle="Brand list">
    <template #actions>
      <v-menu location="bottom start">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="tonal" color="primary" prepend-icon="mdi-download-outline">
            Export
          </v-btn>
        </template>
        <v-list class="export-menu-list" density="comfortable" min-width="170">
          <v-list-item
            v-for="option in exportOptions"
            :key="option.type"
            :title="option.title"
            :prepend-icon="option.icon"
            @click="onExport(option.type)" />
        </v-list>
      </v-menu>

      <BrandCreateButton @saved="onBrandCreated" />
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    @update:options="onOptions">
    <template #actions>
      <PageFilter
        v-model:search="search"
        search-label="Search brands"
        search-placeholder="Search by name or slug"
        :total="total"
        total-label="Items found."
        @search="onSearch"
        @clear="onClearSearch"
      />
    </template>
    <template #item.name="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.logo" :src="item.logo" :alt="item.name" class="brand-logo" />
          <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
        <span>{{ item.name }}</span>
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
    <template #item.total_products="{ item }">
      <span>{{ item.total_products }} products</span>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="onView(item)">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
        <BrandDeleteButton :brand="item" @deleted="onBrandDeleted" />
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
import BrandCreateButton from '@/components/brand/BrandCreateButton.vue';
import BrandDeleteButton from '@/components/brand/BrandDeleteButton.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listBrands, type ProductBrandListItem } from '@/api/products.api';
import { formatLongDate } from '@/shared/utils';

type ProductBrand = {
  id: number | string;
  name: string;
  logo: string;
  slug: string;
  status: boolean;
  total_products: number;
  created_at: string;
};

type ExportType = 'csv' | 'excel' | 'pdf';

const exportOptions: Array<{ type: ExportType; title: string; icon: string }> = [
  { type: 'csv', title: 'Export CSV', icon: 'mdi-file-delimited-outline' },
  { type: 'excel', title: 'Export Excel', icon: 'mdi-microsoft-excel' },
  { type: 'pdf', title: 'Export PDF', icon: 'mdi-file-pdf-box' },
];

const headers = [
  { title: 'Name', key: 'name', sortable: false, minWidth: '220' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Products', key: 'total_products', sortable: false, minWidth: '160' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '140' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

const items = ref<ProductBrand[]>([]);
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

function onView(brand: ProductBrand) {
  router.push({ name: 'admin.product.brands.detail', params: { id: brand.id } });
}

async function fetchBrands() {
  loading.value = true;
  try {
    const response = await listBrands({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((brand: ProductBrandListItem) => ({
      id: brand.id,
      name: brand.name ?? '-',
      logo: typeof brand.logo === 'string' ? brand.logo : '',
      slug: brand.slug ?? '-',
      status: Boolean(brand.status),
      total_products: Number(brand.total_products ?? 0),
      created_at: brand.created_at ?? '',
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
  fetchBrands();
}

function onSearch() {
  options.value.page = 1;
  fetchBrands();
}

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchBrands();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchBrands();
    hasLoadedOnce.value = true;
  }
});

function onBrandCreated(payload?: unknown) {
  const created: any = payload ?? {};
  if (created?.id) {
    router.push({ name: 'admin.product.brands.detail', params: { id: created.id } });
    return;
  }
  options.value.page = 1;
  fetchBrands();
}

function onBrandDeleted() {
  options.value.page = 1;
  fetchBrands();
}
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.brand-logo .v-img__img) {
  object-fit: contain;
}
</style>
