<template>
  <v-row>
    <v-col cols="12" md="6">
      <v-text-field
        v-model="searchQuery"
        label="Search images"
        variant="outlined"
        density="comfortable"
        prepend-inner-icon="mdi-magnify"
        clearable
        @click:clear="onClearSearch"
        hide-details
        class="my-2" />

      <div v-if="tags.length" class="d-flex flex-wrap ga-2 mb-2">
        <v-chip
          class="text-capitalize"
          size="small"
          :variant="selectedTag === null ? 'flat' : 'tonal'"
          color="primary"
          :ripple="true"
          @click="onTagClick(null)"
          label>
          All
        </v-chip>
        <v-chip
          v-for="tag in tags"
          :key="tag"
          class="text-capitalize"
          size="small"
          :variant="selectedTag === tag ? 'flat' : 'tonal'"
          color="primary"
          :ripple="true"
          @click="onTagClick(tag)"
          label>
          {{ dashToSpace(tag) }}
        </v-chip>
      </div>

      <div class="image-list-scroll">
        <template v-if="loading">
          <div class="existing-loading-state">
            <v-progress-circular indeterminate size="24" width="3" color="primary" />
            <div class="text-body-2 text-medium-emphasis mt-2">Loading images...</div>
          </div>
        </template>
        <template v-else-if="loadError">
          <div class="text-body-2 text-error">{{ loadError }}</div>
        </template>
        <template v-else-if="images.length">
          <v-row>
            <v-col
              v-for="image in images"
              :key="String(image.id)"
              cols="12"
              sm="6"
              md="6">
              <v-card
                class="existing-image-card border"
                :class="{ 'existing-image-card--active': String(selectedImageIdModel) === String(image.id) }"
                variant="outlined"
                @click="selectedImageIdModel = image.id">
                <v-img
                  v-if="image.url"
                  :src="image.url"
                  contain
                  height="130" />
                <div v-else class="d-flex align-center justify-center existing-image-placeholder">
                  <v-icon size="24" color="grey-darken-1">mdi-image-off-outline</v-icon>
                </div>
                <v-card-text class="py-2 px-3">
                  <div class="text-caption text-truncate">
                    {{ image.file_name || `Image #${image.id}` }}
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </template>
        <div v-else class="existing-empty-state">
          <v-icon size="34" color="grey-darken-1">mdi-image-search-outline</v-icon>
          <div class="text-body-2 text-medium-emphasis mt-2">
            No images found for the current filter.
          </div>
          <div class="text-caption text-medium-emphasis">
            Try another search term or choose a different tag.
          </div>
        </div>

        <div v-if="!loading && !loadError && hasMorePages" class="d-flex justify-center mt-3">
          <v-btn
            variant="tonal"
            color="primary"
            :loading="loadingMore"
            :disabled="loadingMore"
            @click="onLoadMore">
            <v-icon start size="16">mdi-chevron-double-down</v-icon>
            Load More
          </v-btn>
        </div>
      </div>
    </v-col>

    <v-col cols="12" md="6">
      <v-card class="mb-4">
        <v-card-text class="border rounded">
          <template v-if="selectedImageInfo">
            <div>
              <div class="selected-image-preview">
                <v-img
                  v-if="selectedImageInfo.previewUrl"
                  :src="selectedImageInfo.previewUrl"
                  contain
                  height="200"
                  class="rounded" />
                <div v-else class="d-flex align-center justify-center selected-image-preview-placeholder">
                  <v-icon size="26" color="grey-darken-1">mdi-image-outline</v-icon>
                </div>
              </div>
              <div class="text-caption text-medium-emphasis mt-2 selected-image-info">
                <div>Name: {{ selectedImageInfo.name }}</div>
                <div>Size: {{ selectedImageInfo.size }}</div>
                <div>Dimension: {{ selectedImageInfo.dimension }}</div>
                <!-- <div>Extension: {{ selectedImageInfo.extension }}</div> -->
              </div>
            </div>
          </template>
          <div v-else class="text-caption text-medium-emphasis text-center">
            <v-icon size="64" color="grey-darken-1" class="mb-2">mdi-image-outline</v-icon>
            <div>{{ emptySelectionText }}</div>
          </div>
        </v-card-text>
      </v-card>

      <div class="text-subtitle-2 mb-3">{{ seoTitle }}</div>
      <v-textarea
        v-model="altTextModel"
        label="Alt Text"
        variant="outlined"
        density="comfortable"
        auto-grow
        rows="2"
        />
      <v-textarea
        v-model="captionModel"
        label="Caption"
        variant="outlined"
        density="comfortable"
        auto-grow
        rows="2"
        
        class="mt-2" />
      <v-textarea
        v-model="descriptionModel"
        label="Description"
        variant="outlined"
        density="comfortable"
        auto-grow
        rows="5"
        hide-details
        class="mt-2" />
    </v-col>
  </v-row>
</template>

<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { listFiles } from '@/api/files.api';
import { dashToSpace, formatBytes } from '@/shared/utils';

type ExistingImage = {
  id: number | string;
  file_name?: string | null;
  url?: string | null;
  title?: string | null;
  width?: number | null;
  height?: number | null;
  file_size?: number | null;
  size?: number | null;
};

type SeoState = {
  alt_text: string;
  caption: string;
  description: string;
};

