<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between mb-4">
      <div>
        <!-- <div class="text-h6">Banner Images</div> -->
        <div class="text-body-2 text-medium-emphasis">
          Total images: {{ bannerFiles.length }}
        </div>
      </div>
      <v-btn color="primary" variant="tonal" @click="onAddImage">
        <v-icon start size="16">mdi-image-plus</v-icon>
        Add Image
      </v-btn>
    </div>

    <v-table v-if="bannerFiles.length" density="comfortable">
      <thead>
        <tr>
          <th>Image</th>
          <th>Details</th>
          <th>Specs</th>
          <th>Meta Info</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="file in bannerFiles" :key="String(file.id)">
          <td class="py-3">
            <div class="table-image-preview rounded">
              <v-img v-if="file.url" :src="file.url" cover :title="file.url || undefined" />
              <div v-else class="d-flex align-center justify-center h-100">
                <v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
              </div>
            </div>
          </td>
          <td class="py-3 details-col">
            <div class="text-body-2 font-weight-medium">{{ file.title || `File #${file.id}` }}</div>
            <div class="d-flex align-center ga-2">
              <div class="text-caption text-medium-emphasis">{{ getDisplayLink(file.meta, file.url) }}</div>
              <v-btn
                v-if="getDisplayLink(file.meta, file.url) !== '-'"
                :href="getDisplayLink(file.meta, file.url)"
                target="_blank"
                rel="noopener noreferrer"
                icon
                size="x-small"
                variant="tonal"
                color="primary">
                <v-icon size="14">mdi-open-in-new</v-icon>
              </v-btn>
            </div>
          </td>
          <td class="py-3 specs-col">
            <div class="text-caption"><strong>Width:</strong> {{ Number(file.width ?? 0) }} px</div>
            <div class="text-caption"><strong>Height:</strong> {{ Number(file.height ?? 0) }} px</div>
            <div class="text-caption"><strong>Size:</strong> {{ formatBytes(file.file_size ?? file.size) }}</div>
          </td>
          <td class="py-3 meta-col">
            <template v-if="file.meta && Object.keys(file.meta).length">
              <div v-for="(value, key) in file.meta" :key="String(key)" v-show="String(key) !== 'link'" class="text-caption">
                <strong class="text-capitalize">{{ underscoreToSpace(key) }}:</strong> {{ formatMetaValue(key, value) }}
              </div>
            </template>
            <span v-else class="text-caption text-medium-emphasis">-</span>
          </td>
          <td class="py-3">
            <div class="d-flex align-center ga-1">
              <v-btn
                v-if="file.url"
                :href="file.url"
                target="_blank"
                rel="noopener noreferrer"
                icon
                size="x-small"
                variant="tonal"
                color="primary">
                <v-icon size="16">mdi-eye</v-icon>
              </v-btn>
              <v-btn
                v-else
                icon
                size="x-small"
                variant="tonal"
                color="primary"
                disabled>
                <v-icon size="16">mdi-eye</v-icon>
              </v-btn>
              <v-btn
                icon
                size="x-small"
                variant="tonal"
                color="error"
                @click="onDeleteFile(file.id)">
                <v-icon size="16">mdi-delete</v-icon>
              </v-btn>
            </div>
          </td>
        </tr>
      </tbody>
    </v-table>
    <div v-else class="empty-images-state">
      <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
      <div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
      <div class="text-body-2 text-medium-emphasis">Add an image to get started.</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BannerDetailResponse } from '@/api/banners.api';
import { formatBytes, formatLongDate, underscoreToSpace } from '@/shared/utils';

const props = defineProps<{
  item: BannerDetailResponse | null;
}>();

const bannerFiles = computed(() => props.item?.files ?? []);

function onDeleteFile(fileId: number | string) {
  // TODO: replace with delete confirmation + API call.
  console.log('Delete banner file:', fileId);
}

function onAddImage() {
  // TODO: replace with add image flow.
  console.log('Add banner image');
}

function getDisplayLink(meta: Record<string, unknown> | undefined, fileUrl?: string | null): string {
  const link = meta?.link;
  if (typeof link === 'string' && link.trim()) return link;
  if (typeof fileUrl === 'string' && fileUrl.trim()) return fileUrl;
  return '-';
}

function formatMetaValue(key: string | number, value: unknown): string {
  const normalizedKey = String(key);
  if ((normalizedKey === 'start_date' || normalizedKey === 'end_date') && value) {
    return formatLongDate(value) ?? String(value);
  }
  return value === null || value === undefined || value === '' ? '-' : String(value);
}
</script>

<style scoped>
.table-image-preview {
  width: 140px;
  height: 78px;
  background: rgb(var(--v-theme-surface-variant));
  overflow: hidden;
}

.meta-col{
  min-width: 240px;
}
.specs-col {
  min-width: 190px;
}

.details-col {
  min-width: 400px;
  max-width: 400px;
  word-break: break-word;
}

.empty-images-state {
  min-height: 220px;
  /* border: 1px dashed rgb(var(--v-theme-outline-variant)); */
  /* border-radius: 12px; */
  /* background: rgb(var(--v-theme-surface-variant)); */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
</style>
