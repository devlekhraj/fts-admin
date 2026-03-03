<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">Update</v-btn>
        </div>

        <v-textarea v-model="form.name" label="Name" variant="outlined" density="comfortable" auto-grow rows="2" />
        <v-text-field v-model="form.slug" label="Slug" variant="outlined" density="comfortable" class="mt-3" />
        <div class="d-flex align-center flex-wrap ga-10 mt-3">
          <v-switch v-model="form.status" label="Status" color="primary" inset hide-details />
          <v-switch v-model="form.emi_enabled" label="EMI Enabled" color="primary" inset hide-details />
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  name: '',
  slug: '',
  status: false,
  emi_enabled: false,
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.name = item?.name ? String(item.name) : '';
    form.slug = item?.slug ? String(item.slug) : '';
    form.status = Boolean(item?.status);
    form.emi_enabled = Boolean(item?.emi_enabled);
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.productId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateProduct(id, {
      name: form.name.trim(),
      slug: form.slug.trim(),
      status: form.status,
      emi_enabled: form.emi_enabled,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
