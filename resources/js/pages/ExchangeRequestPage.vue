<template>
  <div>
    <AppPageHeader title="Mobile & Laptop Exchange" subtitle="Manage device exchange requests, evaluation and approvals.">
      <template #actions>
        <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="createOpen = true">Add Exchange Request</v-btn>
      </template>
    </AppPageHeader>

    <RequestStatusOverview :cards="summaryCards" :tabs="visibleTabs" :model-value="activeTab" @update:model-value="selectTab" />

    <v-container v-if="selected.length" fluid class="pb-0">
      <v-alert color="primary" variant="tonal" density="comfortable" class="selection-bar">
        <div class="d-flex flex-wrap align-center ga-2">
          <strong class="mr-2">{{ selected.length }} selected</strong>
          <v-btn size="small" variant="tonal" color="info" prepend-icon="mdi-magnify-scan" :loading="bulkBusy === 'under_review'" :disabled="!!bulkBusy" @click="runBulk('under_review')">
            Mark under review
          </v-btn>
          <v-btn size="small" variant="tonal" color="success" prepend-icon="mdi-check-decagram-outline" :loading="bulkBusy === 'completed'" :disabled="!!bulkBusy" @click="runBulk('completed')">
            Mark completed
          </v-btn>
          <v-btn size="small" variant="tonal" color="error" prepend-icon="mdi-close-circle-outline" :disabled="!!bulkBusy" @click="openReject">
            Reject
          </v-btn>
          <v-spacer />
          <v-btn size="small" variant="text" :disabled="!!bulkBusy" @click="selected = []">Clear</v-btn>
        </div>
        <div class="text-caption mt-1">
          Approving needs each request's offer and inspection note, so it is done from the request itself. Requests that cannot make the move are skipped.
        </div>
      </v-alert>
    </v-container>
    <AppDataTable
      :headers="headers"
      :items="items"
      :total="total"
      :loading="loading"
      :searchable="false"
      :items-per-page="options.itemsPerPage"
      show-select
      :selected="selected"
      @update:selected="onSelected"
      @update:options="onOptions"
    >
      <template #actions>
        <v-container fluid>
          <v-row dense>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="filters.deviceType"
                :items="deviceTypeItems"
                density="compact"
                variant="outlined"
                label="Device type"
                hide-details
                @update:model-value="onSearch"
              />
            </v-col>
            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="dropdownStatus"
                :items="statusItems"
                density="compact"
                variant="outlined"
                label="Status"
                hide-details
                clearable
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
                label="Search name, phone, IMEI or ID"
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

      <template #item.reference="{ item }">
        <RecordIdLink :to="{ name: 'admin.exchange.requests.detail', params: { id: item.id } }" :label="item.reference" />
      </template>
      <template #item.customer="{ item }">
        <div class="font-weight-medium">{{ item.customer_name }}</div>
        <div class="text-caption text-medium-emphasis d-flex align-center ga-1">
          <v-icon icon="mdi-phone-outline" size="14" />{{ item.customer_phone }}
        </div>
      </template>
      <template #item.device_name="{ item }">
        <div class="d-flex align-center ga-2 py-2">
          <v-avatar color="primary" variant="tonal" rounded size="36">
            <v-icon :icon="item.device_type === 'laptop' ? 'mdi-laptop' : 'mdi-cellphone'" size="20" />
          </v-avatar>
          <div>
            <div class="font-weight-medium">{{ item.device_name }}</div>
            <div class="text-caption text-medium-emphasis">
              {{ item.device_type === 'laptop' ? 'SN' : 'IMEI' }}: {{ item.serial_number || '-' }}
            </div>
          </div>
        </div>
      </template>
      <template #item.target_name="{ item }">
        <span>{{ item.target_name || '-' }}</span>
      </template>
      <template #item.customer_estimate="{ item }">
        <div>{{ formatNPR(item.customer_estimate) }}</div>
        <v-chip v-if="item.hold" size="x-small" color="error" variant="tonal" label class="mr-1">On hold</v-chip>
        <v-chip v-if="item.needs_manual_review" size="x-small" color="warning" variant="tonal" label>Manual review</v-chip>
      </template>
      <template #item.admin_offer="{ item }">
        <span class="font-weight-bold">{{ item.admin_offer === null ? '-' : formatNPR(item.admin_offer) }}</span>
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
        <v-tooltip text="View and decide" location="top">
          <template #activator="{ props: tip }">
            <v-btn
              v-bind="tip"
              icon="mdi-eye-outline"
              variant="tonal"
              color="primary"
              size="small"
              aria-label="View exchange request"
              :to="{ name: 'admin.exchange.requests.detail', params: { id: item.id } }"
            />
          </template>
        </v-tooltip>
      </template>
    </AppDataTable>

    <ExchangeRequestCreateDialog v-model="createOpen" @created="onCreated" />

    <v-dialog v-model="rejectOpen" max-width="520" persistent>
      <v-card>
        <v-card-title>Reject {{ selected.length }} {{ selected.length === 1 ? 'request' : 'requests' }}</v-card-title>
        <v-card-text>
          <v-textarea
            v-model="rejectReason"
            label="Reason the customer will see *"
            rows="3"
            auto-grow
            maxlength="1000"
            counter
            variant="outlined"
            :error-messages="rejectError"
            @update:model-value="rejectError = ''"
          />
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" :disabled="bulkBusy === 'rejected'" @click="rejectOpen = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" :loading="bulkBusy === 'rejected'" @click="confirmReject">Reject selected</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableHeader, DataTableOptions } from '@/components/datatable/types';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';
import RequestStatusOverview from '@/components/shared/RequestStatusOverview.vue';
import RecordIdLink from '@/components/shared/RecordIdLink.vue';
import ExchangeRequestCreateDialog from '@/components/exchange/ExchangeRequestCreateDialog.vue';
import { useSnackbar } from '@/composables/snackbar';
import { bulkUpdateExchangeStatus, listExchangeRequests, type ExchangeRequestListItem } from '@/api/exchange.api';
import { EXCHANGE_STATUS_OPTIONS, exchangeStatusColor, exchangeStatusLabel } from '@/shared/exchange';
import { formatDateTime, formatNPR } from '@/shared/formatters';

