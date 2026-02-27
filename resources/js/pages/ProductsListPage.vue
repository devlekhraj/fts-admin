<template>
  <AppPageHeader title="Products" subtitle="Manage products">
    <template #actions>
      <v-btn color="primary" to="/products/create">Create Product</v-btn>
    </template>
  </AppPageHeader>

  <AppDataTable
    title="Products"
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
  { title: 'Price', key: 'price' },
  { title: 'Status', key: 'status' },
];

const items = ref([
  { name: 'Starter Pack', price: '$49', status: 'Active' },
]);
const total = ref(1);
const loading = ref(false);

function onOptions(_options: DataTableOptions) {
  // hook for server-side fetch
}
</script>
