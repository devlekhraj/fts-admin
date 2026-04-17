<template>
  <AppPageHeader title="Orders" subtitle="All orders" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
    :items-per-page="options.itemsPerPage" @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppTextField v-model="search" label="Search" placeholder="Search by name..."
                prepend-inner-icon="mdi-magnify" hide-details clearable style="min-width: 260px"
                @click:clear="onClearSearch" />
              <v-btn color="primary" variant="tonal" height="40">
                <v-icon start>mdi-magnify</v-icon>
                Search
              </v-btn>
            </div>
          </v-col>

          <v-col cols="12" md="6" lg="3">
            <AppSelectField item-title="title" item-value="value" label="Category" clearable hide-details />
          </v-col>

          <v-spacer></v-spacer>

          <v-col cols="12" md="auto" class="text-right">
            <div class="text-medium-emphasis">
              <span class="text-primary" style="font-size: smaller;">Total: {{ total }} Items found.</span>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </template>

    <template #item.order_number="{ item }">
      <span>{{ item.order_number || `#${item.id}` }}</span>
    </template>
    <template #item.customer="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="26" color="grey-lighten-3" rounded>
          <v-img v-if="item.customer_avatar" :src="item.customer_avatar" :alt="item.customer" cover />
          <v-icon v-else size="16" color="grey-darken-1">mdi-account-circle</v-icon>
        </v-avatar>
        <span>{{ item.customer || '-' }}</span>
      </div>
    </template>
    <template #item.items_count="{ item }">
      <span>{{ Number(item.items_count ?? 0) }} {{ Number(item.items_count ?? 0) === 1 ? 'item' : 'items' }}</span>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" variant="tonal" label :color="statusColor(item.status)">
        {{ item.status || '-' }}
      </v-chip>
    </template>
    <template #item.total="{ item }">
      <span>{{ item.total }}</span>
    </template>
    <template #item.created_at="{ item }">
      <span>{{ item.created_at || '-' }}</span>
    </template>
    <template #item.action="{ item }">
      <v-btn size="small" variant="flat" color="primary"
        @click="router.push({ name: 'admin.orders.detail', params: { id: item.id } })">
        Details
      </v-btn>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import PageFilter from '@/components/filters/PageFilter.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listOrders, type OrderListItem } from '@/api/orders.api';
import { formatNPR } from '@/shared/formatters';
import { timeAgo } from '@/shared/utils';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSelectField from '@/components/shared/AppSelectField.vue';

const headers = [
  { title: 'Order #', key: 'order_number', sortable: false, minWidth: '160' },
  { title: 'Customer', key: 'customer', sortable: false, minWidth: '220' },
  { title: 'Items', key: 'items_count', sortable: false, minWidth: '130' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '130' },
  { title: 'Amount', key: 'total', sortable: false, minWidth: '130' },
  { title: 'Order Date', key: 'created_at', sortable: false, minWidth: '150' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '130', align: 'end' },
];

type Order = {
  id: number | string;
  order_number: string;
  customer: string;
  customer_avatar: string;
  items_count: number;
  status: string;
  total: string;
  created_at: string;
};

const items = ref<Order[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

function statusColor(status: string): string {
  const normalized = String(status ?? '').toLowerCase();
  if (normalized === 'paid' || normalized === 'completed' || normalized === 'success') return 'success';
  if (normalized === 'pending' || normalized === 'processing') return 'warning';
  if (normalized === 'cancelled' || normalized === 'failed') return 'error';
  return 'primary';
}

async function fetchOrders() {
  loading.value = true;
  try {
    const response = await listOrders({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
    });

    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list.map((order: OrderListItem) => ({
      id: order.id,
      order_number: String(order.order_number ?? ''),
      customer: String(order.customer?.name ?? order.customer?.email ?? '-'),
      customer_avatar: String(order.customer?.avatar ?? ''),
      items_count: Number(order.items_count ?? 0),
      status: String(order.status ?? '-'),
      total: order.total !== null && order.total !== undefined ? formatNPR(order.total) : '-',
      created_at: order.created_at ? timeAgo(order.created_at) : '-',
    }));
    total.value = Number(response?.total ?? response?.meta?.total ?? list.length);
    if (response?.meta?.current_page) {
      options.value.page = Number(response.meta.current_page);
    }
    if (response?.meta?.per_page) {
      options.value.itemsPerPage = Number(response.meta.per_page);
    }
  } finally {
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  if (!hasLoadedOnce.value) {
    hasLoadedOnce.value = true;
  }
  fetchOrders();
}

function onSearch() {
  options.value.page = 1;
  fetchOrders();
}

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchOrders();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchOrders();
    hasLoadedOnce.value = true;
  }
});
</script>
