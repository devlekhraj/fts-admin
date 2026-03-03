<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">Update</v-btn>
        </div>

        <v-text-field v-model="form.title" label="Title" variant="outlined" density="comfortable" />
        <v-text-field v-model="form.slug" label="Slug" variant="outlined" density="comfortable" class="mt-3" />
        <v-text-field
          v-model="form.status"
          label="Status"
          hint="Use 1 for active, 0 for inactive"
          persistent-hint
          variant="outlined"
          density="comfortable"
          class="mt-3" />
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { updateProductCategory, type ProductCategoryDetailResponse } from '@/api/product-categories.api';

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
  categoryId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  title: '',
  slug: '',
  status: '0',
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.title = item?.title ? String(item.title) : '';
    form.slug = item?.slug ? String(item.slug) : '';
    form.status = item?.status ? '1' : '0';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.categoryId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateProductCategory(id, {
      title: form.title.trim(),
      slug: form.slug.trim(),
      status: Number(form.status) === 1,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
