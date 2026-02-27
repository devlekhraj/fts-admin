<template>
  <AppPageHeader title="Cart Items" subtitle="Active carts" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Customer', key: 'customer' },
  { title: 'Items', key: 'items' },
  { title: 'Updated', key: 'updatedAt' },
];

const items = ref([{ customer: 'Alice', items: 3, updatedAt: '2026-02-01' }]);
const total = ref(1);
const loading = ref(false);
</script>
