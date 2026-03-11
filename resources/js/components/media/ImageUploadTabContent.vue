<template>
  <v-row class="align-stretch">
    <v-col cols="12" md="6" class="d-flex">
      <v-card class="h-100 w-100">
        <v-card-text class="border rounded h-100 d-flex flex-column">
          <input ref="uploadInputRef" type="file" accept="image/*" class="d-none" @change="onNativeFileChange" />

          <div class="upload-dropzone" :class="{
            'upload-dropzone--active': isDragOver,
            'upload-dropzone--filled': !!uploadImageInfo,
          }" @click="triggerFilePicker" @dragenter.prevent="isDragOver = true" @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false" @drop.prevent="onDropFile">
            <template v-if="uploadImageInfo?.previewUrl">
              <v-img :src="uploadImageInfo.previewUrl" contain class="upload-dropzone-preview" />
            </template>
            <template v-else>
              <div class="upload-dropzone-title">{{ dropzoneTitle }}</div>
              <div class="upload-dropzone-subtitle">{{ dropzoneSubtitle }}</div>
            </template>
          </div>

          <template v-if="uploadImageInfo">
            <div class="mt-2">
              <div class="text-caption text-medium-emphasis mt-2 selected-image-info">
                <div>Name: {{ uploadImageInfo.name }}</div>
                <div>Size: {{ uploadImageInfo.size }}</div>
                <div>Dimension: {{ uploadImageInfo.dimension }}</div>
                <!-- <div>Extension: {{ uploadImageInfo.extension }}</div> -->
              </div>
              <div class="mt-2">
                <v-btn size="small" variant="tonal" color="warning" @click.stop="clearUpload">
                  {{ removeButtonText }}
                </v-btn>
              </div>
            </div>
          </template>
        </v-card-text>
      </v-card>
    </v-col>

    <v-col cols="12" md="6" class="d-flex">
      <v-card class="h-100 w-100">
        <v-card-text class="rounded h-100">
          <div class="text-subtitle-2 mb-3">{{ seoTitle }}</div>
          <div class="text-caption text-medium-emphasis mb-1">Image Alt Text</div>
          <v-textarea v-model="altTextModel" variant="outlined" density="comfortable" auto-grow rows="2" maxlength="80" :rules="altTextRules" required />
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { formatBytes } from '@/shared/utils';

type SeoState = {
  alt_text: string;
};

const props = withDefaults(defineProps<{
  file: File | null;
  seo: SeoState;
  seoTitle?: string;
  dropzoneTitle?: string;
  dropzoneSubtitle?: string;
  removeButtonText?: string;
}>(), {
  seoTitle: 'SEO Information',
  dropzoneTitle: 'Drop image here',
  dropzoneSubtitle: 'or click to browse',
  removeButtonText: 'Remove',
});

const emit = defineEmits<{
  (e: 'update:file', value: File | null): void;
  (e: 'update:seo', value: SeoState): void;
}>();

const uploadInputRef = ref<HTMLInputElement | null>(null);
const isDragOver = ref(false);
const uploadPreviewUrl = ref('');
const uploadWidth = ref<number | null>(null);
const uploadHeight = ref<number | null>(null);

const uploadImageInfo = computed(() => {
  if (!props.file) return null;
  return {
    previewUrl: uploadPreviewUrl.value,
    name: props.file.name,
    size: formatBytes(props.file.size),
    dimension:
      Number(uploadWidth.value ?? 0) > 0 && Number(uploadHeight.value ?? 0) > 0
        ? `${uploadWidth.value} x ${uploadHeight.value}`
        : '-',
    extension: getFileExtension(props.file.name),
  };
});

const altTextModel = computed({
  get: () => props.seo.alt_text,
  set: (value: string) => emit('update:seo', { ...props.seo, alt_text: value }),
});

const altTextRules = [
  (value: string) => (String(value ?? '').trim() !== '' ? true : 'Alt Text is required.'),
  (value: string) => (String(value ?? '').length <= 200 ? true : 'Alt Text cannot exceed 200 characters.'),
];

watch(
  () => props.file,
  async (file) => {
    if (uploadPreviewUrl.value) {
      URL.revokeObjectURL(uploadPreviewUrl.value);
      uploadPreviewUrl.value = '';
    }
    uploadWidth.value = null;
    uploadHeight.value = null;
    if (!file) return;

    const objectUrl = URL.createObjectURL(file);
    uploadPreviewUrl.value = objectUrl;
    const dimensions = await readImageDimensions(objectUrl);
    if (dimensions) {
      uploadWidth.value = dimensions.width;
      uploadHeight.value = dimensions.height;
    }
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  if (uploadPreviewUrl.value) {
    URL.revokeObjectURL(uploadPreviewUrl.value);
  }
});

function triggerFilePicker() {
  uploadInputRef.value?.click();
}

function onNativeFileChange(event: Event) {
  const target = event.target as HTMLInputElement | null;
  const file = target?.files?.[0] ?? null;
  emit('update:file', file);
}

function onDropFile(event: DragEvent) {
  isDragOver.value = false;
  const file = event.dataTransfer?.files?.[0] ?? null;
  if (!file) return;
  emit('update:file', file);
}

function clearUpload() {
  emit('update:file', null);
  if (uploadInputRef.value) {
    uploadInputRef.value.value = '';
  }
}

function getFileExtension(value: string): string {
  const cleaned = String(value ?? '').trim();
  if (!cleaned) return '-';
  const withoutQuery = cleaned.split('?')[0].split('#')[0];
  const filename = withoutQuery.split('/').pop() ?? withoutQuery;
  const lastDotIndex = filename.lastIndexOf('.');
  if (lastDotIndex <= 0 || lastDotIndex === filename.length - 1) return '-';
  return filename.slice(lastDotIndex + 1).toUpperCase();
}

function readImageDimensions(url: string): Promise<{ width: number; height: number } | null> {
  return new Promise((resolve) => {
    const image = new Image();
    image.onload = () => resolve({ width: image.naturalWidth, height: image.naturalHeight });
    image.onerror = () => resolve(null);
    image.src = url;
  });
}
</script>

<style scoped>
.selected-image-info {
  min-width: 0;
}

.upload-dropzone {
  flex: 1;
  border: 1px dashed rgb(var(--v-theme-outline-variant));
  border-radius: 8px;
  min-height: 170px;
  padding: 16px;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  cursor: pointer;
  background: #efefef;
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

.upload-dropzone--filled {
  padding: 0;
  background: rgb(var(--v-theme-surface));
  overflow: hidden;
}

.upload-dropzone--active {
  border-color: rgb(var(--v-theme-primary));
  background: color-mix(in srgb, rgb(var(--v-theme-primary)) 6%, #efefef);
}

.upload-dropzone-preview {
  width: 100%;
  height: 170px;
}

.upload-dropzone-title {
  font-size: 28px;
  line-height: 1.2;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
}

.upload-dropzone-subtitle {
  margin-top: 6px;
  font-size: 16px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

@media (max-width: 960px) {
  .upload-dropzone-title {
    font-size: 22px;
  }

  .upload-dropzone-subtitle {
    font-size: 14px;
  }
}
</style>
