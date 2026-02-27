<template>
  <AppPageHeader title="Categories" subtitle="Manage categories" />

  <AppDataTable
    title="Categories"
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
  { title: 'Name', key: 'name' },
  { title: 'Slug', key: 'slug' },
];

const items = ref([{ name: 'News', slug: 'news' }]);
const total = ref(1);
const loading = ref(false);

function onOptions(_options: DataTableOptions) {}
</script>
