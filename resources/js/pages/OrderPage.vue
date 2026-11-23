<template>
  <div>
    <AppPageHeader title="Orders" subtitle="Manage customer orders, payments and delivery." />

    <RequestStatusOverview :cards="summaryCards" :tabs="visibleTabs" :model-value="activeTab" @update:model-value="selectTab" />

    <AppDataTable
      :headers="headers"
      :items="items"
      :total="total"
      :loading="loading"
      :searchable="false"
      :page="options.page"
      :items-per-page="options.itemsPerPage"
      @update:options="onOptions"
    >
      <template #actions>
        <v-container fluid>
          <v-row dense>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.payment"
                :items="paymentItems"
                density="compact"
                variant="outlined"
                label="Payment"
                hide-details
                @update:model-value="onSearch"
              />
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.orderType"
                :items="orderTypeItems"
                density="compact"
                variant="outlined"
                label="Order type"
                hide-details
                @update:model-value="onSearch"
              />
            </v-col>
            <v-col cols="6" md="2">
              <v-text-field
                v-model="filters.dateFrom"
                type="date"
                density="compact"
                variant="outlined"
                label="From"
                hide-details
                :max="filters.dateTo || undefined"
                @update:model-value="onSearch"
              />
            </v-col>
            <v-col cols="6" md="2">
              <v-text-field
                v-model="filters.dateTo"
                type="date"
                density="compact"
                variant="outlined"
                label="To"
                hide-details
                :min="filters.dateFrom || undefined"
                @update:model-value="onSearch"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="filters.query"
                density="compact"
                variant="outlined"
                label="Search order, customer or email"
                prepend-inner-icon="mdi-magnify"
                hide-details
                clearable
                @keyup.enter="onSearch"
                @click:clear="onSearch"
              />
            </v-col>
            <v-col cols="12" md="1" class="d-flex align-center">
              <AppSearchButton :loading="searching" variant="flat" @click="onSearch" />
            </v-col>
          </v-row>
        </v-container>
      </template>

      <template #item.order_number="{ item }">
        <span class="font-weight-bold text-primary">{{ item.order_number || `#${item.id}` }}</span>
      </template>
      <template #item.customer="{ item }">
        <div class="d-flex align-center ga-2">
          <v-avatar size="28" color="grey-lighten-3" rounded>
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
        <v-chip size="small" variant="tonal" label :color="getStatusColor(item.status)">
          {{ item.status || '-' }}
        </v-chip>
      </template>
      <template #item.payment="{ item }">
        <div class="d-flex align-center ga-1 flex-wrap">
          <span class="text-capitalize">{{ item.payment_type }}</span>
          <v-chip size="x-small" variant="tonal" label :color="paymentColor(item.payment_status)">
            {{ item.payment_status }}
          </v-chip>
          <v-chip v-if="item.is_pre_order" size="x-small" variant="tonal" label color="info">Pre-order</v-chip>
        </div>
      </template>
      <template #item.total="{ item }">
        <span class="font-weight-bold">{{ item.total }}</span>
      </template>
      <template #item.created_at="{ item }">
        <span>{{ item.created_at || '-' }}</span>
      </template>
      <template #item.action="{ item }">
        <v-tooltip text="View order" location="top">
          <template #activator="{ props: tip }">
            <v-btn
              v-bind="tip"
              icon="mdi-eye-outline"
              variant="tonal"
              color="primary"
              size="small"
              aria-label="View order"
              :to="{ name: 'admin.orders.detail', params: { id: item.id } }"
            />
          </template>
        </v-tooltip>
      </template>
    </AppDataTable>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listOrders, type OrderListItem } from '@/api/orders.api';
import { formatNPR } from '@/shared/formatters';
import { getStatusColor, timeAgo } from '@/shared/utils';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';
import RequestStatusOverview from '@/components/shared/RequestStatusOverview.vue';

const headers = [
  { title: 'Order #', key: 'order_number', sortable: false, minWidth: '160' },
  { title: 'Customer', key: 'customer', sortable: false, minWidth: '220' },
  { title: 'Items', key: 'items_count', sortable: false, minWidth: '110' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '130' },
  { title: 'Payment', key: 'payment', sortable: false, minWidth: '190' },
  { title: 'Amount', key: 'total', sortable: false, minWidth: '130' },
  { title: 'Order date', key: 'created_at', sortable: false, minWidth: '150' },
  { title: 'Action', key: 'action', sortable: false, minWidth: '90', align: 'end' as const },
];

