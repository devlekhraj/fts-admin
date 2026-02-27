<template>
  <!-- <AppPageHeader title="Customers" subtitle="Customer list" /> -->
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ name: 'Jane Doe', email: 'jane@example.com', status: 'Active' }]);
const total = ref(1);
const loading = ref(false);
</script>
