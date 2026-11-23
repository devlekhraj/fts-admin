<template>
  <div>
    <AppPageHeader title="EMI Requests" subtitle="Manage EMI requests, review and approvals." />

    <RequestStatusOverview :cards="summaryCards" :tabs="visibleTabs" :model-value="activeTab" @update:model-value="selectTab" />

    <AppDataTable
      :headers="headers"
      :items="items"
      :total="total"
      :loading="loading"
      :searchable="false"
      :items-per-page="options.itemsPerPage"
      @update:options="onOptions"
    >
      <template #actions>
        <v-container fluid>
          <v-row dense>
            <v-col cols="12" sm="6" md="3">
              <v-select
                v-model="filters.emiType"
                :items="emiTypeOptions"
                density="compact"
                variant="outlined"
                label="EMI type"
                hide-details
                clearable
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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="filters.query"
                density="compact"
                variant="outlined"
                label="Search customer, email, product or ID"
                prepend-inner-icon="mdi-magnify"
                hide-details
                clearable
                @keyup.enter="onSearch"
                @click:clear="onSearch"
              />
            </v-col>
            <v-col cols="12" md="1" class="d-flex align-center">
              <AppSearchButton :loading="fetchingState" variant="flat" @click="onSearch" />
            </v-col>
          </v-row>
        </v-container>
      </template>

      <template #item.user="{ item }">
        <div class="d-flex align-center gap-2">
          <v-avatar size="32">
            <v-img :src="item.user?.avatar" alt="User" />
          </v-avatar>
          <div>
            <p class="ml-3 text-primary">{{ item.user?.name ?? '-' }}</p>
            <p class="ml-3">{{ item.user?.email ?? '-' }}</p>
            <p v-if="item.account?.email" class="ml-3 text-caption text-medium-emphasis">
              Account: {{ item.account.email }}
              <v-chip v-if="item.identity_mismatch" size="x-small" color="warning" variant="tonal" class="ml-1">Different</v-chip>
            </p>
          </div>
        </div>
      </template>
      <template #item.product="{ item }">
        <div class="d-flex align-center gap-2">
          <v-avatar size="32" rounded>
            <v-img v-if="item.product?.thumb" :src="item.product.thumb" alt="Product" />
            <v-icon v-else size="18" color="grey-darken-1">mdi-package-variant-closed</v-icon>
          </v-avatar>
          <span class="ml-3">{{ item.product?.name ?? '-' }}</span>
        </div>
      </template>
      <template #item.time="{ item }">
        <span>{{ timeAgo(item.time) }}</span>
      </template>
      <template #item.emi_type="{ item }">
        <div style="min-width: 150px" class="d-flex align-center">
          <v-icon start class="mr-2" :color="getEmiIconColor(item.emi_type)" :icon="getEmiTypeIcon(item.emi_type)" />
          <span class="text-capitalize" :class="getEmiIconTextColor(item.emi_type)">
            {{ item.emi_type ? item.emi_type.split('_').join(' ') : 'Old' }}
          </span>
        </div>
      </template>
      <template #item.status_label="{ item }">
        <v-chip size="small" :color="getStatusColor(item.status_label)" variant="tonal" label>
          {{ item.status_label ?? item.status ?? '-' }}
        </v-chip>
      </template>
      <template #item.action="{ item }">
        <v-tooltip text="View request" location="top">
          <template #activator="{ props: tip }">
            <v-btn
              v-bind="tip"
              icon="mdi-eye-outline"
              variant="tonal"
              color="primary"
              size="small"
              aria-label="View EMI request"
              :to="{ name: 'admin.emi.requests.detail', params: { id: item.id } }"
            />
          </template>
        </v-tooltip>
      </template>
    </AppDataTable>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableHeader, DataTableOptions } from '@/components/datatable/types';
import { list as listEmiRequests } from '@/api/emi-requests.api';
import { getEmiIconColor, getEmiIconTextColor, getEmiTypeIcon } from '@/shared/emi';
import { getStatusColor, timeAgo } from '@/shared/utils';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';
import RequestStatusOverview from '@/components/shared/RequestStatusOverview.vue';

type EmiRequest = Record<string, unknown>;

