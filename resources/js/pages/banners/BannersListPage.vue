<template>
  <AppPageHeader title="Banners" subtitle="Promotional banners" />

  <AppDataTable
    title="Banners"
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
  { title: 'Placement', key: 'placement' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ title: 'Hero', placement: 'Home', status: 'Active' }]);
const total = ref(1);
const loading = ref(false);

function onOptions(_options: DataTableOptions) {}
</script>
