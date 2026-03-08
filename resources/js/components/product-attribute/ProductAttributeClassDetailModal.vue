<template>
  <v-card-text class="pt-0">
    <div class="py-2">
      <div class="attribute-select-sticky pt-4 pb-1">
        <v-select
          style="max-width: 400px;"
          v-model="selectedAttributeId"
          label="Attribute Group"
          :items="attributeOptions"
          item-title="label"
          item-value="value"
          :loading="listLoading"
          :disabled="listLoading"
          hide-details
          :error-messages="listErrorMessage ? [listErrorMessage] : []"
          variant="outlined"
          density="comfortable"
          clearable />
      </div>
      <div v-if="selectedAttributeId" class="mt-4">
        <div v-if="detailLoading" class="text-body-2 text-medium-emphasis">
          Loading related attributes...
        </div>

        <div v-else-if="detailErrorMessage" class="text-body-2 text-error">
          {{ detailErrorMessage }}
        </div>

        <v-table v-else-if="attributeRows.length" density="comfortable" class="border-0">
          <thead>
            <tr>
              <th style="min-width: 220px;">Attribute Name</th>
              <!-- <th style="min-width: 120px;">Type</th> -->
              <!-- <th style="min-width: 140px;">For Variant</th>
              <th style="min-width: 130px;">In Filter</th> -->
              <th style="min-width: 250px;">Values</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in attributeRows" :key="String(row.id)">
              <td>{{ row.name || '-' }}</td>
              <!-- <td>{{ row.type || '-' }}</td> -->
              <!-- <td>
                <v-chip size="small" label variant="tonal" :color="row.use_for_variant ? 'success' : 'error'">
                  {{ row.use_for_variant ? 'Yes' : 'No' }}
                </v-chip>
              </td>
              <td>
                <v-chip size="small" label variant="tonal" :color="row.use_in_filter ? 'success' : 'error'">
                  {{ row.use_in_filter ? 'Yes' : 'No' }}
                </v-chip>
              </td> -->
              <td>
                <div class="py-4">
                  <v-select
                    v-if="isOptionType(row.type)"
                    :model-value="getOptionValue(row)"
                    @update:model-value="setOptionValue(row, $event)"
                    :items="getOptionItems(row)"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                    clearable />
                  <v-textarea
                    v-else
                    :model-value="getTextValue(row)"
                    @update:model-value="setTextValue(row, $event)"
                    variant="outlined"
                    density="comfortable"
                    auto-grow
                    rows="2"
                    hide-details
                    placeholder="Enter value" />
                </div>
              </td>
            </tr>
          </tbody>
        </v-table>

        <div v-else class="text-body-2 text-medium-emphasis">
          No attributes found for this group.
        </div>
      </div>
    </div>
  </v-card-text>
  <v-divider />
  <v-card-actions v-if="attributeRows.length" class="justify-end pa-4">
    <div>
      <v-btn color="primary" variant="flat" @click="onUpdate" class="px-4" :loading="saving" :disabled="saving">
        <v-icon start size="16">mdi-content-save-outline</v-icon>
        Update
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import {
  getAttributeProductDetail,
  listAttributeProducts,
  type AttributeDetailItem,
  type AttributeProductDetailResponse,
} from '@/api/attribute-products.api';
import { update as updateProduct } from '@/api/products.api';
import { closeModal } from '@/shared/modal';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  productId: string | number;
  initialClassId?: string | number | null;
  productAttributes?: Record<string, string>;
}>();
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();
const snackbar = useSnackbarStore();

type SelectOption = {
  label: string;
  value: string;
};

const selectedAttributeId = ref<string | null>(null);
const attributeOptions = ref<SelectOption[]>([]);

const listLoading = ref(false);
const detailLoading = ref(false);
const listErrorMessage = ref('');
const detailErrorMessage = ref('');
const selectedAttributeDetail = ref<AttributeProductDetailResponse | null>(null);
const optionValueByRow = ref<Record<string, string | null>>({});
const textValueByRow = ref<Record<string, string>>({});
const saving = ref(false);

const attributeRows = computed<AttributeDetailItem[]>(() =>
  Array.isArray(selectedAttributeDetail.value?.attributes) ? selectedAttributeDetail.value.attributes : [],
);
const prefillValues = computed<Record<string, string>>(() => props.productAttributes ?? {});

async function fetchAttributeOptions() {
  listLoading.value = true;
  listErrorMessage.value = '';

  try {
    const response = await listAttributeProducts({});
    const rows = Array.isArray(response?.data) ? response.data : [];

    attributeOptions.value = rows
      .map((item) => ({
        label: String(item?.name ?? `Attribute #${item?.id ?? '-'}`),
        value: String(item?.id ?? ''),
      }))
      .filter((item) => item.value.length > 0);
  } catch (error: any) {
    listErrorMessage.value = error?.response?.data?.message || error?.message || 'Failed to load attributes.';
    attributeOptions.value = [];
  } finally {
    listLoading.value = false;
  }
}

