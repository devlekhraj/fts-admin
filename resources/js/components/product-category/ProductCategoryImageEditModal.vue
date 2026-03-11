<template>
  <v-card-text class="py-6">
    <div class="d-flex align-start ga-4">
      <div class="preview-box rounded">
        <v-img v-if="file.url" :src="String(file.url)" cover :title="String(file.url)" />
        <div v-else class="d-flex align-center justify-center h-100">
          <v-icon size="30" color="grey-darken-1">mdi-image-outline</v-icon>
        </div>
      </div>

      <div>
        <div class="text-body-2 font-weight-medium">File #{{ file.id }}</div>
        <div class="text-caption text-medium-emphasis mt-1">
          {{ String(file.size_info ?? '').trim() || '-' }}
        </div>
        <div class="text-caption text-medium-emphasis mt-1">
          Alt Text: {{ String(file.alt_text ?? '').trim() || '-' }}
        </div>
      </div>
    </div>

    <v-divider class="my-4" />

    <v-form @submit.prevent="onUpdate">
      <v-row>
        <v-col cols="12" md="12" class="pb-0">
          <app-field-label label="Alt Text" />
          <v-textarea
            v-model="form.alt_text"
            variant="outlined"
            density="comfortable"
            rows="2"
            auto-grow
            maxlength="130"
            counter="130"
            required />
        </v-col>

        <v-col cols="12" md="12" class="py-0">
          <app-field-label label="Is Default" />
          <v-switch v-model="form.is_default" color="primary" inset density="comfortable" />
        </v-col>
      </v-row>
    </v-form>
  </v-card-text>

  <v-divider />

  <v-card-actions class="pb-4 px-6 d-flex justify-space-between">
    <v-btn color="error" variant="tonal" :loading="deleting" :disabled="updating || deleting" @click="onDelete">
      <v-icon start size="16">mdi-delete</v-icon>
      Delete Image
    </v-btn>

    <v-btn color="primary" variant="tonal" :loading="updating" :disabled="updating || deleting" @click="onUpdate">
      <v-icon start size="16">mdi-content-save-outline</v-icon>
      Update Image
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { deleteProductCategoryImage, updateProductCategoryImage } from '@/api/product-categories.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type ProductCategoryFile = {
  id: number | string;
  url?: string | null;
  alt_text?: string | null;
  meta?: Record<string, unknown> | null;
  size_info?: string | null;
};

const props = defineProps<{
  categoryId: number | string | null;
  file: ProductCategoryFile;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const snackbar = useSnackbarStore();
const updating = ref(false);
const deleting = ref(false);

const form = reactive({
  alt_text: String(props.file.alt_text ?? '').trim(),
  is_default: isDefaultFlag(props.file.meta?.is_default),
});

function isDefaultFlag(value: unknown): boolean {
  return value === true || value === 1 || value === '1' || String(value).toLowerCase() === 'true';
}

async function onUpdate() {
  const categoryId = props.categoryId;
  const fileUsageId = props.file.id;
  if (categoryId === null || categoryId === undefined || String(categoryId).trim() === '') {
    snackbar.show({ message: 'Category id is required.', color: 'error' });
    return;
  }

  if (fileUsageId === null || fileUsageId === undefined || String(fileUsageId).trim() === '') {
    snackbar.show({ message: 'File usage id is required.', color: 'error' });
    return;
  }

  if (!String(form.alt_text ?? '').trim()) {
    snackbar.show({ message: 'Alt text is required.', color: 'error' });
    return;
  }

  updating.value = true;
  try {
    await updateProductCategoryImage(categoryId, fileUsageId, {
      alt_text: String(form.alt_text ?? '').trim(),
      is_default: Boolean(form.is_default),
    });

    snackbar.show({ message: 'Product category image updated successfully.', color: 'success' });
    emit('saved', { file_usage_id: props.file.id, action: 'updated' });
    emit('close');
  } catch (error) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
  } finally {
    updating.value = false;
  }
}

async function onDelete() {
  const categoryId = props.categoryId;
  const fileUsageId = props.file.id;
  if (categoryId === null || categoryId === undefined || String(categoryId).trim() === '') {
    snackbar.show({ message: 'Category id is required.', color: 'error' });
    return;
  }

  if (fileUsageId === null || fileUsageId === undefined || String(fileUsageId).trim() === '') {
    snackbar.show({ message: 'File usage id is required.', color: 'error' });
    return;
  }

  deleting.value = true;
  try {
    await deleteProductCategoryImage(categoryId, fileUsageId);

    snackbar.show({ message: 'Product category image deleted successfully.', color: 'success' });
    emit('saved', { file_usage_id: props.file.id, action: 'deleted' });
    emit('close');
  } catch (error) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
  } finally {
    deleting.value = false;
  }
}
</script>

<style scoped>
.preview-box {
  width: 180px;
  height: 100px;
  background: rgb(var(--v-theme-surface-variant));
  overflow: hidden;
  flex-shrink: 0;
}
</style>