const headers: DataTableHeader[] = [
  { title: 'Product', key: 'product', sortable: false, width: '300' },
  { title: 'Customer', key: 'user', sortable: false, width: '230' },
  { title: 'EMI type', key: 'emi_type', sortable: false, width: '160' },
  { title: 'Status', key: 'status_label', sortable: false, width: '130' },
  { title: 'Submitted', key: 'time', width: '170', sortable: false },
  { title: 'Action', key: 'action', sortable: false, width: '90', align: 'end' },
];

// The EMI status numbers the API uses: 0 pending, 1 processing, 2 approved, 3 finished, 4 cancelled,
// 5 bank approval, 6 dispatched, 7 delivered, 8 completed.
const TABS = [
  { key: 'all', label: 'All Requests', status: null as number | null },
  { key: 'pending', label: 'Pending', status: 0 },
  { key: 'processing', label: 'Processing', status: 1 },
  { key: 'approved', label: 'Approved', status: 2 },
  { key: 'finished', label: 'Finished', status: 3 },
  { key: 'bank', label: 'Bank Approval', status: 5 },
  { key: 'dispatched', label: 'Dispatched', status: 6 },
  { key: 'delivered', label: 'Delivered', status: 7 },
  { key: 'completed', label: 'Completed', status: 8 },
  { key: 'cancelled', label: 'Cancelled', status: 4 },
];
// These tabs appear only when they hold requests.
const ALWAYS_SHOWN = ['all', 'pending', 'processing', 'approved', 'finished'];

const items = ref<EmiRequest[]>([]);
const total = ref(0);
const loading = ref(false);
const fetchingState = ref(false);
const counts = ref<Record<string, number>>({});
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 15, sortBy: [] });
const filters = ref({ query: '', emiType: null as null | string, dateFrom: '', dateTo: '' });
const statusParam = ref<number | null>(null);

const emiTypeOptions = [
  { title: 'Credit Card', value: 'credit_card' },
  { title: 'New Credit Card', value: 'new_credit_card' },
  { title: 'Citizenship', value: 'citizenship' },
];

function countFor(status: number | null): number {
  if (status === null) return Object.values(counts.value).reduce((sum, n) => sum + Number(n), 0);
  return Number(counts.value[String(status)] ?? 0);
}

const visibleTabs = computed(() =>
  TABS.map((t) => ({ ...t, count: countFor(t.status) })).filter((t) => ALWAYS_SHOWN.includes(t.key) || t.count > 0),
);

const activeTab = computed<string | null>(() => TABS.find((t) => t.status === statusParam.value)?.key ?? null);

const summaryCards = computed(() => [
  { key: 'total', tab: 'all', label: 'Total requests', value: countFor(null), icon: 'mdi-file-document-outline', color: 'primary' },
  { key: 'pending', tab: 'pending', label: 'Pending', value: countFor(0), icon: 'mdi-clock-outline', color: 'warning' },
  { key: 'processing', tab: 'processing', label: 'Processing', value: countFor(1), icon: 'mdi-progress-clock', color: 'info' },
  { key: 'approved', tab: 'approved', label: 'Approved', value: countFor(2), icon: 'mdi-check-circle-outline', color: 'success' },
  { key: 'finished', tab: 'finished', label: 'Finished', value: countFor(3), icon: 'mdi-check-decagram-outline', color: 'primary' },
]);

async function fetchRequests() {
  loading.value = true;
  try {
    const response = await listEmiRequests({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: filters.value.query?.trim() || undefined,
      emi_type: filters.value.emiType || undefined,
      status: statusParam.value ?? undefined,
      date_from: filters.value.dateFrom || undefined,
      date_to: filters.value.dateTo || undefined,
    });

    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list;
    total.value = Number(response?.total ?? response?.meta?.total ?? list.length);
    counts.value = response?.counts ?? {};
  } finally {
    loading.value = false;
    fetchingState.value = false;
  }
}

function selectTab(key: string | null) {
  const tab = TABS.find((t) => t.key === key);
  if (!tab) return;
  statusParam.value = tab.status;
  onSearch();
}

function onSearch() {
  options.value.page = 1;
  fetchingState.value = true;
  fetchRequests();
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  fetchRequests();
}

onMounted(fetchRequests);
</script>
