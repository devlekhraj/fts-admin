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
          <v-list-item v-for="option in exportOptions" :key="option.type" :title="option.title"
            :prepend-icon="option.icon" @click="onExport(option.type)" />
        </v-list>
      </v-menu>

      <BlogCreateButton @saved="onBlogCreated" />
    </template>
  </AppPageHeader>

  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
    :items-per-page="options.itemsPerPage" @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppTextField v-model="search" label="Search" placeholder="Search by name..."
                prepend-inner-icon="mdi-magnify" hide-details clearable style="min-width: 260px"
                @click:clear="onClearSearch" />
              <v-btn color="primary" variant="tonal" height="40">
                <v-icon start>mdi-magnify</v-icon>
                Search
              </v-btn>
            </div>
          </v-col>

          <v-col cols="12" md="6" lg="3">
            <AppSelectField :items="categoryOptions" item-title="title" item-value="value" label="Category" clearable
              hide-details @update:model-value="onCategoryChange" />
          </v-col>

          <v-spacer></v-spacer>

          <v-col cols="12" md="auto" class="text-right">
            <div class="text-medium-emphasis">
              <span class="text-primary" style="font-size: smaller;">Total: {{ total }} Items found.</span>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </template>

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
      <div class="d-flex align-center justify-end ga-1">
        <v-btn size="small" variant="flat" color="primary" @click="onView(item)">
          details
        </v-btn>
        <BlogDeleteButton :blog="item" @deleted="onBlogDeleted" />
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import PageFilter from '@/components/filters/PageFilter.vue';
import BlogCreateButton from '@/components/blog/BlogCreateButton.vue';
import BlogDeleteButton from '@/components/blog/BlogDeleteButton.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listBlogs, type BlogListItem } from '@/api/blogs.api';
import { listBlogCategories, type BlogCategoryListItem } from '@/api/blog-categories.api';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSelectField from '@/components/shared/AppSelectField.vue';

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
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120', align: 'end' as const },
];

const items = ref<Blog[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');
const selectedCategory = ref<number | string | null>(null);
const categoryOptions = ref<Array<{ title: string; value: number | string | null }>>([
  { title: 'All categories', value: null },
]);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

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

function onBlogCreated(payload?: unknown) {
  const created: any = payload ?? {};
  if (created?.id) {
    router.push({ name: 'admin.blogs.detail', params: { id: created.id } });
    return;
  }
  options.value.page = 1;
  fetchBlogs();
}

function onBlogDeleted() {
  options.value.page = 1;
  fetchBlogs();
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
      search: search.value.trim() || undefined,
      category_id: selectedCategory.value || undefined,
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

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchBlogs();
}

function onCategoryChange() {
  options.value.page = 1;
}

async function fetchCategories() {
  const response = await listBlogCategories({ per_page: 100 });
  const list = Array.isArray(response) ? response : response?.data ?? [];
  const normalized = list.map((category: BlogCategoryListItem) => ({
    title: category.title ?? 'Untitled',
    value: category.id,
  }));
  categoryOptions.value = [{ title: 'All categories', value: null }, ...normalized];
}

watch(
  search,
  (value) => {
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }
    searchDebounceTimer = setTimeout(() => {
      options.value.page = 1;
      fetchBlogs();
    }, value ? 400 : 0);
  },
  { flush: 'post' },
);

watch(selectedCategory, () => {
  options.value.page = 1;
  fetchBlogs();
});

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchCategories();
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
