<template>
  <AppPageHeader title="Wish Pot" subtitle="Wishlist items" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Customer', key: 'customer' },
  { title: 'Item', key: 'item' },
  { title: 'Added', key: 'addedAt' },
];

const items = ref([{ customer: 'Mark', item: 'Starter Pack', addedAt: '2026-01-25' }]);
const total = ref(1);
const loading = ref(false);
</script>
