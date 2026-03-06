<template>
  <AppPageHeader title="Files" subtitle="Browse media files" />

  <v-card class="mt-4 pa-4">
    <div class="d-flex align-center ga-3 mb-3">
      <v-text-field v-model="search" label="Search files" variant="outlined" density="comfortable" hide-details
        clearable prepend-inner-icon="mdi-magnify" class="search-input" @click:clear="onClearSearch" />
    </div>

    <div v-if="tags.length" class="d-flex flex-wrap ga-2 mb-3">
      <v-chip color="primary" label size="small" class="text-capitalize" :variant="selectedTag === null ? 'flat' : 'tonal'"
        @click="onTagClick(null)">
        All
      </v-chip>
      <v-chip v-for="tag in tags" :key="tag" size="small" label color="primary" class="text-capitalize"
        :variant="selectedTag === tag ? 'flat' : 'tonal'" @click="onTagClick(tag)">
        {{ dashToSpace(tag) }}
      </v-chip>
    </div>

    <v-data-table-server :headers="headers" :items="items" :items-length="total" :loading="loading" item-value="id"
      v-model:expanded="expandedRows" :page="options.page" :items-per-page="options.itemsPerPage"
      @update:options="(opts) => onOptions(opts as DataTableOptions)">
      <template #item.preview="{ item }">
        <div class="table-image-preview rounded">
          <v-img v-if="item.url" :src="String(item.url)" contain />
          <div v-else class="d-flex align-center justify-center h-100">
            <v-icon size="18" color="grey-darken-1">mdi-image-outline</v-icon>
          </div>
        </div>
      </template>

      <template #item.file_name="{ item }">
        <span>{{ item.file_name || item.title || `File #${item.id}` }}</span>
        <div class="text-caption text-medium-emphasis">
          {{ formatBytes(item.file_size ?? item.size) }} | {{ Number(item.width ?? 0) }} x
          {{ Number(item.height ?? 0) }}
        </div>
      </template>

      <template #item.tags="{ item }">
        <div v-if="Array.isArray(item.tags) && item.tags.length" class="d-flex flex-wrap ga-1">
          <v-chip v-for="tag in item.tags" :key="String(tag)" size="small" class="text-capitalize" label variant="tonal"
            color="primary">
            {{ dashToSpace(String(tag)) }}
          </v-chip>
        </div>
        <span v-else>-</span>
      </template>

      <template #item.usages="{ item }">
        <div class="d-flex align-center ga-1 flex-wrap">
          <v-chip size="small" label variant="tonal" color="info" class="usage-count-chip"
            @click="toggleUsageDetails(item)">
            {{ Number(item.usage_count ?? 0) }}
            <v-icon end size="14">{{ isUsageExpanded(item) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
          </v-chip>
          <v-chip v-for="usageType in (Array.isArray(item.usage_types) ? item.usage_types : []).slice(0, 2)"
            :key="String(usageType)" size="small" label variant="tonal" color="primary" class="text-lowercase">
            {{ dashToSpace(String(usageType)) }}
          </v-chip>
        </div>
      </template>

      <template #item.created_at="{ item }">
        <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
      </template>

      <template #item.action="{ item }">
        <v-btn v-if="item.url" :href="String(item.url)" target="_blank" rel="noopener noreferrer" icon size="x-small"
          variant="tonal" color="primary">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
        <v-btn v-else icon size="x-small" variant="tonal" color="primary" disabled>
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
      </template>

      <template #expanded-row="{ item, columns }">
        <tr>
          <td :colspan="columns.length">
            <v-container>
              <v-col cols="12" md="8" offset-md="2">
                <div>
                  <div class="usage-details-box my-2">
                    <template v-if="Array.isArray(item.usages) && item.usages.length">
                      
                      <div v-for="usage in item.usages" :key="String(usage.id)" class="usage-details-item border rounded pa-4">
                      
                        <div class="text-caption">
                          Usage Type: {{ usage.usage_type || '-' }}
                        </div>
                        <div class="text-caption text-medium-emphasis text-truncate">
                          Usage ID: {{ usage.usage_id ?? '-' }}
                        </div>
                        <div class="text-caption text-medium-emphasis text-truncate">
                          Title: {{ usage.title || '-' }}
                        </div>
                        <div class="text-caption text-medium-emphasis text-truncate">
                          Alt Text: {{ usage.alt_text || '-' }}
                        </div>
                      </div>
                    </template>
                    <div v-else class="text-caption text-medium-emphasis">No usage details found.</div>
                  </div>
                </div>
              </v-col>
            </v-container>
          </td>
        </tr>
      </template>
    </v-data-table-server>
  </v-card>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { listFilesWithUsages, type FileListItem } from '@/api/files.api';
import AppPageHeader from '@/components/AppPageHeader.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { dashToSpace, formatBytes, formatLongDate } from '@/shared/utils';

const headers = [
  { title: 'Image', key: 'preview', sortable: false, minWidth: '120' },
  { title: 'Name', key: 'file_name', sortable: false, minWidth: '340' },
  { title: 'Tags', key: 'tags', sortable: false, minWidth: '170' },
  { title: 'Usages', key: 'usages', sortable: false, minWidth: '180' },
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '170' },
  { title: 'Action', key: 'action', sortable: false, minWidth: '90' },
];

const items = ref<FileListItem[]>([]);
const total = ref(0);
const loading = ref(false);
const tags = ref<string[]>([]);
const selectedTag = ref<string | null>(null);
const search = ref('');
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 20,
  sortBy: [],
});
const expandedRows = ref<Array<string | number>>([]);
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;
const skipNextSearchWatch = ref(false);

