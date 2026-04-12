<template>
  <v-card-text class="py-6">
    <div class="d-flex align-start ga-4">
      <div class="preview-box rounded border pa-4">
        <v-img v-if="file.url" :src="String(file.url)" contain :title="String(file.url)" />
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
        <v-col cols="12" class="pb-0">
          <app-field-label label="Label / Type" />
          <v-select
            v-model="form.title"
            :items="filteredOptions"
            item-title="title"
            item-value="value"
            variant="outlined"
            density="comfortable"
            placeholder="Select type"
            clearable
          />
          <div class="text-caption text-medium-emphasis mt-2">
            <span>Used: {{ usedOptions.map((o) => o.title).join(', ') || 'None' }}</span>
            <br />
            <span>Available: {{ unusedOptions.map((o) => o.title).join(', ') || 'None' }}</span>
          </div>
        </v-col>

        <v-col cols="12" class="py-0">
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
      </v-row>
    </v-form>
  </v-card-text>

  <v-divider />

  <v-card-actions class="pb-4 px-6">
    <v-btn color="error" variant="tonal" :loading="deleting" :disabled="updating || deleting" @click="onDelete">
      <v-icon start size="16">mdi-delete</v-icon>
      Delete Image
    </v-btn>

    <v-spacer />

    <v-btn color="primary" variant="tonal" :loading="updating" :disabled="updating || deleting" @click="onUpdate">
      <v-icon start size="16">mdi-content-save-outline</v-icon>
      Update Image
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import { updateFileUsage, deleteFileUsage } from '@/api/files.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  file: any;
  usedTitles?: string[];
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
  (e: 'deleted', payload?: unknown): void;
}>();

const snackbar = useSnackbarStore();
const updating = ref(false);
const deleting = ref(false);

const form = reactive({
  title: String(props.file.title ?? '').trim() || 'Main Logo',
  alt_text: String(props.file.alt_text ?? '').trim(),
});

const baseOptions = [
  { title: 'Main Logo', value: 'main_logo' },
  { title: 'Login Image', value: 'login_logo' },
  { title: 'Favicon', value: 'favicon_logo' },
  { title: 'Footer Logo', value: 'footer_logo' },
  { title: 'Other', value: 'other_logo' },
];

const usedOptions = computed(() => {
  const used = props.usedTitles ?? [];
  return baseOptions.filter((opt) => used.includes(opt.value));
});

const unusedOptions = computed(() => {
  const used = props.usedTitles ?? [];
  return baseOptions.filter((opt) => !used.includes(opt.value));
});

const filteredOptions = computed(() => {
  const used = props.usedTitles ?? [];
  const current = form.title;
  return baseOptions.filter((opt) => !used.includes(opt.value) || opt.value === current);
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

  updating.value = true;
  try {
    await updateFileUsage(fileUsageId, {
      title: form.title,
      alt_text: String(form.alt_text ?? '').trim(),
    });

    snackbar.show({ message: 'Logo metadata updated successfully.', color: 'success' });
    emit('saved', { file_usage_id: props.file.id, action: 'updated' });
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

    snackbar.show({ message: 'Logo image deleted successfully.', color: 'success' });
    emit('saved', { file_usage_id: props.file.id, action: 'deleted' });
    emit('deleted', { file_usage_id: props.file.id });
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
  width: 200px;
  overflow: hidden;
  flex-shrink: 0;
}
</style>
