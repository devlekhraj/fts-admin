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
          <app-field-label label="Link" />
          <v-text-field v-model="form.link" variant="outlined" density="comfortable" />
        </v-col>

        <v-col cols="12" md="6" class="py-0">
          <app-field-label label="Start Date" />
          <v-date-input v-model="form.start_date" :min="minStartDate" :max="maxStartDate" prepend-icon="" variant="outlined"
            density="comfortable"></v-date-input>
        </v-col>

        <v-col cols="12" md="6" class="py-0">
          <app-field-label label="End Date" />
          <v-date-input v-model="form.end_date" :min="minEndDate" prepend-icon="" variant="outlined"
            density="comfortable"></v-date-input>
        </v-col>

        <v-col cols="12" md="6" class="py-0">
          <app-field-label label="Seq No" />
          <v-text-field v-model.number="form.seq_no" type="number" min="0" variant="outlined" density="comfortable"
           />
        </v-col>

        <v-col cols="12" md="6" class="py-0">
          <app-field-label label="Is Active" />
          <v-switch v-model="form.is_active" color="primary" inset density="comfortable" />
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

type BannerFile = {
  id: number | string;
  file_id?: number | string | null;
  url?: string | null;
  title?: string | null;
  alt_text?: string | null;
  file_size?: number | null;
  size?: number | null;
  width?: number | null;
  height?: number | null;
  meta?: Record<string, unknown> | null;
};

const props = defineProps<{
  file: BannerFile;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const snackbar = useSnackbarStore();
const updating = ref(false);
const deleting = ref(false);
const fileSizeLabel = formatBytes(props.file.file_size ?? props.file.size);
const fileDimensionLabel = `${Number(props.file.width ?? 0)} x ${Number(props.file.height ?? 0)} px`;

function toIsoDate(value: unknown): string {
  if (!value) return '';
  if (value instanceof Date) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  const raw = String(value).trim();
  if (!raw) return '';
  const head = raw.slice(0, 10);
  if (/^\d{4}-\d{2}-\d{2}$/.test(head)) return head;

  const parsed = new Date(raw);
  if (Number.isNaN(parsed.getTime())) return '';
  const year = parsed.getFullYear();
  const month = String(parsed.getMonth() + 1).padStart(2, '0');
  const day = String(parsed.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function addDays(isoDate: string, days: number): string {
  const parsed = new Date(`${isoDate}T00:00:00`);
  if (Number.isNaN(parsed.getTime())) return isoDate;
  parsed.setDate(parsed.getDate() + days);
  return toIsoDate(parsed);
}

const minStartDate = toIsoDate(new Date());

const form = reactive({
  alt_text: String(props.file.alt_text ?? '').trim(),
  link: String(props.file.meta?.link ?? '').trim(),
  start_date: toIsoDate(props.file.meta?.start_date),
  end_date: toIsoDate(props.file.meta?.end_date),
  seq_no: Number(props.file.meta?.seq_no ?? 0),
  is_active: Boolean(props.file.meta?.is_active === true),
});
const minEndDate = computed(() => {
  const start = toIsoDate(form.start_date);
  if (!start) return addDays(minStartDate, 1);
  return addDays(start, 1);
});
const maxStartDate = computed(() => {
  const end = toIsoDate(form.end_date);
  if (!end) return undefined;
  return addDays(end, -1);
});

async function onUpdate() {
  const fileUsageId = props.file.id;
  if (fileUsageId === null || fileUsageId === undefined || String(fileUsageId).trim() === '') {
    snackbar.show({ message: 'File usage id is required.', color: 'error' });
    return;
  }

  if (!String(form.alt_text ?? '').trim()) {
    snackbar.show({ message: 'Alt text is required.', color: 'error' });
    return;
  }

  const startDate = toIsoDate(form.start_date);
  const endDate = toIsoDate(form.end_date);

  // if (startDate && startDate < minStartDate) {
  //   snackbar.show({ message: 'Start date cannot be before today.', color: 'error' });
  //   return;
  // }

  if (startDate && endDate && endDate <= startDate) {
    snackbar.show({ message: 'End date must be greater than start date.', color: 'error' });
    return;
  }

  console.log({
      file_usage_id: fileUsageId,
      alt_text: String(form.alt_text ?? '').trim(),
      link: String(form.link ?? '').trim(),
      start_date: startDate,
      end_date: endDate,
      seq_no: Number.isFinite(Number(form.seq_no)) ? Number(form.seq_no) : 0,
      is_active: Boolean(form.is_active),
    });

  updating.value = true;
  try {
    await updateFileUsage(fileUsageId, {
      alt_text: String(form.alt_text ?? '').trim(),
      link: String(form.link ?? '').trim(),
      start_date: startDate,
      end_date: endDate,
      seq_no: Number.isFinite(Number(form.seq_no)) ? Number(form.seq_no) : 0,
      is_active: Boolean(form.is_active),
    });

    snackbar.show({ message: 'Banner image updated successfully.', color: 'success' });
    emit('saved', { file_id: props.file.file_id ?? null, file_usage_id: props.file.id, action: 'updated' });
    emit('close');
  } catch (error) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
  } finally {
    updating.value = false;
  }
}

async function onDelete() {
  const fileUsageId = props.file.id;
  if (fileUsageId === null || fileUsageId === undefined || String(fileUsageId).trim() === '') {
    snackbar.show({ message: 'File usage id is required.', color: 'error' });
    return;
  }

  deleting.value = true;
  try {
    await deleteFileUsage(fileUsageId);

    snackbar.show({ message: 'Banner image deleted successfully.', color: 'success' });
    emit('saved', { file_id: props.file.file_id ?? null, file_usage_id: props.file.id, action: 'deleted' });
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
