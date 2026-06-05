<template>
  <v-tooltip location="top" text="Click to edit price">
    <template #activator="{ props: tooltipProps }">
      <p class="price-cell" v-bind="tooltipProps" @click="onOpen">
        {{ formattedPrice }}
      </p>
    </template>
  </v-tooltip>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { getProductDetail, type ProductDetailResponse } from '@/api/products.api';
import { openModal } from '@/shared/modal';
import { formatNPRIfDecimal } from '@/shared/formatters';
import ProductPriceModal from '@/components/product/ProductPriceModal.vue';

const props = defineProps<{
  product: {
    id?: number | string | null;
    name?: string | null;
    price?: number | string | null;
  };
  onUpdated?: (() => void | Promise<void>) | null;
}>();

const formattedPrice = computed(() => formatNPRIfDecimal(props.product?.price));
const loading = ref(false);

async function onOpen() {
  const productId = String(props.product?.id ?? '').trim();
  if (!productId || loading.value) return;

  loading.value = true;

  try {
    const detail = await getProductDetail(productId);
    openModal(
      ProductPriceModal,
      { product: detail as ProductDetailResponse, productId },
      {
        title: 'Product Price',
        size: 'md',
        onSaved: async () => {
          await props.onUpdated?.();
        },
      },
    );
  } catch {
    openModal(
      ProductPriceModal,
      { product: props.product, productId },
      {
        title: 'Product Price',
        size: 'md',
        onSaved: async () => {
          await props.onUpdated?.();
        },
      },
    );
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.price-cell {
  display: inline-block;
  margin: 0;
  font-size: 0.82rem;
  color: rgb(var(--v-theme-primary));
  cursor: pointer;
  text-decoration: none;
  pointer-events: auto;
}

.price-cell:hover {
  text-decoration: underline;
}
</style>
