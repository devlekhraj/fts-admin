<template>
  <AppPageHeader title="Products" subtitle="Product list">
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

      <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="openProductModal()">
        Create Product
      </v-btn>
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
    <template #item.name="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.thumb" :src="item.thumb" :alt="item.name" class="product-thumb" />
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
    <template #item.emi_enabled="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.emi_enabled ? 'primary' : 'grey'">
        {{ item.emi_enabled ? 'Enabled' : 'Disabled' }}
      </v-chip>
    </template>
    <template #item.variants_count="{ item }">
      <span>
        {{ Number(item.variants_count ?? 0) === 0 ? '-' : `${Number(item.variants_count ?? 0)} variants` }}
      </span>
    </template>
    <template #item.created_at="{ item }">
      <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="onView(item)">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
        <v-btn icon size="x-small" variant="tonal" color="error" @click="onDelete(item)">
          <v-icon size="16">mdi-delete</v-icon>
        </v-btn>
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listProducts, type ProductListItem } from '@/api/products.api';
import { formatLongDate } from '@/shared/utils';
import { openModal } from '@/shared/modal';
import ProductCreateModal from '@/components/product/ProductCreateModal.vue';

type Product = {
  id: number | string;
  name: string;
  slug: string;
  status: boolean;
  emi_enabled: boolean;
  variants_count: number;
  created_at: string;
  thumb: string;
};

type ExportType = 'csv' | 'excel' | 'pdf';

const exportOptions: Array<{ type: ExportType; title: string; icon: string }> = [
  { type: 'csv', title: 'Export CSV', icon: 'mdi-file-delimited-outline' },
  { type: 'excel', title: 'Export Excel', icon: 'mdi-microsoft-excel' },
  { type: 'pdf', title: 'Export PDF', icon: 'mdi-file-pdf-box' },
];

const headers = [
  { title: 'Name', key: 'name', sortable: false, minWidth: '260' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Variants', key: 'variants_count', sortable: false, minWidth: '120' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'EMI', key: 'emi_enabled', sortable: false, minWidth: '140' },
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '170' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

const items = ref<Product[]>([]);
const total = ref(0);
const loading = ref(false);
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

function onView(product: Product) {
  router.push({ name: 'admin.product.detail', params: { id: product.id } });
}

function onDelete(product: Product) {
  // TODO: replace with delete confirmation + API call.
  console.log('Delete product:', product.slug);
}

function openProductModal(){
  openModal(ProductCreateModal, {
    onSaved: (product: any) => {
      console.log({product});
      if (product?.id) {
        router.push({ name: 'admin.product.detail', params: { id: product.id } });
      } else {
        fetchProducts();
      }
    }
  }, {
    title: 'New Product'
  });
}

async function fetchProducts() {
  loading.value = true;
  try {
    const response = await listProducts({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((product: ProductListItem) => ({
      id: product.id,
      name: product.name ?? '-',
      slug: product.slug ?? '-',
      status: Boolean(product.status),
      emi_enabled: Boolean(product.emi_enabled),
      variants_count: Number(product.variants_count ?? 0),
      created_at: typeof product.created_at === 'string' ? product.created_at : '',
      thumb: typeof product.thumb === 'string' ? product.thumb : '',
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
  fetchProducts();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchProducts();
    hasLoadedOnce.value = true;
  }
});
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.product-thumb .v-img__img) {
  object-fit: contain;
}
</style>
