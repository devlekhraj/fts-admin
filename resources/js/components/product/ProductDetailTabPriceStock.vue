<template>
  <div class="pa-6">
    <v-container>
      <v-row>
        <v-col cols="12" md="6" offset-md="3">
          <div>

            <div>
              <div class="text-caption text-medium-emphasis mb-1">Amount</div>
              <v-text-field v-model="amount" variant="outlined" density="comfortable" type="number" min="0" />

            </div>
            <div>
              <div class="text-caption text-medium-emphasis mb-1">Compare Price</div>
              <v-text-field v-model="comparePrice" variant="outlined" density="comfortable" type="number" min="0" />
            </div>

            <div>
              <div class="text-caption text-medium-emphasis mb-1">Pre-order Price</div>
              <v-text-field v-model="preOrderPrice" variant="outlined" density="comfortable" type="number" min="0" />
            </div>

            <div>
              <div class="text-caption text-medium-emphasis mb-1">Quantity</div>
              <v-text-field v-model="quantity" variant="outlined" density="comfortable" type="number" min="0" />
            </div>
            <div class="text-center py-4">
            <v-btn variant="flat" size="large" color="primary"><v-icon>mdi-content-save-edit-outline</v-icon> Update</v-btn>
            </div>

          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { ProductDetailResponse } from '@/api/products.api';

const props = defineProps<{
  item: ProductDetailResponse | null;
}>();

const amount = ref<string>('');
const comparePrice = ref<string>('');
const preOrderPrice = ref<string>('');
const quantity = ref<string>('');

watch(
  () => props.item,
  (item) => {
    amount.value = item?.price !== undefined && item?.price !== null ? String(item.price) : '';
    comparePrice.value =
      item?.compare_price !== undefined && item?.compare_price !== null ? String(item.compare_price) : '';
    preOrderPrice.value =
      item?.pre_order_price !== undefined && item?.pre_order_price !== null ? String(item.pre_order_price) : '';
    quantity.value = item?.quantity !== undefined && item?.quantity !== null ? String(item.quantity) : '';
  },
  { immediate: true },
);
</script>
