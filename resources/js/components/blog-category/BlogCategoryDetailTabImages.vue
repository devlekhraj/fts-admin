<template>
  <BaseDetailTabImages
    :files="categoryFiles"
    usage-type="blog_categories"
    :usage-id="item?.id ?? null"
    directory="blog-category"
    :edit-modal="BlogCategoryImageEditModal"
    :edit-modal-props="(file) => ({ categoryId: item?.id ?? null, file })"
    edit-modal-title="Edit Blog Category Image"
    empty-state-message="No files attached to this category."
    show-file-id
    @updated="emit('changed')"
  >
    <template #headers>
      <th>Status</th>
      <th>Primary Image</th>
    </template>
    <template #rows="{ file }">
      <td class="py-3">
        <v-chip
          size="small"
          label
          variant="tonal"
          :color="file.meta?.is_active ? 'success' : 'warning'">
          {{ file.meta?.is_active ? 'Active' : 'Inactive' }}
        </v-chip>
      </td>
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
import { type BlogCategoryDetailResponse } from '@/api/blog-categories.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import BlogCategoryImageEditModal from '@/components/blog-category/BlogCategoryImageEditModal.vue';

const props = defineProps<{
  item: BlogCategoryDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'changed'): void;
}>();

const categoryFiles = computed(() => props.item?.files ?? []);
</script>