const headers: DataTableHeader[] = [
  { title: 'Request ID', key: 'reference', sortable: false, width: '130' },
  { title: 'Customer', key: 'customer', sortable: false, width: '190' },
  { title: 'Old device (exchange)', key: 'device_name', sortable: false, width: '250' },
  { title: 'New product', key: 'target_name', sortable: false, width: '180' },
  { title: 'Expected value', key: 'customer_estimate', sortable: false, width: '160' },
  { title: 'Exchange value', key: 'admin_offer', sortable: false, width: '150' },
  { title: 'Status', key: 'status', sortable: false, width: '130' },
  { title: 'Submitted at', key: 'created_at', sortable: false, width: '170' },
  { title: 'Action', key: 'action', sortable: false, width: '90', align: 'end' },
];

// A tab is one status, or "Pending" which covers requests waiting and under review.
const TABS = [
  { key: 'all', label: 'All Requests', statuses: null as string | null },
  { key: 'pending', label: 'Pending', statuses: 'pending,under_review' },
  { key: 'approved', label: 'Approved', statuses: 'approved' },
  { key: 'rejected', label: 'Rejected', statuses: 'rejected' },
  { key: 'completed', label: 'Completed', statuses: 'completed' },
  { key: 'cancelled', label: 'Cancelled', statuses: 'cancelled' },
];

const route = useRoute();
const router = useRouter();

const items = ref<ExchangeRequestListItem[]>([]);
const total = ref(0);
const loading = ref(false);
const searching = ref(false);
const counts = ref<Record<string, number>>({});
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 15, sortBy: [] });
const filters = ref({ query: '', deviceType: 'all', dateFrom: '', dateTo: '' });

const initialStatus = typeof route.query.status === 'string' ? route.query.status : null;
const statusParam = ref<string | null>(initialStatus);

const deviceTypeItems = [
  { title: 'All devices', value: 'all' },
  { title: 'Mobile', value: 'phone' },
  { title: 'Laptop', value: 'laptop' },
];

const statusItems = EXCHANGE_STATUS_OPTIONS;

// The "Pending" tab covers two statuses, which the single-status dropdown cannot show, so it stays empty then.
const dropdownStatus = computed<string | null>({
  get: () => (statusParam.value && !statusParam.value.includes(',') ? statusParam.value : null),
  set: (value) => applyStatus(value ?? null),
});

function countFor(statuses: string | null): number {
  if (statuses === null) return Object.values(counts.value).reduce((sum, n) => sum + Number(n), 0);
  return statuses.split(',').reduce((sum, s) => sum + Number(counts.value[s] ?? 0), 0);
}

