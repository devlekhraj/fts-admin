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
        <div class="text-body-2 font-weight-medium">{{ file.title || `File #${file.file_id ?? file.id}` }}</div>

        <div class="text-caption text-medium-emphasis mt-1">
          {{ fileSizeLabel }} | {{ fileDimensionLabel }}
        </div>

      </div>
    </div>

    <v-divider class="my-4" />

    <v-form @submit.prevent="onUpdate">
      <v-row>
        <v-col cols="12" md="12" class="pb-0">
          <app-field-label label="Alt Text" />
          <v-textarea v-model="form.alt_text" variant="outlined" density="comfortable" rows="2" auto-grow
            maxlength="130" counter="130" required />
        </v-col>

        <v-col cols="12" md="12" class="py-0">
          <app-field-label label="Make Primary Image" />
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
import { computed, reactive, ref } from 'vue';
import { deleteFileUsage, updateFileUsage } from '@/api/files.api';
import { getErrorMessage } from '@/shared/errors';
import { formatBytes } from '@/shared/utils';
import { useSnackbarStore } from '@/stores/snackbar.store';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';

type CampaignFile = {
  id: number | string; // File Usage ID
  file_id: number | string; // File ID
  url?: string | null;
  title?: string | null;
  alt_text?: string | null;
  size?: number | null;
  width?: number | null;
  height?: number | null;
  meta?: {
    link?: string | null;
    start_date?: string | null;
    end_date?: string | null;
    is_default?: boolean;
    [key: string]: any;
  } | null;
};

const props = defineProps<{
  file: CampaignFile;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const snackbar = useSnackbarStore();
const updating = ref(false);
const deleting = ref(false);
const fileSizeLabel = formatBytes(props.file.size);
const fileDimensionLabel = `${Number(props.file.width ?? 0)} x ${Number(props.file.height ?? 0)} px`;

const form = reactive({
  alt_text: String(props.file.alt_text ?? '').trim(),
  is_default: Boolean(props.file.meta?.is_default === true),
});

async function onUpdate() {
  const fileUsageId = props.file.id;
  if (!fileUsageId) {
    snackbar.show({ message: 'File usage id is required.', color: 'error' });
    return;
  }

  if (!String(form.alt_text ?? '').trim()) {
    snackbar.show({ message: 'Alt text is required.', color: 'error' });
    return;
  }

  updating.value = true;
  try {
    await updateFileUsage(fileUsageId, {
      alt_text: String(form.alt_text ?? '').trim(),
      is_default: Boolean(form.is_default),
    });

    snackbar.show({ message: 'Campaign image updated successfully.', color: 'success' });
    emit('saved', { file_id: props.file.file_id, file_usage_id: fileUsageId, action: 'updated' });
    emit('close');
  } catch (error: any) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
  } finally {
    updating.value = false;
  }
}

async function onDelete() {
  const fileUsageId = props.file.id;
  if (!fileUsageId) {
    snackbar.show({ message: 'File usage id is required.', color: 'error' });
    return;
  }

  deleting.value = true;
  try {
    await deleteFileUsage(fileUsageId);

    snackbar.show({ message: 'Campaign image deleted successfully.', color: 'success' });
    emit('saved', { file_id: props.file.file_id, file_usage_id: fileUsageId, action: 'deleted' });
    emit('close');
  } catch (error: any) {
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
