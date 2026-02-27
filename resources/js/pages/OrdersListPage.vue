<template>
  <AppPageHeader title="Orders" subtitle="All orders" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Order #', key: 'order' },
  { title: 'Customer', key: 'customer' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ order: 'ORD-1001', customer: 'Jane Doe', status: 'Paid' }]);
const total = ref(1);
const loading = ref(false);
</script>
