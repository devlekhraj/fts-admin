<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">Update</v-btn>
        </div>

        <v-textarea
          v-model="form.short_desc"
          label="Short Description"
          variant="outlined"
          density="comfortable"
          auto-grow />

        <div class="mt-4">
          <div class="text-overline text-medium-emphasis mb-2">Content</div>
          <RichText v-model="form.content" />
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, ref, watch } from 'vue';
import { updateProductCategory, type ProductCategoryDetailResponse } from '@/api/product-categories.api';

const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
  categoryId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  short_desc: '',
  content: '',
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.short_desc = item?.short_desc ? String(item.short_desc) : '';
    form.content = item?.content ? String(item.content) : String(item?.description ?? '');
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.categoryId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateProductCategory(id, {
      short_desc: form.short_desc,
      content: form.content,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
