<template>
  <AppPageHeader title="Payment Methods" subtitle="Payment methods" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Method', key: 'method' },
  { title: 'Provider', key: 'provider' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ method: 'Card', provider: 'Stripe', status: 'Active' }]);
const total = ref(1);
const loading = ref(false);
</script>
