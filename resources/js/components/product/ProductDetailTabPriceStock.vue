<template>
  <div class="pa-6">
    <v-container>
      <v-row>
        <v-col cols="12" md="6" offset-md="3">
          <div>
            <div class="mb-10">
              <div class="text-subtitle-2 font-weight-medium mt-3 mb-1">Price Settings</div>
              <v-row>
                <v-col cols="12" md="6">
                  <app-field-label label="Price" />
                  <v-text-field hide-details="auto" v-model="price" variant="outlined" density="comfortable"
                    type="number" min="0" />
                </v-col>
                <v-col cols="12" md="6">
                  <app-field-label label="Compare Price" />
                  <v-text-field hide-details="auto" v-model="comparePrice" variant="outlined" density="comfortable"
                    type="number" min="0" />
                </v-col>
              </v-row>

              <v-row>
                <v-col cols="6">
                  <app-field-label label="Available Quantity" />
                  <v-text-field hide-details="auto" v-model="quantity" variant="outlined" density="comfortable"
                    type="number" min="0" />
                </v-col>
              </v-row>
            </div>

            <div>
              <div class="text-subtitle-2 font-weight-medium mt-3 mb-1">Pre Order Settings</div>
              <v-row>
                <v-col cols="12" md="6">
                  <app-field-label label="Pre Order Available" />
                  <v-select hide-details="auto" v-model="preOrder" :items="preOrderOptions" item-title="label"
                    item-value="value" variant="outlined" density="comfortable" />
                </v-col>
                <v-col cols="12" md="6">
                  <app-field-label label="Pre Order Price (If Available)" />
                  <v-text-field v-model="preOrderPrice" hide-details="auto" variant="outlined" density="comfortable"
                    type="number" min="0" />
                </v-col>
              </v-row>
            </div>
            <div class="text-center py-10">
              <v-btn variant="flat" size="large" color="primary" :loading="saving" @click="onUpdate">
                <v-icon start>mdi-content-save-edit-outline</v-icon>
                Update
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const price = ref<string>('');
const comparePrice = ref<string>('');
const quantity = ref<string>('');
const preOrder = ref<string>('0');
const preOrderPrice = ref<string>('');
const saving = ref(false);
const preOrderOptions = [
  { label: 'Yes', value: '1' },
  { label: 'No', value: '0' },
];
const snackbar = useSnackbarStore();

watch(
  () => props.item,
  (item) => {
    price.value =
      item?.price?.current_price !== undefined && item?.price?.current_price !== null
        ? String(item.price.current_price)
        : '';
    comparePrice.value =
      item?.price?.compare_price !== undefined && item?.price?.compare_price !== null
        ? String(item.price.compare_price)
        : '';
    quantity.value =
      item?.price?.quantity !== undefined && item?.price?.quantity !== null ? String(item.price.quantity) : '';
    preOrder.value = item?.pre_order?.availability ? '1' : '0';
    preOrderPrice.value =
      item?.pre_order?.price !== undefined && item?.pre_order?.price !== null ? String(item.pre_order.price) : '';
  },
  { immediate: true },
);

function toNullableNumber(value: string): number | null {
  const trimmed = String(value ?? '').trim();
  if (!trimmed) return null;
  const parsed = Number(trimmed);
  return Number.isFinite(parsed) ? parsed : null;
}

async function onUpdate() {
  const id = String(props.productId ?? props.item?.id ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateProduct(id, {
      price: toNullableNumber(price.value),
      original_price: toNullableNumber(comparePrice.value),
      quantity: toNullableNumber(quantity.value),
      pre_order: Number(preOrder.value) === 1,
      pre_order_price: toNullableNumber(preOrderPrice.value),
    });
    snackbar.show({ message: 'Product price and stock updated successfully.', color: 'success' });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
