<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12">
        <div class="py-4">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2">Current Product Attributes</div>
            <v-btn variant="tonal" color="primary" @click="onEditAttributes">
              <v-icon start size="16">mdi-square-edit-outline</v-icon>
              {{ attributeActionLabel }}
            </v-btn>
          </div>

          <v-table v-if="productAttributeEntries.length" density="comfortable" class="border rounded">
            <tbody>
              <tr v-for="entry in productAttributeEntries" :key="entry.key">
                <td style="width: 28%;">{{ entry.key }}</td>
                <td><span class="attribute-value-text">{{ entry.value || '-' }}</span></td>
              </tr>
            </tbody>
          </v-table>
          <div v-else class="text-body-2 text-medium-emphasis">
            No product attributes found.
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import ProductAttributeClassDetailModal from '@/components/product-attribute/ProductAttributeClassDetailModal.vue';
import type { ProductDetailResponse } from '@/api/products.api';
import { openModal } from '@/shared/modal';

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();
const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const productAttributePayload = computed<Record<string, unknown>>(() => {
  const attributes = props.item?.attributes;
  if (!attributes || typeof attributes !== 'object' || Array.isArray(attributes)) return {};
  return attributes as Record<string, unknown>;
});

const initialAttributeClassId = computed<string | null>(() => {
  const value = productAttributePayload.value.attribute_class_id;
  if (value === null || value === undefined) return null;
  const normalized = String(value).trim();
  return normalized.length ? normalized : null;
});

const productAttributeValues = computed<Record<string, string>>(() => {
  const raw = productAttributePayload.value.product_attributes;
  if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return {};

  const mapped: Record<string, string> = {};
  Object.entries(raw as Record<string, unknown>).forEach(([key, value]) => {
    const normalizedKey = String(key ?? '').trim();
    if (!normalizedKey) return;
    mapped[normalizedKey] = String(value ?? '').trim();
  });
  return mapped;
});

const productAttributeEntries = computed<Array<{ key: string; value: string }>>(() =>
  Object.entries(productAttributeValues.value).map(([key, value]) => ({ key, value })),
);
const attributeActionLabel = computed(() =>
  productAttributeEntries.value.length ? 'Edit Attributes' : 'Add Attributes',
);

function onEditAttributes() {
  openModal(
    ProductAttributeClassDetailModal,
    {
      productId: props.productId,
      initialClassId: initialAttributeClassId.value,
      productAttributes: { ...productAttributeValues.value },
    },
    {
      title: 'Edit Attributes',
      size: 'xl',
      onSaved: () => {
        emit('updated');
      },
    },
  );
}
</script>

<style scoped>
.attribute-value-text {
  font-size: 0.8rem;
  /* color: rgb(var(--v-theme-on-surface-variant)); */
}
</style>
