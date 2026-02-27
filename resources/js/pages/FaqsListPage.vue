<template>
  <AppPageHeader title="FAQs" subtitle="FAQs Management" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';

const headers = [
  { title: 'Question', key: 'question' },
  { title: 'Status', key: 'status' },
];

const items = ref([{ question: 'How does it work?', status: 'Published' }]);
const total = ref(1);
const loading = ref(false);
</script>
