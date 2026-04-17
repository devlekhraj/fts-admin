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
          <v-list-item v-for="option in exportOptions" :key="option.type" :title="option.title"
            :prepend-icon="option.icon" @click="onExport(option.type)" />
        </v-list>
      </v-menu>

      <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="openProductModal()">
        Create Product
      </v-btn>
    </template>
  </AppPageHeader>

  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
    :items-per-page="options.itemsPerPage" v-model:expanded="expandedRows" @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppTextField v-model="search" label="Search products" placeholder="Search by name..."
                prepend-inner-icon="mdi-magnify" hide-details clearable style="min-width: 260px"
                @click:clear="onClearSearch" />
              <v-btn color="primary" variant="tonal" height="40" @click="onSearch">
                <v-icon start>mdi-magnify</v-icon>
                Search
              </v-btn>
            </div>
          </v-col>

          <v-col cols="12" md="6" lg="3">
            <AppSelectField v-model="categoryFilter" :items="categoryOptions" item-title="title" item-value="value"
              label="Category" clearable hide-details @update:model-value="onCategoryChange" />
          </v-col>

          <v-spacer></v-spacer>

          <v-col cols="12" md="auto" class="text-right">
            <div class="text-medium-emphasis">
              <span class="text-primary" style="font-size: smaller;">Total: {{ total }} Items found.</span>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </template>
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
    <template #item.images_count="{ item }">
      <div class="d-flex align-center ga-2">
        <v-btn icon size="x-small" variant="tonal" :color="isExpanded(item.id) ? 'primary' : 'default'"
          @click.stop="toggleExpand(item.id)">
          <v-icon size="16">{{ isExpanded(item.id) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
        </v-btn>
        <span>{{ Number(item.images_count ?? 0) === 0 ? '-' : `${Number(item.images_count ?? 0)} images` }}</span>
      </div>
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
        <v-btn size="small" variant="flat" class="mr-2" color="primary" @click="onView(item)">
         Details
        </v-btn>
        <v-btn size="small" variant="flat" color="error" @click="onDelete(item)">
          Delete
        </v-btn>
      </div>
    </template>
    <template #expanded-row="{ item, columns }">
      <tr>
        <td :colspan="columns.length">
          <v-container>
            <div class="pa-4">
              <div class="d-flex align-center ga-2 mb-3">
                <!-- <div class="text-subtitle-2">Images for {{ item.name }}</div> -->
                <v-chip size="small" color="primary" label>Total {{ item.images_count || 0 }} images</v-chip>
              </div>

              <div v-if="productImages[String(item.id)]?.loading" class="d-flex align-center ga-2 text-medium-emphasis">
                <v-progress-circular indeterminate size="20" width="2" color="primary" />
                <span>Loading images...</span>
              </div>
              <div v-else-if="productImages[String(item.id)]?.error" class="text-error">
                {{ productImages[String(item.id)]?.error }}
              </div>
              <div v-else>
                <div v-if="(productImages[String(item.id)]?.images?.length ?? 0) === 0" class="text-medium-emphasis">
                  No images found.
                </div>
                <v-table v-else density="comfortable" class="product-images-table">
                  <thead>
                    <tr>
                      <th style="width: 120px;">Preview</th>
                      <th>Info</th>
                      <th style="width: 120px;">Default</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="img in productImages[String(item.id)]?.images ?? []"
                      :key="String(img.id ?? Math.random())">
                      <td class="py-3">
                        <div class="table-image-preview rounded">
                          <v-img v-if="img.url" :src="String(img.url)" contain :alt="img.alt_text || 'Image'" />
                          <div v-else class="d-flex align-center justify-center h-100">
                            <v-icon size="18" color="grey-darken-1">mdi-image-outline</v-icon>
                          </div>
                        </div>
                      </td>
                      <td class="py-3">
                        <div class="text-body-2 font-weight-medium">{{ img.alt_text || 'Untitled' }}</div>
                        <div class="text-caption text-medium-emphasis">Alt: {{ img.alt_text || '-' }}</div>
                        <div class="text-caption text-medium-emphasis">Size: {{ img.size_info || '-' }}</div>
                        <div class="text-caption text-medium-emphasis">
                          URL:
                          <a v-if="img.url" :href="String(img.url)" target="_blank" rel="noopener" class="text-primary">
                            {{ String(img.url) }}
                          </a>
                          <span v-else>-</span>
                        </div>
                        <div v-if="img.id" class="text-caption text-medium-emphasis">File ID: {{ img.id }}</div>
                      </td>
                      <td class="py-3">
                        <v-chip size="small" label variant="tonal"
                          :color="img.meta?.is_default ? 'primary' : 'default'">
                          {{ img.meta?.is_default ? 'Yes' : 'No' }}
                        </v-chip>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </div>
            </div>
          </v-container>
        </td>
      </tr>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSelectField from '@/components/shared/AppSelectField.vue';
import { getProductDetail, listProducts, type ProductDetailResponse, type ProductListItem } from '@/api/products.api';
import { listProductCategoriesLite, type ProductCategoryListItem } from '@/api/product-categories.api';
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
  images_count: number;
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
  // { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Images', key: 'images_count', sortable: false, minWidth: '120' },
  // { title: 'Variants', key: 'variants_count', sortable: false, minWidth: '120' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'EMI', key: 'emi_enabled', sortable: false, minWidth: '140' },
  // { title: 'Created', key: 'created_at', sortable: false, minWidth: '170' },
  { title: 'Actions', key: 'action', sortable: false, width: '100' },
];

const items = ref<Product[]>([]);
const total = ref(0);
const loading = ref(false);
const expandedRows = ref<Array<string | number>>([]);
const productImages = ref<Record<string, { loading: boolean; error: string | null; images: ProductDetailResponse['images'] }>>({});
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const search = ref('');
const categoryFilter = ref<number | string | null>(null);
const categoryOptions = ref<Array<{ title: string; value: number | string | null }>>([]);
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

function openProductModal() {
  openModal(ProductCreateModal, {
    onSaved: (product: any) => {
      console.log({ product });
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
      search: search.value.trim() || undefined,
      category_id: categoryFilter.value || undefined,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((product: ProductListItem) => ({
      id: product.id,
      name: product.name ?? '-',
      slug: product.slug ?? '-',
      status: Boolean(product.status),
      emi_enabled: Boolean(product.emi_enabled),
      variants_count: Number(product.variants_count ?? 0),
      images_count: Number((product as any).images_count ?? (Array.isArray((product as any).images) ? (product as any).images.length : 0)),
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

async function fetchCategories() {
  const list = await listProductCategoriesLite();
  console.log({ list });
  categoryOptions.value = [
    { title: 'All categories', value: null },
    ...list.map((cat: ProductCategoryListItem) => ({
      title: cat.title + " " + ((Number(cat.products_count ?? 0) > 0) ? `(${cat.products_count})` : '') || '-',
      value: cat.id,
    })).sort((a, b) => a.title.localeCompare(b.title)),
  ];
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  if (!hasLoadedOnce.value) {
    hasLoadedOnce.value = true;
  }
  fetchProducts();
}

watch(expandedRows, (rows) => {
  rows.forEach((id) => {
    ensureProductImages(id);
  });
});

function isExpanded(id: string | number): boolean {
  return expandedRows.value.some((entry) => String(entry) === String(id));
}

function toggleExpand(id: string | number) {
  if (isExpanded(id)) {
    expandedRows.value = expandedRows.value.filter((entry) => String(entry) !== String(id));
  } else {
    expandedRows.value = [...expandedRows.value, id];
    ensureProductImages(id);
  }
}

watch(expandedRows, (rows) => {
  rows.forEach((id) => {
    ensureProductImages(id);
  });
});

async function ensureProductImages(productId: string | number) {
  const key = String(productId);
  const cache = productImages.value[key];
  if (cache && (cache.loading || cache.images)) return;

  productImages.value[key] = { loading: true, error: null, images: [] };
  try {
    const detail = await getProductDetail(productId);
    productImages.value[key] = {
      loading: false,
      error: null,
      images: Array.isArray(detail?.images) ? detail.images : [],
    };
  } catch (err: any) {
    productImages.value[key] = {
      loading: false,
      error: err?.message || 'Failed to load images',
      images: [],
    };
  }
}

function onSearch() {
  options.value.page = 1;
  fetchProducts();
}

function onCategoryChange() {
  options.value.page = 1;
  fetchProducts();
}

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchProducts();
}

onMounted(() => {
  fetchCategories();
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

.product-images-table .table-image-preview {
  width: 200px;
  height: 150px;
}
</style>
