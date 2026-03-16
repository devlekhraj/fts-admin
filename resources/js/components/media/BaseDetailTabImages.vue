<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between mb-4">
      <div class="text-body-2 text-medium-emphasis">
        Total images: {{ files.length }}
      </div>
      <v-btn color="primary" variant="tonal" @click="onAddImage">
        <v-icon start size="16">mdi-image-plus</v-icon>
        Add Image
      </v-btn>
    </div>

    <v-table v-if="files.length" density="comfortable">
      <thead>
        <tr>
          <th>Image</th>
          <th>Details</th>
          <slot name="headers"></slot>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="file in files" :key="String(file.id)">
          <td class="py-3">
            <div class="table-image-preview rounded">
              <v-img v-if="file.url" :src="String(file.url)" cover :title="String(file.url)" />
              <div v-else class="d-flex align-center justify-center h-100">
                <v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
              </div>
            </div>
          </td>
          <td class="py-3 details-col">
            <slot name="details" :file="file">
              <div v-if="showFileId" class="text-body-2 font-weight-medium">File #{{ file.id }}</div>
              <div :style="showFileId ? '' : 'font-size: 0.8rem;'">{{ file.alt_text || '-' }}</div>
              <div class="text-caption text-medium-emphasis mt-1">
                {{ String(file.size_info ?? '').trim() || '-' }}
              </div>
            </slot>
          </td>
          <slot name="rows" :file="file"></slot>
          <td class="py-3">
            <div class="d-flex align-center ga-1">
              <v-btn v-if="editModal" size="small" variant="tonal" color="primary" @click="onEditFile(file)">
                <v-icon size="16">mdi-cog</v-icon> {{ editButtonText }}
              </v-btn>
              <slot name="actions" :file="file"></slot>
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div v-else class="empty-images-state">
      <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
      <div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
      <div class="text-body-2 text-medium-emphasis">{{ emptyStateMessage }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import ImageUploadModel from '@/components/media/ImageUploadModel.vue';
import { openModal } from '@/shared/modal';
import type { Component } from 'vue';

const props = withDefaults(defineProps<{
  files: any[];
  usageType: string;
  usageId: number | string | null;
  directory?: string;
  editModal?: Component | null;
  editModalProps?: (file: any) => Record<string, any>;
  editModalTitle?: string;
  editButtonText?: string;
  emptyStateMessage?: string;
  showFileId?: boolean;
}>(), {
  directory: '',
  editModal: null,
  editModalTitle: 'Edit Image',
  editButtonText: 'Edit Image',
  emptyStateMessage: 'No files attached.',
  showFileId: false,
  editModalProps: (file: any) => ({ file })
});

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

function onEditFile(file: any) {
  if (props.editModal) {
    openModal(
      props.editModal,
      props.editModalProps(file),
      {
        title: props.editModalTitle,
        size: 'md',
        onSaved: () => {
          emit('updated');
        },
      },
    );
  }
}

function onAddImage() {
  openModal(
    ImageUploadModel,
    {
      usage_type: props.usageType,
      usage_id: props.usageId,
      directory: props.directory || props.usageType,
    },
    {
      title: `Add ${props.usageType.charAt(0).toUpperCase() + props.usageType.slice(0, -1)} Image`,
      size: 'lg',
      onSaved: () => {
        emit('updated');
      },
    },
  );
}
</script>

<style scoped>
.table-image-preview {
  width: 140px;
  height: 78px;
  overflow: hidden;
}

.details-col {
  min-width: 320px;
  max-width: 420px;
  word-break: break-word;
}

.empty-images-state {
  min-height: 220px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
</style>
