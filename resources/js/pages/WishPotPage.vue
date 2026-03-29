<template>
  <AppPageHeader title="Wish Pot" subtitle="Wishlist items" />
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
  { title: 'Item', key: 'item' },
  { title: 'Added', key: 'addedAt' },
  { title: 'Action', key: 'action' },
];

const router = useRouter();

const items = ref([{ id: 1, customer: 'Mark', item: 'Starter Pack', addedAt: timeAgo('2026-01-25') }]);
const total = ref(1);
const loading = ref(false);

function onView(item: any) {
  router.push({ name: 'admin.orders.wish.detail', params: { id: item.id } });
}
</script>
