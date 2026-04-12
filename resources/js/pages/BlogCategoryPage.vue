<template>
  <AppPageHeader title="Blog Categories" subtitle="Blog category list">
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

      <BlogCategoryCreateButton @saved="onCategoryCreated" />
    </template>
  </AppPageHeader>

  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
    :items-per-page="options.itemsPerPage" @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppTextField  label="Search" placeholder="Search by name..."
                prepend-inner-icon="mdi-magnify" hide-details clearable style="min-width: 260px"
                 />
              <v-btn color="primary" variant="tonal" height="40">
                <v-icon start>mdi-magnify</v-icon>
                Search
              </v-btn>
            </div>
          </v-col>

          <!-- <v-col cols="12" md="6" lg="3">
            <AppSelectField  item-title="title" item-value="value" label="Category" clearable
              hide-details  />
          </v-col> -->

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
          <v-img v-if="item.thumb" :src="item.thumb" :alt="item.title" class="category-thumb" />
          <v-icon v-else size="18" color="grey-darken-1">mdi-folder-outline</v-icon>
        </v-avatar>
        <span>{{ item.title }}</span>
      </div>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'error'">
        {{ item.status ? 'Active' : 'Inactive' }}
      </v-chip>
    </template>
    <template #item.created_at="{ item }">
      {{ item.created_at }}
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="onView(item)">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
        <BlogCategoryDeleteButton :category="item" @deleted="onCategoryDeleted" />
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import BlogCategoryCreateButton from '@/components/blog/BlogCategoryCreateButton.vue';
import BlogCategoryDeleteButton from '@/components/blog/BlogCategoryDeleteButton.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import {
  listBlogCategories,
  type BlogCategoryListItem,
  type BlogCategoryListPaginatedResponse,
} from '@/api/blog-categories.api';
import { formatLongDate } from '@/shared/utils';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSelectField from '@/components/shared/AppSelectField.vue';

type BlogCategory = {
  id: number | string;
  title: string;
  slug: string;
  thumb: string;
  created_at: string;
  status: boolean;
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
  { title: 'Created', key: 'created_at', sortable: false, minWidth: '170' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '140' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '120' },
];

const items = ref<BlogCategory[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

function onExport(type: ExportType) {
  // TODO: replace with real export API/download logic.
  console.log(`Export clicked: ${type}`);
}

function onView(category: BlogCategory) {
  router.push({ name: 'admin.blogCategories.detail', params: { id: category.id } });
}

function onCategoryCreated(payload?: unknown) {
  const created: any = payload ?? {};
  if (created?.id) {
    router.push({ name: 'admin.blogCategories.detail', params: { id: created.id } });
    return;
  }
  options.value.page = 1;
  fetchCategories();
}

function onCategoryDeleted() {
  options.value.page = 1;
  fetchCategories();
}

async function fetchCategories() {
  loading.value = true;
  try {
    const response = await listBlogCategories({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    });

    const isPaginated = !Array.isArray(response);
    const paginated = isPaginated ? (response as BlogCategoryListPaginatedResponse) : null;
    const list = isPaginated ? paginated?.data ?? [] : response;

    items.value = list.map((category: BlogCategoryListItem) => ({
      id: category.id,
      title: category.title ?? '-',
      slug: category.slug ?? '-',
      thumb: typeof category.thumb === 'string' ? category.thumb : '',
      created_at: formatLongDate(category.created_at) ?? '-',
      status: Boolean(category.status),
    }));

    total.value = Number(paginated?.total ?? paginated?.meta?.total ?? list.length);
    if (paginated?.meta?.current_page) {
      options.value.page = Number(paginated.meta.current_page);
    }
    if (paginated?.meta?.per_page) {
      options.value.itemsPerPage = Number(paginated.meta.per_page);
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
  fetchCategories();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchCategories();
    hasLoadedOnce.value = true;
  }
});
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
  font-size: 0.9rem;
}

:deep(.category-thumb .v-img__img) {
  object-fit: contain;
}
</style>