async function fetchAttributeDetail(id: string) {
  detailLoading.value = true;
  detailErrorMessage.value = '';

  try {
    selectedAttributeDetail.value = await getAttributeProductDetail(id);
    prefillRowInputs(attributeRows.value);
  } catch (error: any) {
    detailErrorMessage.value =
      error?.response?.data?.message || error?.message || 'Failed to load related attributes.';
    selectedAttributeDetail.value = null;
  } finally {
    detailLoading.value = false;
  }
}

function isOptionType(type: string | null | undefined): boolean {
  return String(type ?? '').trim().toLowerCase() === 'option';
}

function normalizeValues(values: unknown): string[] {
  if (!Array.isArray(values)) return [];
  return values
    .map((value) => String(value ?? '').trim())
    .filter((value) => value.length > 0);
}

function getOptionItems(row: AttributeDetailItem): string[] {
  const rowId = String(row.id);
  const baseItems = normalizeValues(row.values);
  const selectedValue = String(optionValueByRow.value[rowId] ?? '').trim();
  if (!selectedValue || baseItems.includes(selectedValue)) return baseItems;
  return [selectedValue, ...baseItems];
}

function getOptionValue(row: AttributeDetailItem): string | null {
  const id = String(row.id);
  if (optionValueByRow.value[id] !== undefined) {
    return optionValueByRow.value[id];
  }
  const [first] = normalizeValues(row.values);
  return first ?? null;
}

function setOptionValue(row: AttributeDetailItem, value: unknown) {
  const id = String(row.id);
  const nextValue = String(value ?? '').trim();
  optionValueByRow.value[id] = nextValue.length ? nextValue : null;
}

function getTextValue(row: AttributeDetailItem): string {
  const id = String(row.id);
  if (textValueByRow.value[id] !== undefined) {
    return textValueByRow.value[id];
  }
  return normalizeValues(row.values).join(', ');
}

function setTextValue(row: AttributeDetailItem, value: unknown) {
  const id = String(row.id);
  textValueByRow.value[id] = String(value ?? '');
}

function prefillRowInputs(rows: AttributeDetailItem[]) {
  rows.forEach((row) => {
    const rowId = String(row.id);
    const rowName = String(row.name ?? '').trim();
    const prefillValue = rowName ? String(prefillValues.value[rowName] ?? '').trim() : '';

    if (isOptionType(row.type)) {
      const options = normalizeValues(row.values);
      if (prefillValue.length) {
        optionValueByRow.value[rowId] = prefillValue;
      } else {
        optionValueByRow.value[rowId] = options[0] ?? null;
      }
      return;
    }

    if (prefillValue.length) {
      textValueByRow.value[rowId] = prefillValue;
    } else {
      textValueByRow.value[rowId] = normalizeValues(row.values).join(', ');
    }
  });
}

function getCurrentRowValue(row: AttributeDetailItem): string {
  if (isOptionType(row.type)) {
    return String(getOptionValue(row) ?? '').trim();
  }
  return String(getTextValue(row) ?? '').trim();
}

async function onUpdate() {
  const productId = String(props.productId ?? '').trim();
  const attributeClassId = String(selectedAttributeId.value ?? '').trim();
  if (!productId || !attributeClassId) return;

  const payload = attributeRows.value.reduce<Record<string, string>>((acc, row) => {
    const key = String(row.name ?? '').trim();
    const value = getCurrentRowValue(row);
    if (!key || !value) return acc;
    acc[key] = value;
    return acc;
  }, {});

  saving.value = true;
  try {
    await updateProduct(productId, {
      attributes: {
        attribute_class_id: attributeClassId,
        product_attributes: payload,
      },
    });
    emit('saved', { attribute_class_id: attributeClassId, product_attributes: payload });
    snackbar.show({ message: 'Product attributes updated successfully.', color: 'success' });
    closeModal();
  } catch (error: any) {
    const message = error?.response?.data?.message || error?.message || 'Failed to update product attributes.';
    snackbar.show({ message, color: 'error' });
  } finally {
    saving.value = false;
  }
}

watch(selectedAttributeId, async (id) => {
  optionValueByRow.value = {};
  textValueByRow.value = {};
  if (!id) {
    selectedAttributeDetail.value = null;
    detailErrorMessage.value = '';
    return;
  }
  await fetchAttributeDetail(id);
});

watch(
  () => props.initialClassId,
  (value) => {
    const id = String(value ?? '').trim();
    selectedAttributeId.value = id.length ? id : null;
  },
  { immediate: true },
);

onMounted(fetchAttributeOptions);
</script>

<style scoped>
.attribute-select-sticky {
  position: sticky;
  top: 0;
  z-index: 2;
  background: rgb(var(--v-theme-surface));
}
</style>
