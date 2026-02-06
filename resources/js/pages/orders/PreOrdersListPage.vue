<template>
  <AppPageHeader title="Pre Orders" subtitle="All pre orders" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Pre-Order #', key: 'order' },
  { title: 'Customer', key: 'customer' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ order: 'PRE-2001', customer: 'John Smith', status: 'Reserved' }]);
const total = ref(1);
const loading = ref(false);
</script>
