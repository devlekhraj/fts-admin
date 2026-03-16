<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Product Overview</div>
            <div class="text-body-2 text-medium-emphasis">Edit product name, slug, and status.</div>
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
import { reactive, ref, watch } from 'vue';
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

const snackbar = useSnackbarStore();
const saving = ref(false);
const overviewFormRef = ref();

const form = reactive({
  name: '',
  slug: '',
  sku: '',
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
    form.status = item?.overview?.status ? '1' : '0';
    form.emi_enabled = item?.overview?.emi_enabled ? '1' : '0';
  },
  { immediate: true },
);

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
