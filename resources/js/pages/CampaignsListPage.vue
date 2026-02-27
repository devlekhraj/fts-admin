<template>
  <AppPageHeader title="Campaigns" subtitle="Marketing campaigns" />

  <AppDataTable
    title="Campaigns"
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
  { title: 'Status', key: 'status' },
  { title: 'Start Date', key: 'startDate' },
];

const items = ref([{ name: 'Spring Sale', status: 'Planned', startDate: '2026-03-01' }]);
const total = ref(1);
const loading = ref(false);

function onOptions(_options: DataTableOptions) {}
</script>