const props = withDefaults(defineProps<{
  selectedImageId: number | string | null;
  seo: SeoState;
  seoTitle?: string;
  emptyListText?: string;
  emptySelectionText?: string;
}>(), {
  seoTitle: 'SEO Information',
  emptyListText: 'No existing image available.',
  emptySelectionText: 'Select an existing image to view details.',
});

const emit = defineEmits<{
  (e: 'update:selectedImageId', value: number | string | null): void;
  (e: 'update:seo', value: SeoState): void;
}>();

const selectedImageIdModel = computed({
  get: () => props.selectedImageId,
  set: (value: number | string | null) => emit('update:selectedImageId', value),
});

const images = ref<ExistingImage[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const loadError = ref('');
const searchQuery = ref('');
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;
const skipNextSearchWatch = ref(false);
const currentPage = ref(1);
const hasMorePages = ref(false);
const tags = ref<string[]>([]);
const selectedTag = ref<string | null>(null);

const selectedImage = computed(() =>
  images.value.find((image) => String(image.id) === String(props.selectedImageId)) ?? null,
);

const selectedImageInfo = computed(() => {
  if (!selectedImage.value) return null;
  const image = selectedImage.value;
  return {
    previewUrl: image.url ?? '',
    name: image.file_name || `Image #${image.id}`,
    size: formatBytes(image.file_size ?? image.size ?? 0),
    dimension:
      Number(image.width ?? 0) > 0 && Number(image.height ?? 0) > 0
        ? `${Number(image.width)} x ${Number(image.height)}`
        : '-',
    extension: getFileExtension(image.url ?? image.title ?? ''),
  };
});

const altTextModel = computed({
  get: () => props.seo.alt_text,
  set: (value: string) => emit('update:seo', { ...props.seo, alt_text: value }),
});

const captionModel = computed({
  get: () => props.seo.caption,
  set: (value: string) => emit('update:seo', { ...props.seo, caption: value }),
});

const descriptionModel = computed({
  get: () => props.seo.description,
  set: (value: string) => emit('update:seo', { ...props.seo, description: value }),
});

function getFileExtension(value: string): string {
  const cleaned = String(value ?? '').trim();
  if (!cleaned) return '-';
  const withoutQuery = cleaned.split('?')[0].split('#')[0];
  const filename = withoutQuery.split('/').pop() ?? withoutQuery;
  const lastDotIndex = filename.lastIndexOf('.');
  if (lastDotIndex <= 0 || lastDotIndex === filename.length - 1) return '-';
  return filename.slice(lastDotIndex + 1).toUpperCase();
}

async function fetchImages(page = 1, append = false) {
  if (append) {
    loadingMore.value = true;
  } else {
    loading.value = true;
  }
  loadError.value = '';
  try {
    const response = await listFiles({
      page,
      per_page: 24,
      search: searchQuery.value.trim() || undefined,
      tag: selectedTag.value ?? undefined,
    });
    const list = Array.isArray(response?.data) ? response.data : [];
    images.value = append ? [...images.value, ...(list as ExistingImage[])] : (list as ExistingImage[]);
    if (!append) {
      tags.value = Array.isArray(response?.tags) ? response.tags : [];
    }

    const current = Number(response?.meta?.current_page ?? page);
    const last = Number(response?.meta?.last_page ?? current);
    currentPage.value = current;
    hasMorePages.value = current < last;
  } catch (error) {
    loadError.value = 'Failed to load images.';
    if ((window as any)?.console) {
      console.error(error);
    }
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
}

onMounted(fetchImages);

watch(searchQuery, () => {
  if (skipNextSearchWatch.value) {
    skipNextSearchWatch.value = false;
    return;
  }
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
  searchDebounceTimer = setTimeout(() => {
    currentPage.value = 1;
    hasMorePages.value = false;
    fetchImages(1, false);
  }, 350);
});

onBeforeUnmount(() => {
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
});

function onClearSearch() {
  selectedTag.value = null;
  skipNextSearchWatch.value = true;
  searchQuery.value = '';
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
  currentPage.value = 1;
  hasMorePages.value = false;
  fetchImages(1, false);
}

function onLoadMore() {
  if (loadingMore.value || !hasMorePages.value) return;
  fetchImages(currentPage.value + 1, true);
}

function onTagClick(tag: string | null) {
  if (tag === null) {
    selectedTag.value = null;
  } else {
    const normalized = String(tag).trim();
    if (!normalized) return;
    selectedTag.value = selectedTag.value === normalized ? null : normalized;
  }

  skipNextSearchWatch.value = true;
  searchQuery.value = '';
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
  currentPage.value = 1;
  hasMorePages.value = false;
  fetchImages(1, false);
}
</script>

<style scoped>
.existing-image-card {
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.existing-image-card--active {
  /* border-color: rgb(var(--v-theme-primary)); */
  box-shadow: 0 0 0 1px rgb(var(--v-theme-primary));
}

.existing-image-placeholder {
  height: 130px;
  background: rgb(var(--v-theme-surface-variant));
}

.selected-image-preview {
  /* width: 100%;
  border: 1px solid rgb(var(--v-theme-outline-variant));
  overflow: hidden;
  max-height: 200px;
  object-fit: contain; */
  max-height: 200px;
}

.selected-image-preview-placeholder {
  height: 180px;
  background: rgb(var(--v-theme-surface-variant));
}

.selected-image-info {
  min-width: 0;
}

.image-list-scroll {
  min-height: 54vh;
  max-height: 54vh;
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 6px;
}

.existing-empty-state {
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 16px;
}

.existing-loading-state {
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 16px;
}
</style>
