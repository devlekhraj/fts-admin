<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between">
      <div>
        <div class="text-subtitle-1">Gift Items</div>
        <div class="text-body-2 text-medium-emphasis">Select products to include as free gifts.</div>
      </div>

      <v-btn color="primary" variant="flat" @click="handleOpenPicker">
        <v-icon start>mdi-plus</v-icon>
        Add Gift Items
      </v-btn>
    </div>

    <v-divider class="my-4" />

    <v-data-table
      :headers="headers"
      :items="selectedGiftItems"
      :loading="loading"
      item-key="id"
      density="comfortable"
      class="elevation-0">
      <template #item.product="{ item }">
        <div class="d-flex align-center ga-3">
          <v-avatar size="44" rounded="lg">
            <v-img v-if="item.thumb" :src="String(item.thumb)" contain />
            <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
          </v-avatar>
          <div class="min-w-0">
            <div class="text-truncate">{{ item.name || '-' }}</div>
          </div>
        </div>
      </template>

      <template #item.price="{ item }">
        <div class="text-primary" style="width: max-content;">
          {{ item.price == null ? '-' : formatAmount(item.price) }}
        </div>
      </template>

      <template #item.status="{ item }">
        <v-chip size="small" label variant="tonal" :color="item.status ? 'primary' : 'warning'">
          {{ item.status ? 'Active' : 'Inactive' }}
        </v-chip>
      </template>

      <template #item.actions="{ item }">
        <v-btn size="small" color="error" variant="outlined" @click="removeGiftItem(item.id)">
         Delete
        </v-btn>
      </template>

      <template #no-data>
        <div class="py-8 text-center text-body-2 text-medium-emphasis">
          No gift items selected yet. Click “Add Gift Items” to pick products.
        </div>
      </template>
    </v-data-table>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { openModal } from '@/shared/modal';
import ProductFreeGiftPickerModal from '@/components/product/ProductFreeGiftPickerModal.vue';
import type { ProductDetailResponse } from '@/api/products.api';
import { getProductGifts, type ProductGiftItem } from '@/api/product-gifts.api';
import ProductGiftDeleteModal from '@/components/product/ProductGiftDeleteModal.vue';
import { formatAmount } from '@/shared/utils';

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const selectedGiftItems = ref<ProductGiftItem[]>([]);
const loading = ref(false);

const headers = [
  { title: 'Product', key: 'product', sortable: false },
  { title: 'Price', key: 'price', sortable: false, width: 140 },
  { title: 'Status', key: 'status', sortable: false, width: 120 },
  { title: 'Delete', key: 'actions', sortable: false, width: 90 },
];

async function fetchGiftItems() {
  if (!props.productId) return;
  loading.value = true;
  try {
    selectedGiftItems.value = await getProductGifts(props.productId);
  } finally {
    loading.value = false;
  }
}

function handleOpenPicker() {
  openModal(
    ProductFreeGiftPickerModal,
    {
      productId: props.productId,
      initialSelectedIds: selectedGiftItems.value.map((p) => p.id),
      initialSelectedItems: selectedGiftItems.value,
    },
    {
      size: 'xl',
      title: 'Add Gift Items',
      onSaved: (payload) => {
        selectedGiftItems.value = payload as ProductGiftItem[];
      },
    },
  );
}

async function removeGiftItem(id: ProductGiftItem['id']) {
  const gift = selectedGiftItems.value.find((p) => String(p.id) === String(id));
  if (!gift) return;

  openModal(
    ProductGiftDeleteModal,
    { productId: props.productId, gift },
    {
      size: 'sm',
      title: 'Confirm Gift Removal',
      onSaved: () => fetchGiftItems(),
    },
  );
}

watch(
  () => props.productId,
  () => fetchGiftItems(),
  { immediate: true },
);

onMounted(fetchGiftItems);
</script>

<style scoped>
.min-w-0 {
  min-width: 0;
}
</style>
