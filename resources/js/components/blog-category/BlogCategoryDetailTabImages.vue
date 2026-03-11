<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between mb-4">
      <div class="text-body-2 text-medium-emphasis">
        Total images: {{ categoryFiles.length }}
      </div>
      <v-btn color="primary" variant="tonal" @click="onAddImage">
        <v-icon start size="16">mdi-image-plus</v-icon>
        Add Image
      </v-btn>
    </div>

    <v-table v-if="categoryFiles.length" density="comfortable">
      <thead>
        <tr>
          <th>Image</th>
          <th>Details</th>
          <th>Status</th>
          <th>Primary Image</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="file in categoryFiles" :key="String(file.id)">
          <td class="py-3">
            <div class="table-image-preview rounded">
              <v-img v-if="file.url" :src="String(file.url)" cover :title="String(file.url)" />
              <div v-else class="d-flex align-center justify-center h-100">
                <v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
              </div>
            </div>
          </td>
          <td class="py-3 details-col">
            <div class="text-body-2 font-weight-medium">File #{{ file.id }}</div>
            <div class="text-caption text-medium-emphasis">{{ file.alt_text || '-' }}</div>
            <div class="text-caption text-medium-emphasis mt-1">
              {{ String(file.size_info ?? '').trim() || '-' }}
            </div>
          </td>
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
          <td class="py-3">
            <div class="d-flex align-center ga-1">
              <v-btn size="small" variant="tonal" color="primary" @click="onEditFile(file)">
                <v-icon size="16">mdi-cog</v-icon> Edit Image
              </v-btn>
              
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div v-else class="empty-images-state">
      <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
      <div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
      <div class="text-body-2 text-medium-emphasis">No files attached to this category.</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type BlogCategoryDetailResponse } from '@/api/blog-categories.api';
import BlogCategoryImageEditModal from '@/components/blog-category/BlogCategoryImageEditModal.vue';
import ImageUploadModel from '@/components/media/ImageUploadModel.vue';
import { openModal } from '@/shared/modal';

const props = defineProps<{
  item: BlogCategoryDetailResponse | null;
}>();
const emit = defineEmits<{
  (e: 'changed'): void;
}>();

const categoryFiles = computed(() => props.item?.files ?? []);

function onEditFile(file: NonNullable<BlogCategoryDetailResponse['files']>[number]) {
  openModal(
    BlogCategoryImageEditModal,
    {
      categoryId: props.item?.id ?? null,
      file,
    },
    {
      title: 'Edit Blog Category Image',
      size: 'md',
      onSaved: () => {
        emit('changed');
      },
    },
  );
}

function onAddImage() {
  openModal(
    ImageUploadModel,
    {
      usage_type: 'blog_categories',
      usage_id: props.item?.id ?? null,
      directory: 'blog-category',
    },
    {
      title: 'Add Blog Category Image',
      size: 'lg',
      onSaved: () => {
        emit('changed');
      },
    },
  );
}
</script>

<style scoped>
.table-image-preview {
  width: 140px;
  height: 78px;
  background: rgb(var(--v-theme-surface-variant));
  overflow: hidden;
}

.details-col {
  min-width: 320px;
  max-width: 420px;
  word-break: break-word;
}

.empty-images-state {
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
</style>
