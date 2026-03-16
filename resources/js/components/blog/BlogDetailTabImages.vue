<template>
  <BaseDetailTabImages
    :files="blogFiles"
    usage-type="blogs"
    :usage-id="item?.id ?? null"
    directory="blogs"
    :edit-modal="BlogImageEditModal"
    :edit-modal-props="(file) => ({ blogId: item?.id ?? null, file })"
    edit-modal-title="Edit Blog Image"
    empty-state-message="No files attached to this blog."
    @updated="emit('updated')"
  >
    <template #headers>
      <th>Primary Image</th>
    </template>
    <template #rows="{ file }">
      <td class="py-3">
        <v-chip
          size="small"
          label
          variant="tonal"
          :color="file.meta?.is_default ? 'primary' : 'default'">
          {{ file.meta?.is_default ? 'Yes' : 'No' }}
        </v-chip>
      </td>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type BlogDetailResponse } from '@/api/blogs.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import BlogImageEditModal from '@/components/blog/BlogImageEditModal.vue';

const props = defineProps<{
  item: BlogDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const blogFiles = computed(() => props.item?.files ?? []);
</script>
