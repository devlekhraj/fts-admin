<template>
  <v-card-text class="py-6">
    <v-form ref="formRef" class="banner-form-grid" @submit.prevent="onSubmit">
      <div class="mb-4">
     
        <div class="image-picker rounded" role="button" tabindex="0" @click="openImagePicker" @keydown.enter="openImagePicker">
          <v-img v-if="imagePreview" :src="imagePreview" contain />
          <div v-else class="image-picker-empty">
            <v-icon size="36" color="grey-darken-1">mdi-image-plus-outline</v-icon>
            <div class="text-body-2 text-medium-emphasis mt-2">Select banner image</div>
          </div>
        </div>
        <div v-if="imageError" class="text-caption text-error mt-1">{{ imageError }}</div>
        <div v-if="imageInfo.length" class="image-info mt-2">
          <div v-for="info in imageInfo" :key="info.label" class="image-info-item">
            <span class="image-info-label">{{ info.label }}:</span>
            <span class="image-info-value">{{ info.value }}</span>
          </div>
        </div>
        <v-file-input
          ref="imageInputRef"
          v-model="form.image"
          class="d-none"
          variant="outlined"
          density="comfortable"
          accept="image/*"
          prepend-icon=""
          label="Upload Image"
          :rules="[rules.image]"
          @update:model-value="onImageChange" />
      </div>

      <v-row>
        <v-col cols="12" class="py-0">
          <app-field-label label="Redirect URL" />
          <v-text-field v-model="form.redirectUrl" variant="outlined" density="comfortable"
            :rules="[rules.url]" placeholder="https://example.com/offer" />
        </v-col>

        <v-col cols="4" class="py-0">
          <app-field-label label="Start Date" />
          <v-date-input
            v-model="form.startDate"
            :min="today"
            :max="form.endDate ? form.endDate : null"
            prepend-icon=""
            prepend-inner-icon="mdi-calendar"
            variant="outlined"
            density="comfortable" />
        </v-col>

        <v-col cols="4" class="py-0">
          <app-field-label label="End Date" />
          <v-date-input
            v-model="form.endDate"
            :min="form.startDate ? form.startDate : null"
            prepend-icon=""
            prepend-inner-icon="mdi-calendar"
            variant="outlined"
            density="comfortable" />
        </v-col>

        <v-col cols="4" class="py-0">
          <app-field-label label="Status" />
          <v-select
            v-model="form.status"
            :items="statusOptions"
            variant="outlined"
            density="comfortable"
            :rules="[rules.required]" />
        </v-col>
      </v-row>
    </v-form>
  </v-card-text>

  <v-divider />

  <v-card-actions class="pb-4 px-6">
    <v-spacer />
    <v-btn variant="text" :disabled="saving" @click="emit('close')">Cancel</v-btn>
    <v-btn color="primary" variant="flat" :loading="saving" :disabled="saving" @click="onSubmit">
      Save Banner
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { saveBrandBanner } from '@/api/brands.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  brandId: number | string | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const formRef = ref();
const imageInputRef = ref();
const saving = ref(false);
const imagePreviewUrl = ref('');
const imageDimensions = ref<{ width: number; height: number } | null>(null);
const imageError = ref('');
const today = new Date();
const snackbar = useSnackbarStore();

const form = reactive({
  image: null as File | File[] | null,
  redirectUrl: '',
  startDate: null as Date | null,
  endDate: null as Date | null,
  status: 'active',
});

const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
];

const rules = {
  required: (value: unknown) => (String(value ?? '').trim() ? true : 'Required'),
  image: (value: File | File[] | null) => getImageFile(value) ? true : 'Image is required',
  url: (value: string) => {
    const text = String(value ?? '').trim();
    if (!text) return true;
    return /^https?:\/\/.+/i.test(text) ? true : 'Enter a valid http or https URL';
  },
};

