<template>
  <AppPageHeader title="Categories" subtitle="Category list">
    <template #actions>
      <v-menu location="bottom start">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="tonal" color="primary" prepend-icon="mdi-download-outline">
            Export
          </v-btn>
          <v-btn variant="outlined" color="primary" prepend-icon="mdi-pencil-outline" @click="openOrderModal">
            Edit Category Sequence
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

  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    show-select
    v-model:selected="selectedCategories"
    @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppSearchTextField v-model="search" label="Search" placeholder="Search by name..."
                @click:clear="onClearSearch" />

           
              <AppSearchButton :loading="fetchingState" @click="onSearch" />
            </div>
          </v-col>

          <v-spacer></v-spacer>

          <v-col cols="12" md="auto" class="text-right">
            <v-btn
              v-if="selectedCategories.length > 0"
              color="error"
              variant="flat"
              prepend-icon="mdi-delete"
              :loading="bulkDeleting"
              @click="onBulkDelete">
              Bulk Delete ({{ selectedCategories.length }})
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </template>
    <template #item.title="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.thumb" :src="item.thumb" :alt="item.title" class="category-thumb" />
          <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
        <div>
          <p class="text-primary">{{ item.title }}</p>
          <p class="text-caption">{{ item.slug }}</p>
        </div>
      </div>
    </template>
    <template #item.products_count="{ item }">
      <span>{{ item.products_count ?? 0 }} products</span>
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
      <div class="d-flex align-center justify-end ga-2">
        <v-btn size="small" variant="outlined" color="primary" @click="onView(item)">
          Details
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
import ProductCategoryBulkDeleteModal from '@/components/category/ProductCategoryBulkDeleteModal.vue';
import ProductCategoryDeleteButton from '@/components/category/ProductCategoryDeleteButton.vue';
import ProductCategoryOrderModal from '@/components/category/ProductCategoryOrderModal.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import {
  bulkDeleteProductCategories,
  listProductCategories,
  type ProductCategoryListItem,
} from '@/api/product-categories.api';
import { formatLongDate } from '@/shared/utils';
import AppSearchTextField from '@/components/shared/AppSearchTextField.vue';
import AppSelectField from '@/components/shared/AppSelectField.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';
import { openModal } from '@/shared/modal';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

type ProductCategory = {
  id: number;
  title: string;
  parent_title: string;
  slug: string;
  thumb: string;
  products_count: number;
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
  { title: 'Parent Category', key: 'parent_title', sortable: false, minWidth: '200' },
  // { title: 'Slug', key: 'slug', sortable: false, minWidth: '240' },
  { title: 'Products', key: 'products_count', sortable: false, minWidth: '120' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '140' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120', align: "end" as const },
];

const items = ref<ProductCategory[]>([]);
const total = ref(0);
const loading = ref(false);
const selectedCategories = ref<Array<string | number>>([]);
const search = ref('');
const categoryFilter = ref('');

const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const router = useRouter();
const categoryOptions = ref<Array<{ title: string; value: number | string | null }>>([]);
const fetchingState = ref(false);
const bulkDeleting = ref(false);
const snackbar = useSnackbarStore();

function onExport(type: ExportType) {
  // TODO: replace with real export API/download logic.
  console.log(`Export clicked: ${type}`);
}

function openOrderModal() {
  openModal(
    ProductCategoryOrderModal,
    {},
    {
      size: 'xl',
      title: 'Edit Category Order',
      onSaved: () => fetchCategories(),
    },
  );
}

function onView(category: ProductCategory) {
  router.push({ name: 'admin.product.categories.detail', params: { id: category.id } });
}

async function fetchCategories() {
  loading.value = true;
  try {
    selectedCategories.value = [];
    const response = await listProductCategories({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((category: ProductCategoryListItem) => ({
      id: Number(category.id),
      title: category.title ?? '-',
      parent_title: typeof category.parent_title === 'string'
        ? category.parent_title
        : '-',
      slug: category.slug ?? '-',
      status: Boolean(category.status),
      created_at: category.created_at ?? '-',
      thumb: typeof category.thumb === 'string' ? category.thumb : '',
      products_count: Number(category.products_count ?? 0),
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
  const currentSortBy = JSON.stringify(options.value.sortBy ?? []);
  const nextSortBy = JSON.stringify(next.sortBy ?? []);

  if (
    options.value.page === next.page
    && options.value.itemsPerPage === next.itemsPerPage
    && currentSortBy === nextSortBy
  ) {
    return;
  }

  options.value = next;
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

function onBulkDelete() {
  if (selectedCategories.value.length === 0) return;

  openModal(
    ProductCategoryBulkDeleteModal,
    {
      count: selectedCategories.value.length,
      onConfirm: bulkDelete,
    },
    {
      title: 'Confirm Category Deletion',
      size: 'sm',
    },
  );
}

async function bulkDelete() {
  bulkDeleting.value = true;
  try {
    const selectedIds = [...selectedCategories.value];
    const response = await bulkDeleteProductCategories(selectedIds);
    const deletedCount = Number((response as any)?.data?.deleted_count ?? selectedIds.length);
    snackbar.show({
      message: (response as any)?.data?.message ?? `Deleted ${deletedCount} ${deletedCount === 1 ? 'category' : 'categories'} successfully.`,
      color: 'success',
    });
    selectedCategories.value = [];
    await fetchCategories();
  } catch (error) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
    throw error;
  } finally {
    bulkDeleting.value = false;
  }
}

onMounted(() => {
  fetchCategories();
});
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.category-thumb .v-img__img) {
  object-fit: contain;
}
</style>
