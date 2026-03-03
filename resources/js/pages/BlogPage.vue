<template>
  <AppPageHeader title="Blogs" subtitle="Blog list">
    <template #actions>
      <v-menu location="bottom start">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="tonal" color="primary" prepend-icon="mdi-download-outline">
            Export
          </v-btn>
        </template>
        <v-list class="export-menu-list" density="comfortable" min-width="170">
          <v-list-item
            v-for="option in exportOptions"
            :key="option.type"
            :title="option.title"
            :prepend-icon="option.icon"
            @click="onExport(option.type)" />
        </v-list>
      </v-menu>

      <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" :to="{ name: 'admin.blogs.create' }">
        Create Blog
      </v-btn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    @update:options="onOptions">
    <template #item.title="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.thumb" :src="item.thumb" :alt="item.title" class="blog-thumb" />
          <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
        <span>{{ item.title }}</span>
      </div>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'error'">
        {{ item.status ? 'Active' : 'Inactive' }}
      </v-chip>
    </template>
    <template #item.published_at="{ item }">
      {{ formatDate(item.published_at) }}
    </template>
    <template #item.category_name="{ item }">
      <span class="category-label" :style="{ color: `rgb(var(--v-theme-${categoryColor(item.category_name)}))` }">
        <span class="category-dot" />
        <span>{{ item.category_name || 'Uncategorized' }}</span>
      </span>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="onView(item)">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
        <v-btn icon size="x-small" variant="tonal" color="error" @click="onDelete(item)">
          <v-icon size="16">mdi-delete</v-icon>
        </v-btn>
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listBlogs, type BlogListItem } from '@/api/blogs.api';

type Blog = {
  id: number | string;
  title: string;
  slug: string;
  status: boolean;
  published_at: string;
  category_name: string;
  thumb: string;
};

type ExportType = 'csv' | 'excel' | 'pdf';

const exportOptions: Array<{ type: ExportType; title: string; icon: string }> = [
  { type: 'csv', title: 'Export CSV', icon: 'mdi-file-delimited-outline' },
  { type: 'excel', title: 'Export Excel', icon: 'mdi-microsoft-excel' },
  { type: 'pdf', title: 'Export PDF', icon: 'mdi-file-pdf-box' },
];

const headers = [
  { title: 'Title', key: 'title', sortable: false, minWidth: '260' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '220' },
  { title: 'Category', key: 'category_name', sortable: false, minWidth: '180' },
  { title: 'Published', key: 'published_at', sortable: false, minWidth: '170' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

const items = ref<Blog[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

const CATEGORY_COLORS: Record<string, string> = {
  news: 'primary',
  mobile: 'success',
  laptop: 'warning',
  default: 'info',
};

function onExport(type: ExportType) {
  // TODO: replace with real export API/download logic.
  console.log(`Export clicked: ${type}`);
}

function onView(blog: Blog) {
  router.push({ name: 'admin.blogs.detail', params: { id: blog.id } });
}

function onDelete(blog: Blog) {
  // TODO: replace with delete confirmation + API call.
  console.log('Delete blog:', blog.slug);
}

function categoryColor(categoryName: string) {
  const key = (categoryName || '').trim().toLowerCase();
  if (!key) return CATEGORY_COLORS.default;
  return CATEGORY_COLORS[key] ?? CATEGORY_COLORS.default;
}

function formatDate(value: string) {
  if (!value) {
    return '-';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  }).format(date);
}

async function fetchBlogs() {
  loading.value = true;
  try {
    const response = await listBlogs({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((blog: BlogListItem) => ({
      id: blog.id,
      title: blog.title ?? '-',
      slug: blog.slug ?? '-',
      status: Boolean(blog.status),
      published_at: typeof blog.published_at === 'string' ? blog.published_at : '',
      category_name: blog.category_name ? String(blog.category_name) : '-',
      thumb: typeof blog.thumb === 'string' ? blog.thumb : '',
    }));
    total.value = Number(response?.total ?? response?.meta?.total ?? list.length);
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
  if (!hasLoadedOnce.value) {
    hasLoadedOnce.value = true;
  }
  fetchBlogs();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchBlogs();
    hasLoadedOnce.value = true;
  }
});
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.blog-thumb .v-img__img) {
  object-fit: contain;
}

.category-dot {
  width: 8px;
  height: 8px;
  border-radius: 9999px;
  background-color: currentColor;
  display: inline-block;
}

.category-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}
</style>