const visibleTabs = computed(() =>
  TABS.map((t) => ({ ...t, count: countFor(t.statuses) })).filter((t) => t.key !== 'cancelled' || t.count > 0),
);

const activeTab = computed<string | null>({
  get: () => TABS.find((t) => t.statuses === statusParam.value)?.key ?? null,
  set: () => {},
});

const summaryCards = computed(() => [
  { key: 'total', tab: 'all', label: 'Total requests', value: countFor(null), icon: 'mdi-file-document-outline', color: 'primary' },
  { key: 'pending', tab: 'pending', label: 'Pending evaluation', value: countFor('pending,under_review'), icon: 'mdi-clock-outline', color: 'warning' },
  { key: 'approved', tab: 'approved', label: 'Approved', value: countFor('approved'), icon: 'mdi-check-circle-outline', color: 'success' },
  { key: 'rejected', tab: 'rejected', label: 'Rejected', value: countFor('rejected'), icon: 'mdi-close-circle-outline', color: 'error' },
  { key: 'completed', tab: 'completed', label: 'Completed', value: countFor('completed'), icon: 'mdi-check-decagram-outline', color: 'info' },
]);

async function fetchRequests() {
  loading.value = true;
  try {
    const params: Record<string, unknown> = { page: options.value.page, per_page: options.value.itemsPerPage };
    if (filters.value.query?.trim()) params.search = filters.value.query.trim();
    if (statusParam.value) params.status = statusParam.value;
    if (filters.value.deviceType !== 'all') params.device_type = filters.value.deviceType;
    if (filters.value.dateFrom) params.date_from = filters.value.dateFrom;
    if (filters.value.dateTo) params.date_to = filters.value.dateTo;

    const response = await listExchangeRequests(params);
    items.value = response.data ?? [];
    total.value = Number(response.meta?.total ?? items.value.length);
    counts.value = response.counts ?? {};
  } finally {
    loading.value = false;
    searching.value = false;
  }
}

// ---- add a request / act on several at once
const { showSuccess, showError, showWarning } = useSnackbar();
const createOpen = ref(false);
const selected = ref<Array<string | number>>([]);
const bulkBusy = ref<null | 'under_review' | 'completed' | 'rejected'>(null);
const rejectOpen = ref(false);
const rejectReason = ref('');
const rejectError = ref('');
const MAX_BULK = 50;

function onSelected(next: Array<string | number>) {
  if (next.length > MAX_BULK) {
    showWarning(`You can act on up to ${MAX_BULK} requests at once.`);
    selected.value = next.slice(0, MAX_BULK);
    return;
  }
  selected.value = next;
}

function onCreated() {
  onSearch();
}

function openReject() {
  rejectReason.value = '';
  rejectError.value = '';
  rejectOpen.value = true;
}

function confirmReject() {
  if (rejectReason.value.trim() === '') {
    rejectError.value = 'Write the reason the customer will see';
    return;
  }
  runBulk('rejected', rejectReason.value.trim());
}

async function runBulk(status: 'under_review' | 'completed' | 'rejected', customerNote?: string) {
  if (bulkBusy.value || selected.value.length === 0) return;
  bulkBusy.value = status;
  try {
    const response = await bulkUpdateExchangeStatus({ ids: selected.value.map(Number), status, customer_note: customerNote ?? null });
    const { updated, skipped } = response.data;
    if (skipped.length === 0) {
      showSuccess(`${updated.length} ${updated.length === 1 ? 'request' : 'requests'} updated`);
    } else {
      const names = skipped.slice(0, 3).map((s) => s.reference ?? `#${s.id}`).join(', ');
      showWarning(`${updated.length} updated, ${skipped.length} skipped (${names}${skipped.length > 3 ? ', ...' : ''}). ${skipped[0].reason}`);
    }
    rejectOpen.value = false;
    selected.value = [];
    await fetchRequests();
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string } } })?.response?.data;
    showError(data?.message ?? 'Could not update the requests');
  } finally {
    bulkBusy.value = null;
  }
}

function applyStatus(next: string | null) {
  statusParam.value = next;
  router.replace({ query: next ? { status: next } : {} });
  onSearch();
}

function selectTab(key: string | null) {
  const tab = TABS.find((t) => t.key === key);
  if (tab) applyStatus(tab.statuses);
}

function onSearch() {
  selected.value = [];
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
