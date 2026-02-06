<template>
  <!-- <AppPageHeader title="EMI Applications" subtitle="Manage EMI applications" /> -->
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Applicant', key: 'applicant' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ applicant: 'John Doe', amount: '$1,200', status: 'Pending' }]);
const total = ref(1);
const loading = ref(false);
</script>
