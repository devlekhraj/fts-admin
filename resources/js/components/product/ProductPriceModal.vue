<template>
  <v-card-text class="py-6 price-modal">
    <v-alert v-if="validationMessage" type="error" variant="tonal" class="mb-6">
      {{ validationMessage }}
    </v-alert>

    <div class="d-flex align-center justify-space-between mb-6">
      <div class="d-flex align-center ga-3">
        <v-avatar size="48" rounded="lg" class="product-header-avatar">
          <v-img
            :src="productImage"
            :alt="productName"
            cover />
        </v-avatar>
        <div>
          <div class="text-subtitle-1 mb-1">
            {{ productName }}
          </div>
          <div class="text-caption text-medium-emphasis">
            Price details and variants
          </div>
        </div>
      </div>

      <div class="d-flex align-center ga-2">
        <v-btn v-if="!editing" color="primary" variant="flat" prepend-icon="mdi-pencil" @click="startEditing">
          Edit
        </v-btn>
        <v-btn v-else color="error" variant="outlined" prepend-icon="mdi-close" :disabled="saving" @click="cancelEditing">
          Cancel Edit
        </v-btn>
      </div>
    </div>

    <div class="mb-2 text-subtitle-2">
      Product Price
    </div>

    <v-table density="compact" class="product-price-table mb-10">
      <thead>
        <tr>
          <th style="width: 240px;">Field</th>
          <th>Value</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Original Price</td>
          <td>
            <template v-if="editing">
              <v-text-field v-model="originalPriceDraft" type="number" min="0" class="py-2" density="compact"
                variant="outlined" hide-details="auto" :error-messages="getFieldErrors('original_price')" />
            </template>
            <div v-else class="original-price">
              {{ formattedOriginalPrice }}
            </div>
          </td>
        </tr>
        <tr>
          <td>Price</td>
          <td>
            <template v-if="editing">
              <v-text-field v-model="currentPriceDraft" type="number" min="0" class="py-2" density="compact"
                variant="outlined" hide-details="auto" :rules="[requiredNumberRule]"
                :error-messages="getFieldErrors('price')" />
            </template>
            <div v-else class="text-primary">
              {{ formattedCurrentPrice }}
            </div>
          </td>
        </tr>
        <tr>
          <td>Quantity</td>
          <td>
            <template v-if="editing">
              <v-text-field v-model="quantityDraft" type="number"  class="py-2" min="0" density="compact" variant="outlined"
                hide-details="auto" :rules="[requiredIntegerRule]" :error-messages="getFieldErrors('quantity')" />
            </template>
            <div v-else>
              {{ quantityDisplay }}
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div class="mb-2 text-subtitle-2">
      Pre Order
    </div>

    <v-table density="compact" class="product-price-table mb-10">
      <thead>
        <tr>
          <th style="width: 240px;">Field</th>
          <th>Value</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Pre Order Available</td>
          <td>
            <template v-if="editing">
              <v-select v-model="preOrderDraft" :items="preOrderOptions" item-title="label" class="py-2"
                item-value="value" density="compact" variant="outlined" hide-details="auto"
                :error-messages="getFieldErrors('pre_order')" />
            </template>
            <div v-else>
              {{ preOrderDraft === '1' ? 'Yes' : 'No' }}
            </div>
          </td>
        </tr>
        <tr>
          <td>Pre Order Price</td>
          <td>
            <template v-if="editing">
              <v-text-field v-model="preOrderPriceDraft" type="number"  class="py-2" min="0" density="compact" variant="outlined"
                hide-details="auto" :rules="[preOrderPriceRule]"
                :error-messages="getFieldErrors('pre_order_price')" />
            </template>
            <div v-else>
              {{ formattedPreOrderPrice }}
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div class="d-flex align-center justify-space-between mb-2">
      <div class="text-subtitle-2">
        Variants
      </div>
      <v-btn
        v-if="editing && variantDrafts.length > 0"
        variant="flat"
        size="small"
        color="primary"
        class="px-3"
        @click="syncProductPriceToVariants">
        Sync with Price - {{ formatNPRIfDecimal(currentPriceDraft) }}
      </v-btn>
    </div>

    <v-table density="compact" class="variant-table">
      <thead>
        <tr>
          <th style="width: 72px;">#</th>
          <th>Attributes</th>
          <th style="width: 180px;">Price</th>
          <th style="width: 140px;">Quantity</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="variantDrafts.length === 0">
          <td colspan="4" class="text-medium-emphasis text-center py-4">
            No variants found.
          </td>
        </tr>
        <tr v-for="(variant, index) in variantDrafts" :key="String(variant.id ?? Math.random())">
          <td class="py-3">
            {{ index + 1 }}
          </td>
          <td class="py-3">
            <div class="d-flex flex-column ga-1">
              <div v-for="(value, key) in variant.attributes || {}" :key="`${String(variant.id)}-${String(key)}`"
                class="text-body-2">
                <span>{{ key }}:</span>
                <span class="ml-1">{{ value }}</span>
              </div>
              <span v-if="!variant.attributes || Object.keys(variant.attributes).length === 0"
                class="text-medium-emphasis">
                -
              </span>
            </div>
          </td>
          <td class="py-3">
            <template v-if="editing">
              <v-text-field v-model="variant.price" type="number"  class="py-2" min="0" density="compact" variant="outlined"
                hide-details="auto" :rules="[requiredNumberRule]"
                :error-messages="getVariantFieldErrors(index, 'price')" />
            </template>
            <template v-else>
              {{ formatNPRIfDecimal(variant.price) }}
            </template>
          </td>
          <td class="py-3">
            <template v-if="editing">
              <v-text-field v-model="variant.quantity" type="number"  class="py-2" min="0" density="compact" variant="outlined"
                hide-details="auto" :rules="[requiredIntegerRule]"
                :error-messages="getVariantFieldErrors(index, 'quantity')" />
            </template>
            <template v-else>
              {{ variant.quantity === '' ? '-' : variant.quantity }}
            </template>
          </td>
        </tr>
      </tbody>
    </v-table>
  </v-card-text>
  <v-divider></v-divider>
  <v-card-actions class="pb-4 justify-space-between px-4 pt-3">
    <v-btn v-if="editing" variant="tonal" prepend-icon="mdi-arrow-left" :disabled="saving" @click="cancelEditing">
      Back
    </v-btn>
    <v-spacer v-else />

    <div class="d-flex align-center ga-2">
      <v-btn v-if="editing" class="px-4" color="primary" variant="flat" :disabled="saving" @click="onUpdate">
        <template v-if="saving">
          <v-progress-circular indeterminate size="16" width="2" class="me-2" />
        </template>
        <v-icon v-else start>mdi-content-save-edit-outline</v-icon>
        Save Changes
      </v-btn>
      <v-btn v-else color="primary" variant="tonal" @click="emit('close')">
        Close
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import type { ProductDetailResponse } from '@/api/products.api';
import { getProductDetail, updateProductPrice } from '@/api/products.api';
import { formatNPRIfDecimal } from '@/shared/formatters';
import { useSnackbarStore } from '@/stores/snackbar.store';

