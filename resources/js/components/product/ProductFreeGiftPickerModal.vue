<template>
  <v-card-text class="pt-6">
    <v-row class="align-center">
      <v-col cols="12" md="8">
        <v-text-field v-model="search" label="Search products" density="compact" variant="outlined" clearable
          hide-details @keyup.enter="handleSearch" @click:clear="handleSearch" />
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn color="primary" variant="outlined" @click="handleSearch">
          <v-icon start>mdi-magnify</v-icon>
          Search
        </v-btn>
      </v-col>
    </v-row>

    <v-row class="mt-2">
      <v-col cols="12" md="6">
        <v-data-table-server :headers="productHeaders" :items="products" :items-length="pagination.total"
          :loading="loading" density="comfortable" v-model:page="pagination.page"
          v-model:items-per-page="pagination.perPage" :hide-default-footer="!showPagination" @update:page="fetchProducts"
          @update:items-per-page="handlePerPageChange">
          <template v-if="!showPagination" #bottom />
          <template #item.select="{ item }">
            <v-checkbox v-model="selectedIds" :value="item.id" color="primary" hide-details
              :disabled="isSameProduct(item.id)" />
          </template>

          <template #item.name="{ item }">
            <div class="d-flex align-center ga-3">
              <v-avatar size="44" rounded="lg" >
                <v-img v-if="item.thumb" :src="String(item.thumb)" cover />
                <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
              </v-avatar>
              <div class="min-w-0">
                <div class="text-truncate">{{ item.name || '-' }}</div>
              </div>
            </div>
          </template>



          <template #no-data>
            <div class="py-8 text-center text-body-2 text-medium-emphasis">
              No products found.
            </div>
          </template>
        </v-data-table-server>
      </v-col>

      <v-col cols="12" md="6">
        <!-- <div class="d-flex align-center justify-space-between mb-2">
          <div class="text-subtitle-2">Selected Items</div>
          <v-btn size="small" variant="text" color="error" :disabled="selectedItems.length === 0"
            @click="clearSelection">
            Clear
          </v-btn>
        </div> -->

        <v-data-table :headers="selectedHeaders" :items="selectedItems" item-key="id" density="comfortable"
        style="font-size: small;"
          class="elevation-0">
          <template #item.product="{ item }">
            <div class="d-flex align-center ga-3">
              <v-avatar size="36" rounded="lg" >
                <v-img v-if="item.thumb" :src="String(item.thumb)" cover />
                <v-icon v-else size="16" color="grey-darken-1">mdi-image-outline</v-icon>
              </v-avatar>
              <div class="min-w-0">
                <div class="text-truncate">{{ item.name || '-' }}</div>

              </div>
            </div>
          </template>

          <template #item.actions="{ item }">
            <v-btn icon size="x-small" color="error" variant="tonal" @click="removeSelected(item.id)">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </template>

          <template #no-data>
            <div class="py-6 text-center text-body-2 text-medium-emphasis">No items selected.</div>
          </template>
        </v-data-table>

        <div class="mt-4">
          <v-btn color="primary" block variant="flat" :disabled="saving || selectedItems.length === 0"
            @click="saveSelection">
            <template v-if="!saving" #prepend>
              <v-icon>mdi-check</v-icon>
            </template>
            <template v-else #prepend>
              <v-progress-circular indeterminate size="18" width="2" />
            </template>
            Add Selected
          </v-btn>
        </div>
      </v-col>
    </v-row>
  </v-card-text>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { listProducts, type ProductListItem } from '@/api/products.api';
import { storeProductGifts, type ProductGiftItem } from '@/api/product-gifts.api';

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload: ProductGiftItem[]): void;
}>();

const props = defineProps<{
  productId: string | number;
  initialSelectedIds?: Array<string | number>;
  initialSelectedItems?: ProductGiftItem[];
}>();

const loading = ref(false);
const saving = ref(false);
const search = ref('');
const products = ref<ProductListItem[]>([]);

const pagination = reactive({
  page: 1,
  perPage: 20,
  total: 0,
});

const selectedIds = ref<Array<string | number>>([...(props.initialSelectedIds ?? [])]);
const selectedById = ref<Record<string, ProductListItem>>({});

const showPagination = computed(() => pagination.total > pagination.perPage);

function keyFor(id: string | number) {
  return String(id);
}

function isSameProduct(id: string | number) {
  return keyFor(id) === keyFor(props.productId);
}

function seedInitialSelectedItems() {
  const seeded: Record<string, ProductListItem> = {};
  for (const item of props.initialSelectedItems ?? []) {
    seeded[keyFor(item.id)] = {
      id: item.id,
      name: item.name ?? null,
      slug: item.slug ?? null,
      status: Boolean(item.status),
      emi_enabled: false,
      thumb: item.thumb ?? null,
      price: item.price ?? null,
      original_price: null,
    };
  }
  selectedById.value = { ...seeded, ...selectedById.value };
}

async function fetchProducts() {
  loading.value = true;
  try {
    const resp = await listProducts({
      page: pagination.page,
      per_page: pagination.perPage,
      search: search.value || '',
    });

    const list = Array.isArray(resp.data) ? resp.data : [];
    products.value = list.filter((p) => !isSameProduct(p.id));
    pagination.total = resp.meta?.total ?? resp.total ?? 0;

    // Update selectedById with any selected items found in the current page
    const nextSelectedById = { ...selectedById.value };
    for (const item of products.value) {
      const idKey = keyFor(item.id);
      if (selectedIds.value.some((id) => keyFor(id) === idKey)) {
        nextSelectedById[idKey] = item;
      }
    }
    selectedById.value = nextSelectedById;
  } finally {
    loading.value = false;
  }
}

function handleSearch() {
  pagination.page = 1;
  fetchProducts();
}

function handlePerPageChange() {
  pagination.page = 1;
  fetchProducts();
}

const selectedItems = computed(() => {
  const idSet = new Set(selectedIds.value.map((id) => keyFor(id)));
  return Object.values(selectedById.value).filter((item) => idSet.has(keyFor(item.id)));
});

watch(
  () => selectedIds.value,
  (next) => {
    const idSet = new Set(next.map((id) => keyFor(id)));
    const nextSelectedById = { ...selectedById.value };

    // Ensure we keep entries for currently selected ids only
    for (const existingKey of Object.keys(nextSelectedById)) {
      if (!idSet.has(existingKey)) delete nextSelectedById[existingKey];
    }

    // Add newly selected ids from current page if available
    for (const item of products.value) {
      const idKey = keyFor(item.id);
      if (idSet.has(idKey)) nextSelectedById[idKey] = item;
    }

    selectedById.value = nextSelectedById;
  },
  { deep: true },
);

function removeSelected(id: string | number) {
  selectedIds.value = selectedIds.value.filter((x) => keyFor(x) !== keyFor(id));
}

function clearSelection() {
  selectedIds.value = [];
  selectedById.value = {};
}

async function saveSelection() {
  if (saving.value) return;
  saving.value = true;
  try {
    const giftIds = selectedItems.value.map((p) => p.id);
    const saved = await storeProductGifts(props.productId, giftIds);
    emit('saved', saved);
    emit('close');
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  seedInitialSelectedItems();
  fetchProducts();
});

const productHeaders = [
  { title: '', key: 'select', sortable: false, width: 48 },
  { title: 'Product', key: 'name', sortable: false },
];

const selectedHeaders = [
  { title: 'Selected Products', key: 'product', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, width: 80 },
];
</script>

<style scoped>
.min-w-0 {
  min-width: 0;
}
</style>
