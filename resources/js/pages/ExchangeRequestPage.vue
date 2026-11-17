<template>
  <div>
    <AppPageHeader title="Exchange Requests" subtitle="Customers trading in an old device" />
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
          <v-row>
            <v-col cols="12" md="5">
              <v-text-field
                v-model="filters.query"
                density="compact"
                variant="outlined"
                label="Search name, phone, device, IMEI or reference"
                hide-details
                clearable
                @keyup.enter="onSearch"
                @click:clear="onSearch"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="filters.status"
                :items="statusItems"
                density="compact"
                variant="outlined"
                label="Status"
                hide-details
                clearable
                @update:model-value="onSearch"
              />
            </v-col>
            <v-col cols="12" md="2" class="d-flex align-end">
              <AppSearchButton :loading="searching" variant="flat" @click="onSearch" />
            </v-col>
          </v-row>
        </v-container>
      </template>

      <template #item.reference="{ item }">
        <span class="font-weight-medium">{{ item.reference }}</span>
      </template>
      <template #item.customer="{ item }">
        <div>{{ item.customer_name }}</div>
        <div class="text-caption text-medium-emphasis">{{ item.customer_phone }}</div>
      </template>
      <template #item.customer_estimate="{ item }">
        <div>{{ formatNPR(item.customer_estimate) }}</div>
        <v-chip v-if="item.needs_manual_review" size="x-small" color="warning" variant="tonal" label>Manual review</v-chip>
      </template>
      <template #item.admin_offer="{ item }">
        <span>{{ item.admin_offer === null ? '-' : formatNPR(item.admin_offer) }}</span>
      </template>
      <template #item.status="{ item }">
        <v-chip size="small" :color="exchangeStatusColor(item.status)" variant="tonal" label>
          {{ exchangeStatusLabel(item.status) }}
        </v-chip>
      </template>
      <template #item.created_at="{ item }">
        <span>{{ formatDateTime(item.created_at) }}</span>
      </template>
      <template #item.action="{ item }">
        <v-btn variant="outlined" color="primary" size="small" :to="{ name: 'admin.exchange.requests.detail', params: { id: item.id } }">
          Details
        </v-btn>
      </template>
    </AppDataTable>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableHeader, DataTableOptions } from '@/components/datatable/types';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';
import { listExchangeRequests, type ExchangeRequestListItem } from '@/api/exchange.api';
import { EXCHANGE_STATUS_OPTIONS, exchangeStatusColor, exchangeStatusLabel } from '@/shared/exchange';
import { formatDateTime, formatNPR } from '@/shared/formatters';

const headers: DataTableHeader[] = [
  { title: 'Reference', key: 'reference', sortable: false, width: '140' },
  { title: 'Customer', key: 'customer', sortable: false, width: '200' },
  { title: 'Device', key: 'device_name', sortable: false, width: '220' },
  { title: 'Customer estimate', key: 'customer_estimate', sortable: false, width: '170' },
  { title: 'Our offer', key: 'admin_offer', sortable: false, width: '120' },
  { title: 'Status', key: 'status', sortable: false, width: '130' },
  { title: 'Submitted', key: 'created_at', sortable: false, width: '180' },
  { title: 'Actions', key: 'action', sortable: false, width: '100', align: 'end' },
];

const items = ref<ExchangeRequestListItem[]>([]);
const total = ref(0);
const loading = ref(false);
const searching = ref(false);
const counts = ref<Record<string, number>>({});
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 15, sortBy: [] });
const filters = ref({ query: '', status: null as null | string });

const statusItems = computed(() =>
  EXCHANGE_STATUS_OPTIONS.map((o) => ({ ...o, title: `${o.title} (${counts.value[o.value] ?? 0})` })),
);

async function fetchRequests() {
  loading.value = true;
  try {
    const params: Record<string, unknown> = { page: options.value.page, per_page: options.value.itemsPerPage };
    if (filters.value.query?.trim()) params.search = filters.value.query.trim();
    if (filters.value.status) params.status = filters.value.status;

    const response = await listExchangeRequests(params);
    items.value = response.data ?? [];
    total.value = Number(response.meta?.total ?? items.value.length);
    counts.value = response.counts ?? {};
  } finally {
    loading.value = false;
    searching.value = false;
  }
}

function onSearch() {
  options.value.page = 1;
  searching.value = true;
  fetchRequests();
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  fetchRequests();
}

onMounted(fetchRequests);
</script>
