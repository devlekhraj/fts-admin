<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">Update</v-btn>
        </div>

        <v-text-field v-model="form.name" label="Name" variant="outlined" density="comfortable" />
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
import { updateBrand, type ProductBrandDetailResponse } from '@/api/products.api';

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
  brandId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  name: '',
  slug: '',
  status: '0',
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.name = item?.name ? String(item.name) : '';
    form.slug = item?.slug ? String(item.slug) : '';
    form.status = item?.status ? '1' : '0';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.brandId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateBrand(id, {
      name: form.name.trim(),
      slug: form.slug.trim(),
      status: Number(form.status) === 1,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
