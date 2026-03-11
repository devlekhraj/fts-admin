<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">

        <div class="pt-10">
          <app-field-label label="Title" />
          <v-text-field v-model="form.title" variant="outlined" density="comfortable" />
        </div>

        <div>
          <app-field-label label="Slug" />
          <v-text-field v-model="form.slug" variant="outlined" density="comfortable" />
        </div>

        <div>
          <app-field-label label="Status" />
          <div style="max-width: 200px;">
            <v-select v-model="form.status" :items="statusOptions" item-title="label" item-value="value"
              variant="outlined" density="comfortable" />
          </div>
        </div>

        <div class="d-flex justify-space-around mb-4">
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import type { BlogCategoryDetailResponse } from '@/api/blog-categories.api';
import { update as updateBlogCategory } from '@/api/blog-categories.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: BlogCategoryDetailResponse | null;
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
const statusOptions = [
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' },
];
const snackbar = useSnackbarStore();
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
    await updateBlogCategory(id, {
      title: form.title.trim(),
      slug: form.slug.trim(),
      status: Number(form.status) === 1,
    });
    snackbar.show({ message: 'Blog category updated successfully.', color: 'success' });
    // emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
