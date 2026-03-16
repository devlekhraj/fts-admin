<template>
  <BaseDetailTabImages
    :files="bannerFiles"
    usage-type="banners"
    :usage-id="item?.id ?? null"
    directory="banners"
    :edit-modal="BannerImageEditModel"
    edit-modal-title="Edit Banner Image"
    edit-button-text="Edit Banner"
    empty-state-message="Add an image to get started."
    @updated="emit('changed')"
  >
    <template #details="{ file }">
      <div class="text-body-2" style="font-size: 0.8rem;">
        {{ file.alt_text || `File #${file.file_id ?? file.id}` }}
      </div>
      <div class="d-flex align-center ga-2">
        <div class="text-caption text-medium-emphasis">
          Redirect: {{ String(file.meta?.link ?? '').trim() || 'Not available' }}
        </div>
        <v-btn
          v-if="String(file.meta?.link ?? '').trim()"
          :href="String(file.meta?.link ?? '').trim()"
          target="_blank"
          rel="noopener noreferrer"
          icon
          size="x-small"
          variant="tonal"
          color="primary"
        >
          <v-icon size="14">mdi-open-in-new</v-icon>
        </v-btn>
      </div>
      <div class="text-caption text-medium-emphasis mt-2">
        {{ file.size_info }}
      </div>
    </template>

    <template #headers>
      <th>Status</th>
      <th>Banner Date</th>
    </template>

    <template #rows="{ file }">
      <td class="py-3">
        <v-chip
          size="small"
          label
          variant="tonal"
          :color="file.status === true ? 'success' : 'warning'"
        >
          {{ file.status === true ? 'Active' : 'Inactive' }}
        </v-chip>
      </td>
      <td class="py-3 meta-col">
        <div class="text-caption">
          <strong>Start Date:</strong> {{ formatLongDate(file.meta?.start_date) ?? '-' }}
        </div>
        <div class="text-caption">
          <strong>End Date:</strong> {{ formatLongDate(file.meta?.end_date) ?? '-' }}
        </div>
      </td>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BannerDetailResponse } from '@/api/banners.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import BannerImageEditModel from '@/components/banner/BannerImageEditModel.vue';
import { formatBytes, formatLongDate } from '@/shared/utils';

const props = defineProps<{
  item: BannerDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'changed'): void;
}>();

const bannerFiles = computed(() => props.item?.files ?? []);
</script>

<style scoped>
.meta-col {
  min-width: 240px;
}
</style>
