<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Product Description</div>
            <div class="text-body-2 text-medium-emphasis">Manage product descriptions and highlights.</div>
          </div>
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="descriptionFormRef">
          <div class="mb-0">
            <app-field-label label="Short Description" />
            <v-textarea
              v-model="form.short_description"
              variant="outlined"
              density="comfortable"
              auto-grow
              rows="2"
              placeholder="A brief summary of the product"
            />
          </div>

         <div class="mb-0">
            <app-field-label label="Highlights" />
            <v-textarea
              v-model="form.highlights"
              variant="outlined"
              density="comfortable"
              auto-grow
              rows="3"
              placeholder="Key features (one per line)"
            />
          </div>

          <div class="mb-0">
            <app-field-label label="Detailed Description" />
            <RichText v-model="form.description" />
          </div>

          <div class="mb-0">
            <app-field-label label="Warranty Description" />
            <v-textarea
              v-model="form.warranty_description"
              variant="outlined"
              density="comfortable"
              auto-grow
              rows="3"
              placeholder="Warranty terms and conditions"
            />
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const descriptionFormRef = ref();

const form = reactive({
  short_description: '',
  description: '',
  highlights: '',
  warranty_description: '',
});

watch(
  () => props.item,
  (item) => {
    const description = item?.description;

    form.short_description = String(description?.short_description ?? '');
    form.description = String(description?.description ?? '');
    form.highlights = String(description?.highlights ?? '');
    form.warranty_description = String(description?.warranty_description ?? '');
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.productId ?? '').trim();
  if (!id) return;

  const { valid } = await descriptionFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateProduct(id, {
      short_description: form.short_description.trim(),
      description: form.description,
      highlights: form.highlights.trim(),
      warranty_description: form.warranty_description.trim(),
    });
    
    snackbar.show({
      message: 'Product description updated successfully',
      color: 'success',
    });
    
    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update product description',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