type ProductPriceLike = ProductDetailResponse & {
  id?: number | string;
  name?: string | null;
  price?: {
    current_price?: number | null;
    compare_price?: number | null;
    quantity?: number | null;
  } | number | string | null;
  pre_order?: {
    availability?: boolean;
    price?: number | null;
  } | null;
};

type VariantDraft = {
  id: number | string;
  price: string;
  quantity: string;
  attributes: Record<string, unknown> | null;
};

const props = defineProps<{
  product: ProductPriceLike;
  productId?: number | string | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved'): void;
}>();

const snackbar = useSnackbarStore();
const editing = ref(false);
const saving = ref(false);
const validationMessage = ref('');
const validationErrors = ref<Record<string, string[]>>({});
const currentPriceDraft = ref('');
const originalPriceDraft = ref('');
const quantityDraft = ref('');
const preOrderDraft = ref('0');
const preOrderPriceDraft = ref('');
const variantDrafts = ref<VariantDraft[]>([]);
const preOrderOptions = [
  { label: 'Yes', value: '1' },
  { label: 'No', value: '0' },
];
const localProduct = ref<ProductPriceLike>(props.product);

const productData = computed(() => localProduct.value);

function normalizeInput(value: unknown): string {
  if (value === null || value === undefined) return '';
  return String(value);
}

function syncDrafts() {
  const price = productData.value?.price;
  currentPriceDraft.value = normalizeInput(
    typeof price === 'object' && price !== null ? price.current_price : price,
  );
  originalPriceDraft.value = normalizeInput(
    typeof price === 'object' && price !== null ? price.compare_price : null,
  );
  quantityDraft.value = normalizeInput(
    typeof price === 'object' && price !== null ? price.quantity : null,
  );
  preOrderDraft.value = productData.value?.pre_order?.availability ? '1' : '0';
  preOrderPriceDraft.value = normalizeInput(productData.value?.pre_order?.price ?? null);
  variantDrafts.value = (productData.value?.variants ?? []).map((variant) => ({
    id: variant.id,
    price: normalizeInput(variant.price),
    quantity: normalizeInput(variant.quantity),
    attributes: variant.attributes ?? {},
  }));
}

watch(
  () => props.product,
  () => {
    localProduct.value = props.product;
    syncDrafts();
    editing.value = false;
  },
  { immediate: true, deep: true },
);

