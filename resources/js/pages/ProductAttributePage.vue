<template>
  <AppPageHeader title="Attributes" subtitle="Product attributes" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Type', key: 'type' },
];

const items = ref([{ name: 'Color', type: 'Select' }]);
const total = ref(1);
const loading = ref(false);
</script>