const imagePreview = computed(() => imagePreviewUrl.value);
const imageInfo = computed(() => {
  const file = getImageFile(form.image);
  if (!file) return [];

  const items = [
    { label: 'Name', value: file.name },
    { label: 'Type', value: file.type || 'Unknown type' },
    { label: 'Size', value: formatBytes(file.size) },
  ];

  if (imageDimensions.value) {
    const { width, height } = imageDimensions.value;
    items.push({ label: 'Dimensions', value: `${width}x${height}` });
    items.push({ label: 'Aspect Ratio', value: formatAspectRatio(width, height) });
  }

  return items;
});

function getImageFile(value: File | File[] | null): File | null {
  if (Array.isArray(value)) return value[0] ?? null;
  return value ?? null;
}

function onImageChange(value: File | File[] | null) {
  const file = getImageFile(value);
  imageError.value = '';
  if (imagePreviewUrl.value) {
    URL.revokeObjectURL(imagePreviewUrl.value);
  }
  imageDimensions.value = null;
  imagePreviewUrl.value = file ? URL.createObjectURL(file) : '';
  if (imagePreviewUrl.value) {
    const image = new Image();
    image.onload = () => {
      imageDimensions.value = {
        width: image.naturalWidth,
        height: image.naturalHeight,
      };
    };
    image.src = imagePreviewUrl.value;
  }
}

function openImagePicker() {
  imageInputRef.value?.$el?.querySelector?.('input')?.click?.();
}

function formatBytes(bytes: number): string {
  if (!Number.isFinite(bytes) || bytes <= 0) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  let size = bytes;
  let unitIndex = 0;
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex += 1;
  }
  return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
}

function formatAspectRatio(width: number, height: number): string {
  const divisor = gcd(width, height);
  return `${width / divisor}:${height / divisor}`;
}

function gcd(a: number, b: number): number {
  return b === 0 ? a : gcd(b, a % b);
}

async function onSubmit() {
  const { valid } = (await formRef.value?.validate?.()) ?? { valid: true };
  if (!valid) return;

  const image = getImageFile(form.image);
  if (!image) {
    imageError.value = 'Select image';
    snackbar.show({ message: 'Select image', color: 'error' });
    return;
  }

  const brandId = props.brandId;
  if (brandId === null || brandId === undefined || String(brandId).trim() === '') {
    snackbar.show({ message: 'Brand id is required.', color: 'error' });
    return;
  }

  saving.value = true;
  try {
    const payload = new FormData();
    payload.append('redirect_url', String(form.redirectUrl ?? '').trim());
    payload.append('start_date', formatDateToYMD(form.startDate));
    payload.append('end_date', formatDateToYMD(form.endDate));
    payload.append('status', form.status);
    if (image) {
      payload.append('image', image);
    }

    const response = await saveBrandBanner(brandId, payload);
    snackbar.show({ message: (response as any)?.message || 'Brand banner saved successfully.', color: 'success' });
    emit('close');
  } catch (error) {
    snackbar.show({ message: getErrorMessage(error), color: 'error' });
  } finally {
    saving.value = false;
  }
}

function formatDateToYMD(date: Date | null): string {
  return date ? new Date(date).toISOString().split('T')[0] : '';
}
</script>

<style scoped>
.banner-form-grid {
  display: grid;
  gap: 16px;
}

.image-picker {
  width: 100%;
  aspect-ratio: 16 / 9;
  max-height: 250px;
  overflow: hidden;
  cursor: pointer;
  border: 2px dashed #000;
  object-fit: contain;
}

.image-picker-empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 20px;
}

.image-info {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 14px;
}

.image-info-item {
  font-size: 0.75rem;
  line-height: 1.35;
}

.image-info-label {
  color: rgb(var(--v-theme-primary));
  font-weight: 600;
  margin-right: 4px;
}

.image-info-value {
  color: rgba(var(--v-theme-on-surface), 0.7);
}

</style>
