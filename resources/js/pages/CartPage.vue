<template>
  <AppPageHeader title="Cart Items" subtitle="Active carts" />
  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    @update:options="onOptions">
    <template #item.customer="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.avatar" :src="item.avatar" :alt="item.customer" cover />
          <v-icon v-else size="16" color="grey-darken-1">mdi-account-circle</v-icon>
        </v-avatar>
        <span>{{ item.customer ?? '-' }}</span>
      </div>
    </template>
    <template #item.items="{ item }">
      <span>{{ item.items > 0 ? `${item.items} ${item.items === 1 ? 'item' : 'items'}` : '-' }}</span>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.isProceed ? 'success' : 'warning'">
        {{ item.isProceed ? 'Completed' : 'Pending' }}
      </v-chip>
    </template>
    <template #item.updatedAt="{ item }">
      <span class="text-medium-emphasis">{{ item.updatedAt }}</span>
    </template>
    <template #item.action="{ item }">
      <v-btn size="small" variant="tonal" color="primary" @click="onView(item)">
        View
      </v-btn>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import { timeAgo } from '@/shared/utils';
import { listCarts, type CartListItem } from '@/api/carts.api';

const headers = [
  { title: 'Customer', key: 'customer' ,minWidth:'250'},
  { title: 'Items', key: 'items',minWidth:'150' },
  { title: 'Status', key: 'status', minWidth:'120' },
  { title: 'Updated', key: 'updatedAt', minWidth:'150' },
  { title: 'Action', key: 'action', width:'120' },
];

const router = useRouter();

const items = ref<{ id: number | string; customer: string; avatar?: string | null; items: number; updatedAt: string; isProceed: boolean }[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref({ page: 1, itemsPerPage: 10 });

function onView(item: any) {
  router.push({ name: 'admin.orders.cart.detail', params: { id: item.id } });
}

async function fetchCarts() {
  loading.value = true;
  try {
    const response = await listCarts({ page: options.value.page, per_page: options.value.itemsPerPage });
    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list.map((cart: CartListItem) => ({
      id: cart.id,
      customer: cart.customer?.name ?? '-',
      avatar: cart.customer?.avatar ?? null,
      items: Number(cart.items_count ?? 0),
      updatedAt: cart.updated_at ? timeAgo(cart.updated_at) : '-',
      isProceed: Boolean(cart.is_proceed),
    }));
    total.value = Number(response?.meta?.total ?? list.length);
    if (response?.meta?.current_page) options.value.page = Number(response.meta.current_page);
    if (response?.meta?.per_page) options.value.itemsPerPage = Number(response.meta.per_page);
  } finally {
    loading.value = false;
  }
}

function onOptions(next: { page: number; itemsPerPage: number }) {
  options.value = next;
  fetchCarts();
}

onMounted(fetchCarts);
</script>
