<template>
  <AppPageHeader title="User Management" subtitle="Manage users" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Role', key: 'role' },
];

const items = ref([{ name: 'Admin', email: 'admin@example.com', role: 'Owner' }]);
const total = ref(1);
const loading = ref(false);
</script>
