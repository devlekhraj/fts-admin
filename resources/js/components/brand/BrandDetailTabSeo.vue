<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">Update</v-btn>
        </div>

        <v-textarea
          v-model="form.meta_title"
          label="Meta Title"
          variant="outlined"
          density="comfortable"
          rows="3"
          auto-grow />
        <v-textarea
          v-model="form.meta_keywords"
          label="Meta Keywords"
          variant="outlined"
          density="comfortable"
          rows="3"
          auto-grow
          class="mt-3" />
        <v-textarea
          v-model="form.meta_description"
          label="Meta Description"
          variant="outlined"
          density="comfortable"
          rows="4"
          auto-grow
          class="mt-3" />
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { updateBrand, type ProductBrandDetailResponse } from '@/api/products.api';

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
  brandId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  meta_title: '',
  meta_keywords: '',
  meta_description: '',
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.meta_title = item?.meta_title ? String(item.meta_title) : '';
    form.meta_keywords = item?.meta_keywords ? String(item.meta_keywords) : '';
    form.meta_description = item?.meta_description ? String(item.meta_description) : '';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.brandId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateBrand(id, {
      meta_title: form.meta_title,
      meta_keywords: form.meta_keywords,
      meta_description: form.meta_description,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
