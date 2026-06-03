<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Product Overview</div>
            <div class="text-body-2 text-medium-emphasis">Edit product name, slug, brand, categories, and status.</div>
          </div>
          <v-btn color="primary" :loading="saving" @click="onUpdate" variant="flat">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="overviewFormRef">
          <div class="mb-0">
            <app-field-label label="Name" />
            <v-textarea
              v-model="form.name"
              variant="outlined"
              density="comfortable"
              auto-grow
              rows="2"
              :rules="[v => !!v || 'Name is required']"
            />
          </div>

          <div class="mb-0">
            <app-field-label label="Slug" />
            <v-text-field
              v-model="form.slug"
              variant="outlined"
              density="comfortable"
              :rules="[v => !!v || 'Slug is required']"
            />
          </div>

          <div class="mb-0">
            <app-field-label label="SKU" />
            <v-text-field
              v-model="form.sku"
              variant="outlined"
              density="comfortable"
            />
          </div>

          <div class="mb-0">
            <v-row>
              <v-col cols="12" md="6">
                <app-field-label label="Brand" />
                <v-select
                  v-model="form.brand_id"
                  :items="brandOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="comfortable"
                  clearable
                  :loading="loadingOptions"
                  placeholder="Select brand"
                />
              </v-col>

              <v-col cols="12" md="6">
                <app-field-label label="Categories" />
                <v-select
                  v-model="form.category_ids"
                  :items="categoryOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="comfortable"
                  multiple
                  chips
                  closable-chips
                  clearable
                  :loading="loadingOptions"
                  placeholder="Select categories"
                />
              </v-col>

              <v-col cols="12" md="6">
                <app-field-label label="Status" />
                <v-select
                  v-model="form.status"
                  :items="statusOptions"
                  item-title="label"
                  item-value="value"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => v !== null || 'Status is required']"
                />
              </v-col>

              <v-col cols="12" md="6">
                <app-field-label label="EMI Enabled" />
                <v-select
                  v-model="form.emi_enabled"
                  :items="emiOptions"
                  item-title="label"
                  item-value="value"
                  variant="outlined"
                  density="comfortable"
                />
              </v-col>
            </v-row>
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';
import { listProductBrandsLite, type ProductBrandLiteItem } from '@/api/product-brands.api';
import { listProductCategoriesLite, type ProductCategoryListItem } from '@/api/product-categories.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

type SelectOption = {
  title: string;
  value: number;
};

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const loadingOptions = ref(false);
const overviewFormRef = ref();
const brandOptions = ref<SelectOption[]>([]);
const categoryOptions = ref<SelectOption[]>([]);

const form = reactive({
  name: '',
  slug: '',
  sku: '',
  brand_id: null as number | null,
  category_ids: [] as number[],
  status: '0',
  emi_enabled: '0',
});

const statusOptions = [
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' },
];

const emiOptions = [
  { label: 'Enabled', value: '1' },
  { label: 'Disabled', value: '0' },
];

watch(
  () => props.item,
  (item) => {
    form.name = item?.overview?.name ? String(item.overview.name) : '';
    form.slug = item?.overview?.slug ? String(item.overview.slug) : '';
    form.sku = item?.overview?.sku ? String(item.overview.sku) : '';
    form.brand_id = normalizeId(item?.brand?.id);
    form.category_ids = Array.isArray(item?.categories)
      ? item.categories.map((category) => normalizeId(category.id)).filter((id): id is number => id !== null)
      : [];
    form.status = item?.overview?.status ? '1' : '0';
    form.emi_enabled = item?.overview?.emi_enabled ? '1' : '0';
  },
  { immediate: true },
);

onMounted(() => {
  fetchOptions();
});

function normalizeId(value: unknown): number | null {
  if (value === null || value === undefined || value === '') return null;
  const id = Number(value);
  return Number.isFinite(id) ? id : null;
}

async function fetchOptions() {
  loadingOptions.value = true;
  try {
    const [brands, categories] = await Promise.all([
      listProductBrandsLite(),
      listProductCategoriesLite(),
    ]);

    brandOptions.value = brands
      .map((brand: ProductBrandLiteItem) => ({
        title: String(brand.name ?? ''),
        value: normalizeId(brand.id),
      }))
      .filter((brand): brand is SelectOption => brand.value !== null && brand.title.length > 0);

    categoryOptions.value = categories
      .map((category: ProductCategoryListItem) => ({
        title: String(category.title ?? ''),
        value: normalizeId(category.id),
      }))
      .filter((category): category is SelectOption => category.value !== null && category.title.length > 0);
  } catch (error) {
    snackbar.show({
      message: 'Failed to load brand and category options',
      color: 'error',
    });
  } finally {
    loadingOptions.value = false;
  }
}

async function onUpdate() {
  const id = String(props.productId ?? '').trim();
  if (!id) return;

  const { valid } = await overviewFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateProduct(id, {
      name: form.name.trim(),
      slug: form.slug.trim(),
      sku: form.sku.trim(),
      brand_id: form.brand_id || null,
      category_ids: form.category_ids,
      status: Number(form.status) === 1,
      emi_enabled: Number(form.emi_enabled) === 1,
    });

    snackbar.show({
      message: 'Product overview updated successfully',
      color: 'success',
    });

    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update product overview',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