const productName = computed(() => productData.value?.overview?.name || productData.value?.name || 'Product price');
const productImage = computed(() => {
  const thumb = productData.value?.overview?.thumb || productData.value?.thumb || '';
  return typeof thumb === 'string' && thumb.trim()
    ? thumb
    : 'https://placehold.co/96x96?text=Product';
});
const formattedCurrentPrice = computed(() => {
  const price = productData.value?.price;
  const priceObject = price && typeof price === 'object' ? price : null;
  return formatNPRIfDecimal(priceObject?.current_price ?? price ?? null);
});
const formattedOriginalPrice = computed(() => {
  const price = productData.value?.price;
  const priceObject = price && typeof price === 'object' ? price : null;
  return formatNPRIfDecimal(priceObject?.compare_price ?? null);
});
const quantityDisplay = computed(() => {
  const price = productData.value?.price;
  const priceObject = price && typeof price === 'object' ? price : null;
  const quantity = priceObject?.quantity ?? null;
  return quantity === null || quantity === undefined ? '-' : String(quantity);
});
const formattedPreOrderPrice = computed(() => formatNPRIfDecimal(productData.value?.pre_order?.price ?? null));

function toNullableNumber(value: string): number | null {
  const trimmed = String(value ?? '').trim();
  if (!trimmed) return null;
  const parsed = Number(trimmed);
  return Number.isFinite(parsed) ? parsed : null;
}

function toNullableInteger(value: string): number | null {
  const trimmed = String(value ?? '').trim();
  if (!trimmed) return null;
  const parsed = Number(trimmed);
  return Number.isInteger(parsed) ? parsed : null;
}

function requiredNumberRule(value: unknown) {
  const raw = String(value ?? '').trim();
  return raw !== '' && Number.isFinite(Number(raw)) ? true : 'Required';
}

function requiredIntegerRule(value: unknown) {
  const raw = String(value ?? '').trim();
  return raw !== '' && Number.isInteger(Number(raw)) ? true : 'Required';
}

function preOrderPriceRule(value: unknown) {
  if (preOrderDraft.value === '1') {
    return requiredNumberRule(value);
  }

  const raw = String(value ?? '').trim();
  if (!raw) return true;
  return Number.isFinite(Number(raw)) ? true : 'Invalid value';
}

function getFieldErrors(field: string): string[] {
  return validationErrors.value[field] ?? [];
}

function getVariantFieldErrors(index: number, field: string): string[] {
  const dotKey = `variants.${index}.${field}`;
  const bracketKey = `variants[${index}].${field}`;
  return validationErrors.value[dotKey] ?? validationErrors.value[bracketKey] ?? [];
}

function setValidationErrors(errors: Record<string, unknown> | null | undefined) {
  validationErrors.value = {};
  validationMessage.value = '';

  if (!errors || typeof errors !== 'object') return;

  const normalized: Record<string, string[]> = {};
  for (const [key, value] of Object.entries(errors)) {
    if (Array.isArray(value)) {
      normalized[key.trim()] = value.map((item) => String(item));
    } else if (value !== null && value !== undefined) {
      normalized[key.trim()] = [String(value)];
    }
  }

  validationErrors.value = normalized;
  const firstError = Object.values(normalized).flat()[0];
  validationMessage.value = firstError || '';
}

function startEditing() {
  syncDrafts();
  editing.value = true;
}

function cancelEditing() {
  syncDrafts();
  editing.value = false;
}

function syncProductPriceToVariants() {
  const price = normalizeInput(currentPriceDraft.value);
  variantDrafts.value = variantDrafts.value.map((variant) => ({
    ...variant,
    price,
  }));
}

async function onUpdate() {
  const productId = String(props.productId ?? productData.value?.id ?? '').trim();
  if (!productId || saving.value) return;

  saving.value = true;
  validationMessage.value = '';
  validationErrors.value = {};

  try {
    await updateProductPrice(productId, {
      price: toNullableNumber(currentPriceDraft.value),
      original_price: toNullableNumber(originalPriceDraft.value),
      quantity: toNullableInteger(quantityDraft.value),
      pre_order: preOrderDraft.value === '1',
      pre_order_price: toNullableNumber(preOrderPriceDraft.value),
      variants: variantDrafts.value.map((variant) => ({
        id: variant.id,
        price: toNullableNumber(variant.price),
        quantity: toNullableInteger(variant.quantity),
      })),
    });

    snackbar.show({ message: 'Product price details updated successfully.', color: 'success' });
    const refreshed = await getProductDetail(productId);
    localProduct.value = refreshed as ProductPriceLike;
    syncDrafts();
    editing.value = false;
    emit('saved');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update product price details.',
      color: 'error',
    });
    if (error.response?.status === 422) {
      setValidationErrors(error.response?.data?.errors ?? null);
    }
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.price-metric {
  min-height: 84px;
  padding: 16px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  background: rgba(var(--v-theme-surface), 0.8);
}

.v-table,
.price-modal {
  font-size: 0.82rem !important;
}

.product-header-avatar {
  background: rgba(var(--v-theme-on-surface), 0.04);
  overflow: hidden;
}

.variant-table {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  overflow: hidden;
}

.product-price-table {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  overflow: hidden;
}

.original-price {
  text-decoration: line-through;
  color: rgba(var(--v-theme-on-surface), 0.55);
}
</style>
