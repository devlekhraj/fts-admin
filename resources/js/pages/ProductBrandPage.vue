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
          <v-list-item v-for="option in exportOptions" :key="option.type" :title="option.title"
            :prepend-icon="option.icon" @click="onExport(option.type)" />
        </v-list>
      </v-menu>

      <v-btn variant="outlined" color="primary" prepend-icon="mdi-pencil-outline" @click="openOrderModal">
        Edit Brand Sequence
      </v-btn>

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
    show-select
    v-model:selected="selectedBrands"
    @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppSearchTextField v-model="search" label="Search" placeholder="Type ..."
                @click:clear="onClearSearch" />
              <AppSearchButton :loading="fetchingState" @click="onSearch" />
            </div>
          </v-col>
          <v-spacer></v-spacer>
          <v-col cols="12" md="auto" class="text-right">
            <v-btn
              v-if="selectedBrands.length > 0"
              color="error"
              variant="flat"
              prepend-icon="mdi-delete"
              :loading="bulkDeleting"
              @click="onBulkDelete">
              Bulk Delete ({{ selectedBrands.length }})
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
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
    <template #item.categories_count="{ item }">
      <button
        type="button"
        class="brand-categories-link text-primary"
        @click="openCategoriesModal(item)">
        {{ item.categories_count }} items
      </button>
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
      <div class="d-flex align-center justify-end ga-1">
        <v-btn size="small" variant="outlined" color="primary" @click="onView(item)">
          Details
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
import BrandBulkDeleteModal from '@/components/brand/BrandBulkDeleteModal.vue';
import BrandDeleteButton from '@/components/brand/BrandDeleteButton.vue';
import ProductBrandOrderModal from '@/components/brand/ProductBrandOrderModal.vue';
import type { DataTableHeader, DataTableOptions } from '@/components/datatable/types';
import { bulkDeleteBrands, listBrands, type ProductBrandListItem } from '@/api/products.api';
import { formatLongDate } from '@/shared/utils';
import AppSearchTextField from '@/components/shared/AppSearchTextField.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';
import { openModal } from '@/shared/modal';
import BrandCategoriesModal from '@/components/brand/BrandCategoriesModal.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

type ProductBrand = {
  id: number | string;
  name: string;
  logo: string;
  slug: string;
  seq_no: number;
  status: boolean;
  total_products: number;
  categories_count: number;
  created_at: string;
};

type ExportType = 'csv' | 'excel' | 'pdf';

const exportOptions: Array<{ type: ExportType; title: string; icon: string }> = [
  { type: 'csv', title: 'Export CSV', icon: 'mdi-file-delimited-outline' },
  { type: 'excel', title: 'Export Excel', icon: 'mdi-microsoft-excel' },
  { type: 'pdf', title: 'Export PDF', icon: 'mdi-file-pdf-box' },
];

const headers: DataTableHeader[] = [
  { title: 'Name', key: 'name', sortable: false, minWidth: '220' },
  // { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Products', key: 'total_products', sortable: false, minWidth: '160' },
  { title: 'Total Categories', key: 'categories_count', sortable: false, minWidth: '160' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '140' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120', align: 'end' },
];

const items = ref<ProductBrand[]>([]);
const total = ref(0);
const loading = ref(false);
const selectedBrands = ref<Array<string | number>>([]);
const search = ref('');
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();
const fetchingState = ref(false);
const bulkDeleting = ref(false);
const snackbar = useSnackbarStore();

function onExport(type: ExportType) {
  // TODO: replace with real export API/download logic.
  console.log(`Export clicked: ${type}`);
}

function openOrderModal() {
  openModal(
    ProductBrandOrderModal,
    {},
    {
      size: 'xl',
      title: 'Edit Brand Order',
      onSaved: () => fetchBrands(),
    },
  );
}

function openCategoriesModal(brand: ProductBrand) {
  openModal(
    BrandCategoriesModal,
    { brand },
    {
      title: 'Brand Categories',
      size: 'lg',
    },
  );
}

function onView(brand: ProductBrand) {
  router.push({ name: 'admin.product.brands.detail', params: { id: brand.id } });
}

async function fetchBrands() {
  loading.value = true;
  try {
    selectedBrands.value = [];
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
      seq_no: Number(brand.seq_no ?? 0),
      status: Boolean(brand.status),
      total_products: Number(brand.total_products ?? 0),
      categories_count: Number(brand.categories_count ?? 0),
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
    fetchingState.value = false;
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
  fetchingState.value = true;
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

function onBulkDelete() {
  if (selectedBrands.value.length === 0) return;

  openModal(
    BrandBulkDeleteModal,
    {
      count: selectedBrands.value.length,
      onConfirm: bulkDelete,
    },
    {
      title: 'Confirm Brand Deletion',
      size: 'sm',
    },
  );
}

async function bulkDelete() {
  bulkDeleting.value = true;
  try {
    const selectedIds = [...selectedBrands.value];
    const response = await bulkDeleteBrands(selectedIds);
    const deletedCount = Number((response as any)?.data?.deleted_count ?? selectedIds.length);
    snackbar.show({
      message: (response as any)?.data?.message ?? `Deleted ${deletedCount} ${deletedCount === 1 ? 'brand' : 'brands'} successfully.`,
      color: 'success',
    });
    selectedBrands.value = [];
    await fetchBrands();
  } catch (error) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
    throw error;
  } finally {
    bulkDeleting.value = false;
  }
}
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.brand-logo .v-img__img) {
  object-fit: contain;
}

.brand-categories-link {
  appearance: none;
  background: transparent;
  border: 0;
  padding: 0;
  font: inherit;
  cursor: pointer;
  text-decoration: none;
}

.brand-categories-link:hover {
  text-decoration: underline;
}
</style>
