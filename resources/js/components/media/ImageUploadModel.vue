<template>
  <v-card-text class="py-6 pt-0">
    <v-tabs v-model="activeTab" color="primary">
      <v-tab value="existing">
        <v-icon start size="16">mdi-image-multiple-outline</v-icon>
        Existing Image
      </v-tab>
      <v-tab value="upload">
        <v-icon start size="16">mdi-cloud-upload-outline</v-icon>
        Upload New Image
      </v-tab>
    </v-tabs>
    <v-divider></v-divider>

    <v-window v-model="activeTab" class="mt-4">
      <v-window-item value="existing">
        <ImageExistingTabContent :selected-image-id="selectedExistingImageId" :seo="existingSeo"
          seo-title="SEO Information" empty-list-text="No existing image available."
          empty-selection-text="Select an existing image to view details."
          @update:selected-image-id="selectedExistingImageId = $event" @update:seo="existingSeo = $event" />
      </v-window-item>

      <v-window-item value="upload">
        <ImageUploadTabContent :file="uploadFile" :seo="uploadSeo" seo-title="SEO Information"
          dropzone-title="Drop image here" dropzone-subtitle="or click to browse" remove-button-text="Remove"
          @update:file="uploadFile = $event" @update:seo="uploadSeo = $event" />
      </v-window-item>
    </v-window>
  </v-card-text>

  <v-divider></v-divider>
  <v-card-actions class="pb-4 px-6 d-flex justify-end ga-2">
    <!-- <v-btn variant="tonal" color="default" @click="emit('close')">
      <v-icon start>mdi-close</v-icon>
      Cancel
    </v-btn> -->
    <v-btn color="primary" variant="tonal" class="px-4" :disabled="!canSubmit || submitting" :loading="submitting"
      @click="onSubmit">
      <v-icon start>{{ activeTab === 'existing' ? 'mdi-image-check-outline' : 'mdi-cloud-upload-outline' }}</v-icon>
      {{ activeTab === 'existing' ? 'Use Existing Image' : 'Upload Image' }}
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { fileAssign, type FileAssignPayload } from '@/api/files.api';
import ImageExistingTabContent from '@/components/media/ImageExistingTabContent.vue';
import ImageUploadTabContent from '@/components/media/ImageUploadTabContent.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  usage_type?: string | null;
  usage_id?: number | string | null;
  directory?: string | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const usageType = props.usage_type ?? null;
const usageId = props.usage_id ?? null;
const directory = props.directory ?? null;
const snackbar = useSnackbarStore();
const activeTab = ref<'existing' | 'upload'>('existing');
const selectedExistingImageId = ref<number | string | null>(null);
const uploadFile = ref<File | null>(null);
const submitting = ref(false);
const existingSeo = ref({
  alt_text: '',
});
const uploadSeo = ref({
  alt_text: '',
});

function hasAltText(value: string | null | undefined): boolean {
  return String(value ?? '').trim() !== '';
}

const canSubmit = computed(() => {
  if (activeTab.value === 'existing') {
    return selectedExistingImageId.value !== null && hasAltText(existingSeo.value.alt_text);
  }
  return uploadFile.value !== null && hasAltText(uploadSeo.value.alt_text);
});

async function onSubmit() {
  const seoState = activeTab.value === 'existing' ? existingSeo.value : uploadSeo.value;
  const altText = String(seoState.alt_text ?? '').trim();

  if (!usageType || usageId === null || usageId === undefined || String(usageId).trim() === '') {
    snackbar.show({ message: 'Usage type and usage id are required.', color: 'error' });
    return;
  }

  if (activeTab.value === 'existing') {
    if (selectedExistingImageId.value === null || !hasAltText(altText)) {
      snackbar.show({ message: 'Please select an image and provide alt text.', color: 'error' });
      return;
    }
  } else if (uploadFile.value === null || !hasAltText(altText)) {
    snackbar.show({ message: 'Please upload an image and provide alt text.', color: 'error' });
    return;
  }

  const payload: FileAssignPayload =
    activeTab.value === 'existing'
      ? {
        usage_type: usageType,
        usage_id: usageId,
        directory,
        source: 'existing' as const,
        image_id: selectedExistingImageId.value,
        alt_text: altText,
      }
      : {
        usage_type: usageType,
        usage_id: usageId,
        directory,
        source: 'upload' as const,
        file: uploadFile.value,
        alt_text: altText,
      };
  // console.log({payload});
  try {
    submitting.value = true;
    const response = await fileAssign(payload);
    snackbar.show({ message: response?.message || 'Image assigned successfully.', color: 'success' });
    emit('saved', response?.data ?? response);
    emit('close');
  } catch (error: any) {
    const message =
      error?.response?.data?.message ||
      error?.message ||
      'Failed to assign image.';
    snackbar.show({ message, color: 'error' });
  } finally {
    submitting.value = false;
  }
}
</script>