// The order status numbers the API uses: 0 draft, 1 placed, 2 confirmed, 3 dispatched, 4 completed, 5 canceled, 6 delivered.
const TABS = [
  { key: 'all', label: 'All Orders', statuses: null as string | null },
  { key: 'placed', label: 'Placed', statuses: '1' },
  { key: 'confirmed', label: 'Confirmed', statuses: '2' },
  { key: 'dispatched', label: 'Dispatched', statuses: '3' },
  { key: 'delivered', label: 'Delivered', statuses: '6' },
  { key: 'completed', label: 'Completed', statuses: '4' },
  { key: 'canceled', label: 'Canceled', statuses: '5' },
  { key: 'draft', label: 'Draft', statuses: '0' },
];

type Order = {
  id: number | string;
  order_number: string;
  customer: string;
  customer_avatar: string;
  items_count: number;
  status: string;
  payment_type: string;
  payment_status: string;
  is_pre_order: boolean;
  total: string;
  created_at: string;
};

const items = ref<Order[]>([]);
const total = ref(0);
const loading = ref(false);
const searching = ref(false);
const counts = ref<Record<string, number>>({});
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 15, sortBy: [] });
const filters = ref({ query: '', payment: 'all', orderType: 'all', dateFrom: '', dateTo: '' });
const statusParam = ref<string | null>(null);

const paymentItems = [
  { title: 'All payments', value: 'all' },
  { title: 'Paid', value: 'paid' },
  { title: 'Unpaid', value: 'unpaid' },
  { title: 'Failed', value: 'failed' },
];
const orderTypeItems = [
  { title: 'All orders', value: 'all' },
  { title: 'Regular', value: 'regular' },
  { title: 'Pre-order', value: 'pre' },
];

function countFor(statuses: string | null): number {
  if (statuses === null) return Object.values(counts.value).reduce((sum, n) => sum + Number(n), 0);
  return statuses.split(',').reduce((sum, s) => sum + Number(counts.value[s] ?? 0), 0);
}

// A tab with no orders is hidden, except the main ones, so the row stays short.
const ALWAYS_SHOWN = ['all', 'placed', 'confirmed', 'dispatched', 'delivered', 'completed', 'canceled'];
const visibleTabs = computed(() =>
  TABS.map((t) => ({ ...t, count: countFor(t.statuses) })).filter((t) => ALWAYS_SHOWN.includes(t.key) || t.count > 0),
);

const activeTab = computed<string | null>(() => TABS.find((t) => t.statuses === statusParam.value)?.key ?? null);

const summaryCards = computed(() => [
  { key: 'total', tab: 'all', label: 'Total orders', value: countFor(null), icon: 'mdi-clipboard-text-outline', color: 'primary' },
  { key: 'placed', tab: 'placed', label: 'Placed', value: countFor('1'), icon: 'mdi-clock-outline', color: 'warning' },
  { key: 'confirmed', tab: 'confirmed', label: 'Confirmed', value: countFor('2'), icon: 'mdi-check-circle-outline', color: 'success' },
  { key: 'dispatched', tab: 'dispatched', label: 'Dispatched', value: countFor('3'), icon: 'mdi-truck-delivery-outline', color: 'info' },
  { key: 'canceled', tab: 'canceled', label: 'Canceled', value: countFor('5'), icon: 'mdi-close-circle-outline', color: 'error' },
]);

function paymentColor(status: string): string {
  if (status === 'paid') return 'success';
  if (status === 'failed') return 'error';
  return 'warning';
}

async function fetchOrders() {
  loading.value = true;
  try {
    const response = await listOrders({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: filters.value.query?.trim() || undefined,
      status: statusParam.value || undefined,
      payment_status: filters.value.payment !== 'all' ? filters.value.payment : undefined,
      pre_order: filters.value.orderType === 'all' ? undefined : filters.value.orderType === 'pre',
      date_from: filters.value.dateFrom || undefined,
      date_to: filters.value.dateTo || undefined,
    });

    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list.map((order: OrderListItem) => ({
      id: order.id,
      order_number: String(order.order_number ?? ''),
      customer: String(order.customer?.name ?? order.customer?.email ?? '-'),
      customer_avatar: String(order.customer?.avatar ?? ''),
      items_count: Number(order.items_count ?? 0),
      status: String(order.status ?? '-'),
      payment_type: String(order.payment_type ?? '-'),
      payment_status: String(order.payment_status ?? '-'),
      is_pre_order: Boolean(order.is_pre_order),
      total: order.total !== null && order.total !== undefined ? formatNPR(order.total) : '-',
      created_at: order.created_at ? timeAgo(order.created_at) : '-',
    }));
    total.value = Number(response?.total ?? response?.meta?.total ?? list.length);
    counts.value = response?.counts ?? {};
  } finally {
    searching.value = false;
    loading.value = false;
  }
}

function selectTab(key: string | null) {
  const tab = TABS.find((t) => t.key === key);
  if (!tab) return;
  statusParam.value = tab.statuses;
  onSearch();
}

function onSearch() {
  options.value.page = 1;
  searching.value = true;
  fetchOrders();
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  fetchOrders();
}

onMounted(fetchOrders);
</script>
