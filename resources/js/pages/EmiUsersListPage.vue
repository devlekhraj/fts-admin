<template>
  <!-- <AppPageHeader title="EMI Users" subtitle="Manage EMI users" /> -->
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Phone', key: 'phone' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ name: 'Rahul', phone: '+91-99999-00000', status: 'Active' }]);
const total = ref(1);
const loading = ref(false);
</script>
