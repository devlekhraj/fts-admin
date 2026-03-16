<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <div>
          <app-field-label label="Short Description" />
          <v-textarea
            v-model="form.short_desc"
            variant="outlined"
            density="comfortable"
            auto-grow
            rows="2" />
        </div>

        <div class="mt-3">
          <app-field-label label="Description" />
          <RichText v-model="form.description" />
        </div>

        <div class="mt-3">
          <app-field-label label="Warranty Description" />
          <v-textarea
            v-model="form.warranty_description"
            variant="outlined"
            density="comfortable"
            auto-grow
            rows="4" />
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';

const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  short_desc: '',
  description: '',
  warranty_description: '',
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    const description = item?.description;

    form.short_desc = String(description?.short_desc ?? '');
    form.description = String(description?.description ?? '');
    form.warranty_description = String(description?.warranty_description ?? '');
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.productId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateProduct(id, {
      short_desc: form.short_desc,
      description: form.description,
      content: form.description,
      warranty_description: form.warranty_description,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
