<template>
  <AppPageHeader title="Blog Categories" subtitle="Manage blog categories" />

  <AppDataTable
    title="Blog Categories"
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

const items = ref([{ name: 'Guides', slug: 'guides' }]);
const total = ref(1);
const loading = ref(false);

function onOptions(_options: DataTableOptions) {}
</script>
