<template>
  <div class="pa-6">
    <v-table v-if="brandFiles.length" density="comfortable">
      <thead>
        <tr>
          <th>Image</th>
          <th>Details</th>
          <th>Specs</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="file in brandFiles" :key="String(file.id)">
          <td class="py-3">
            <div class="table-image-preview rounded">
              <v-img v-if="file.url" :src="String(file.url)" cover :title="String(file.url)" />
              <div v-else class="d-flex align-center justify-center h-100">
                <v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
              </div>
            </div>
          </td>
          <td class="py-3 details-col">
            <div class="text-body-2 font-weight-medium">{{ file.title || `File #${file.id}` }}</div>
            <div class="text-caption text-medium-emphasis">{{ file.alt_text || '-' }}</div>
          </td>
          <td class="py-3 specs-col">
            <div class="text-caption"><strong>Width:</strong> {{ Number(file.width ?? 0) }} px</div>
            <div class="text-caption"><strong>Height:</strong> {{ Number(file.height ?? 0) }} px</div>
            <div class="text-caption"><strong>Size:</strong> {{ formatBytes(file.file_size ?? file.size) }}</div>
          </td>
          <td class="py-3">
            <v-btn
              v-if="file.url"
              :href="String(file.url)"
              target="_blank"
              rel="noopener noreferrer"
              icon
              size="x-small"
              variant="tonal"
              color="primary">
              <v-icon size="16">mdi-eye</v-icon>
            </v-btn>
            <v-btn v-else icon size="x-small" variant="tonal" color="primary" disabled>
              <v-icon size="16">mdi-eye</v-icon>
            </v-btn>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div v-else class="empty-images-state">
      <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
      <div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
      <div class="text-body-2 text-medium-emphasis">No files attached to this brand.</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type ProductBrandDetailResponse } from '@/api/products.api';
import { formatBytes } from '@/shared/utils';

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
}>();

const brandFiles = computed(() => props.item?.files ?? []);
</script>

<style scoped>
.table-image-preview {
  width: 140px;
  height: 78px;
  background: rgb(var(--v-theme-surface-variant));
  overflow: hidden;
}

.specs-col {
  min-width: 190px;
}

.details-col {
  min-width: 320px;
  max-width: 420px;
  word-break: break-word;
}

.empty-images-state {
  min-height: 220px;
  border: 1px dashed rgb(var(--v-theme-outline-variant));
  border-radius: 12px;
  background: rgb(var(--v-theme-surface-variant));
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
</style>
