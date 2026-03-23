<template>
  <BaseDetailTabImages
    :files="campaignFiles"
    usage-type="campaigns"
    :usage-id="item?.id ?? null"
    directory="campaigns"
    :edit-modal="CampaignImageEditModel"
    edit-modal-title="Edit Campaign Image"
    edit-button-text="Edit Image"
    empty-state-message="Add an image to get started."
    @updated="emit('changed')"
  >
    <template #details="{ file }">
      <div class="d-flex align-center ga-2">
        <div class="text-body-2 font-weight-medium">{{ file.alt_text || '-' }}</div>
        <v-chip v-if="file.meta?.is_default" size="x-small" label color="success" class="text-uppercase font-weight-bold"
          style="border-radius: 4px;">Primary Image</v-chip>
      </div>
      <div class="text-caption text-medium-emphasis mt-1">
        {{ String(file.size_info ?? '').trim() || '-' }}
      </div>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Campaign } from '@/types/models';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import CampaignImageEditModel from './modal/CampaignImageEditModel.vue';

const props = defineProps<{
  item: Campaign | null;
}>();

const emit = defineEmits<{
  (e: 'changed'): void;
}>();

const campaignFiles = computed(() => props.item?.files ?? []);
</script>
