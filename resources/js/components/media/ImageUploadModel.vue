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
    <v-btn color="primary" variant="tonal" class="px-4" :disabled="!canSubmit" @click="onSubmit">
      <v-icon start>{{ activeTab === 'existing' ? 'mdi-image-check-outline' : 'mdi-cloud-upload-outline' }}</v-icon>
      {{ activeTab === 'existing' ? 'Use Existing Image' : 'Upload Image' }}
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import ImageExistingTabContent from '@/components/media/ImageExistingTabContent.vue';
import ImageUploadTabContent from '@/components/media/ImageUploadTabContent.vue';

const props = defineProps<{
  usage_type?: string | null;
  usage_id?: number | string | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const usageType = props.usage_type ?? null;
const usageId = props.usage_id ?? null;
const activeTab = ref<'existing' | 'upload'>('existing');
const selectedExistingImageId = ref<number | string | null>(null);
const uploadFile = ref<File | null>(null);
const existingSeo = ref({
  alt_text: '',
  caption: '',
  description: '',
});
const uploadSeo = ref({
  alt_text: '',
  caption: '',
  description: '',
});

const canSubmit = computed(() => {
  if (activeTab.value === 'existing') {
    return selectedExistingImageId.value !== null;
  }
  return uploadFile.value !== null;
});

function onSubmit() {
  const seoState = activeTab.value === 'existing' ? existingSeo.value : uploadSeo.value;
  const payload =
    activeTab.value === 'existing'
      ? {
        usage_type: usageType,
        usage_id: usageId,
        source: 'existing',
        image_id: selectedExistingImageId.value,
        alt_text: seoState.alt_text,
        // meta: {
        caption: seoState.caption,
        description: seoState.description,
        // },
      }
      : {
        usage_type: usageType,
        usage_id: usageId,
        source: 'upload',
        file: uploadFile.value,
        alt_text: seoState.alt_text,
        // meta: {
        caption: seoState.caption,
        description: seoState.description,
        // },
      };
  console.log({ payload });
  // emit('saved', payload);
  // emit('close');
}
</script>
