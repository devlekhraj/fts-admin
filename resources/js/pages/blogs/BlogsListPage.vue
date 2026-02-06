<template>
  <AppPageHeader title="Blogs" subtitle="Manage blog posts">
    <template #actions>
      <v-btn color="primary" to="/blogs/create">Create Blog</v-btn>
    </template>
  </AppPageHeader>

  <AppDataTable
    title="Blogs"
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    @update:options="onOptions"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';

const headers = [
  { title: 'Title', key: 'title' },
  { title: 'Status', key: 'status' },
  { title: 'Published', key: 'publishedAt' },
];

const items = ref([{ title: 'Launch Post', status: 'Draft', publishedAt: '-' }]);
const total = ref(1);
const loading = ref(false);

function onOptions(_options: DataTableOptions) {}
</script>
