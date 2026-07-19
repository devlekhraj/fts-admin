<template>
  <v-card-text class="pt-6">
    <v-row class="align-center">
      <v-col cols="12" md="8">
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Search categories"
          density="compact"
          variant="outlined"
          clearable
          hide-details
          @click:clear="search = ''"
        />
      </v-col>
      <v-col cols="12" md="4" class="d-flex justify-md-end">
        <v-chip size="small" label variant="tonal" color="primary">
          Selected: {{ selectedIds.length }}
        </v-chip>
      </v-col>
    </v-row>

    <v-alert v-if="error" type="error" variant="tonal" class="mt-4">
      {{ error }}
    </v-alert>

    <v-skeleton-loader v-if="loading" type="table" class="mt-4" />

    <v-alert v-else-if="filteredCategories.length === 0" type="info" variant="tonal" class="mt-4">
      No categories found.
    </v-alert>

    <v-table v-else density="comfortable" class="border rounded mt-4">
      <thead>
        <tr>
          <th class="select-col">Select</th>
          <th>Category</th>
          <th>Slug</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="category in filteredCategories" :key="category.id">
          <td class="select-col">
            <v-checkbox v-model="selectedIds" :value="category.id" color="primary" hide-details />
          </td>
          <td class="py-3">
            <div class="text-body-2 font-weight-medium">
              {{ category.title || '-' }}
            </div>
          </td>
          <td class="py-3 text-body-2 text-medium-emphasis">
            {{ category.slug || '-' }}
          </td>
        </tr>
      </tbody>
    </v-table>

  </v-card-text>

  <v-divider />

  <v-card-actions class="px-6 pb-4">
    <v-spacer />
    <v-btn variant="text" color="error" @click="emit('close')">
      Cancel
    </v-btn>
    <v-btn color="primary" variant="flat" :loading="saving" :disabled="saving || !brandId" @click="saveCategories">
      Save Changes
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { listProductCategoriesLite, type ProductCategoryListItem } from '@/api/product-categories.api';
import { syncBrandCategories, type ProductBrandDetailResponse } from '@/api/products.api';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  brandId: string | number | null | undefined;
  initialSelectedIds?: Array<string | number | null | undefined>;
  item?: ProductBrandDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const snackbar = useSnackbarStore();
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const search = ref('');
const categories = ref<ProductCategoryListItem[]>([]);
const selectedIds = ref<Array<string | number>>([]);

function keyFor(id: string | number | null | undefined) {
  return String(id ?? '');
}

function normalizeSelectedIds(source: Array<string | number | null | undefined> = []) {
  return source
    .filter((value): value is string | number => value !== null && value !== undefined && String(value).trim() !== '')
    .map((value) => (typeof value === 'number' ? value : value));
}

const filteredCategories = computed(() => {
  const query = search.value.trim().toLowerCase();
  const items = [...categories.value].sort((a, b) => {
    const leftSeq = Number(a.seq_no ?? 0);
    const rightSeq = Number(b.seq_no ?? 0);
    if (leftSeq !== rightSeq) return leftSeq - rightSeq;
    return String(a.title ?? '').localeCompare(String(b.title ?? ''));
  });

  if (!query) return items;
  return items.filter((category) => {
    const haystack = [
      category.title,
      category.slug,
      category.seq_no,
    ]
      .filter((value) => value !== null && value !== undefined)
      .join(' ')
      .toLowerCase();

    return haystack.includes(query);
  });
});

async function fetchCategories() {
  loading.value = true;
  error.value = '';
  try {
    categories.value = await listProductCategoriesLite();

    const initialIds = normalizeSelectedIds([
      ...(props.initialSelectedIds ?? []),
      ...((props.item?.categories ?? []).map((category) => category.id) as Array<string | number | null | undefined>),
    ]);

    selectedIds.value = Array.from(
      new Set(initialIds.map((value) => keyFor(value))),
    ).map((value) => {
      const numeric = Number(value);
      return Number.isNaN(numeric) ? value : numeric;
    });
  } catch (exception: any) {
    error.value = exception?.response?.data?.message || 'Failed to load categories.';
  } finally {
    loading.value = false;
  }
}

async function saveCategories() {
  const brandId = props.brandId;
  if (brandId === null || brandId === undefined || String(brandId).trim() === '') return;

  saving.value = true;
  error.value = '';
  try {
    const response = await syncBrandCategories(brandId, selectedIds.value);
    snackbar.show({
      message: 'Brand categories updated successfully.',
      color: 'success',
    });
    emit('saved', response);
    emit('close');
  } catch (exception: any) {
    error.value = exception?.response?.data?.message || 'Failed to update brand categories.';
    snackbar.show({
      message: error.value,
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}

onMounted(fetchCategories);
</script>

<style scoped>
.select-col {
  width: 72px;
}
</style>