async function fetchFiles() {
  loading.value = true;
  try {
    const response = await listFilesWithUsages({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
      tag: selectedTag.value || undefined,
    });
    items.value = Array.isArray(response?.data) ? response.data : [];
    if (expandedRows.value.length) {
      const allowedIds = new Set(items.value.map((entry) => String(entry.id)));
      expandedRows.value = expandedRows.value.filter((id) => allowedIds.has(String(id)));
    }
    total.value = Number(response?.meta?.total ?? items.value.length);
    tags.value = Array.isArray(response?.tags) ? response.tags : [];
    if (response?.meta?.current_page) {
      options.value.page = Number(response.meta.current_page);
    }
    if (response?.meta?.per_page) {
      options.value.itemsPerPage = Number(response.meta.per_page);
    }
  } finally {
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  fetchFiles();
}

function onTagClick(tag: string | null) {
  selectedTag.value = tag;
  options.value.page = 1;
  fetchFiles();
}

function onClearSearch() {
  skipNextSearchWatch.value = true;
  search.value = '';
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
  options.value.page = 1;
  fetchFiles();
}

function isUsageExpanded(item: FileListItem): boolean {
  return expandedRows.value.some((id) => String(id) === String(item.id));
}

function toggleUsageDetails(item: FileListItem) {
  const id = item.id;
  if (expandedRows.value.some((value) => String(value) === String(id))) {
    expandedRows.value = [];
  } else {
    expandedRows.value = [id];
  }
}

watch(search, () => {
  if (skipNextSearchWatch.value) {
    skipNextSearchWatch.value = false;
    return;
  }
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
  searchDebounceTimer = setTimeout(() => {
    options.value.page = 1;
    fetchFiles();
  }, 350);
});

onMounted(fetchFiles);
onBeforeUnmount(() => {
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
});
</script>

<style scoped>
.search-input {
  max-width: 420px;
}

.table-image-preview {
  width: 92px;
  height: 58px;
  overflow: hidden;
}

.usage-count-chip {
  cursor: pointer;
}

.usage-details-box {
  border-radius: 8px;
  padding: 8px;
  overflow: auto;
}

.usage-details-item+.usage-details-item {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px dashed rgb(var(--v-theme-outline-variant));
}
</style>
