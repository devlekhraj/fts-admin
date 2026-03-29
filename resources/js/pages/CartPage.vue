<template>
  <AppPageHeader title="Cart Items" subtitle="Active carts" />
<AppDataTable :headers="headers" :items="items" :total="total" :loading="loading">
  <template #item.action="{ item }">
    <v-btn size="small" variant="tonal" color="primary" @click="onView(item)">
      View
    </v-btn>
  </template>
</AppDataTable>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import { timeAgo } from '@/shared/utils';

const headers = [
  { title: 'Customer', key: 'customer' },
  { title: 'Items', key: 'items' },
  { title: 'Updated', key: 'updatedAt' },
  { title: 'Action', key: 'action' },
];

const router = useRouter();

const items = ref([{ id: 1, customer: 'Alice', items: 3, updatedAt: timeAgo('2026-02-01') }]);
const total = ref(1);
const loading = ref(false);

function onView(item: any) {
  router.push({ name: 'admin.orders.cart.detail', params: { id: item.id } });
}
</script>
