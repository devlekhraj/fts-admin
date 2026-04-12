<template>
  <BaseDetailTabImages
    :files="files"
    usage-type="logo"
    usage-id="1"
    directory="logo"
    :edit-modal="LogoImageEditModal"
    :edit-modal-props="editModalProps"
    edit-modal-title="Edit Logo Metadata"
    empty-state-message="No logo images found."
    @updated="fetchFiles"
  >
    <template #headers>
      <th>Label / Type</th>
    </template>
    <template #rows="{ file }">
      <td class="py-3">
        <v-chip
          size="small"
          label
          variant="tonal"
          color="primary">
          {{ file.title || 'N/A' }}
        </v-chip>
      </td>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import LogoImageEditModal from '@/components/settings/LogoImageEditModal.vue';
import { getLogoImages } from '@/api/files.api';
import { computed } from 'vue';

const files = ref<any[]>([]);
const loading = ref(false);

const usedTitles = computed(() =>
  files.value
    .map((f) => String(f.title ?? '').trim())
    .filter((t) => t.length > 0),
);

const editModalProps = (file: any) => ({
  file,
  usedTitles: usedTitles.value,
});

async function fetchFiles() {
  loading.value = true;
  try {
    const response = await getLogoImages();
    files.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch logo files:', error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchFiles();
});
</script>
