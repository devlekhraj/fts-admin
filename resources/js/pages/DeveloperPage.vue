<template>
  <AppPageHeader title="Developer" subtitle="Developer tools and utilities">
    <template #actions>
      <ApiKeyCreateButton @saved="onApiKeyChanged" />
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    @update:options="onOptions">
    <template #item.sn="{ item }">
      <span>{{ item.sn }}</span>
    </template>

      <template #item.host="{ item }">
        <span style="font-size: 0.8rem;">{{ item.host || '-' }}</span>
      </template>

      <template #item.mode="{ item }">
        <v-chip size="small" label class="text-uppercase" variant="tonal"
          :color="item.mode == 'live' ? 'primary' : 'warning'">
          {{ item.mode || '-' }}
        </v-chip>
      </template>

      <template #item.live_public_key="{ item }">
        <div class="d-flex align-center ga-2">
          <span class="text-caption text-medium-emphasis text-truncate key-preview">
            {{ maskKey(item.live_public_key) }}
          </span>
          <v-btn icon size="x-small" variant="tonal" color="primary" :disabled="!item.live_public_key"
            @click="copyLivePublicKey(item.live_public_key)">
            <v-icon size="14">mdi-content-copy</v-icon>
          </v-btn>
        </div>
      </template>

      <template #item.is_active="{ item }">
        <v-chip size="small" label variant="tonal" :color="item.is_active ? 'success' : 'warning'">
          {{ item.is_active ? 'Active' : 'Inactive' }}
        </v-chip>
      </template>

      <template #item.created_at="{ item }">
        <span class="text-muted">{{ formatLongDate(item.created_at) ?? '-' }}</span>
      </template>

      <template #item.action="{ item }">
        <div class="d-flex justify-end ga-2">
          <v-btn size="small" variant="outlined" color="primary" @click="onConfigure(item)">
            Edit
          </v-btn>
          <v-btn size="small" variant="outlined" color="error" @click="onDelete(item)">
            delete
          </v-btn>
        </div>
      </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { listApiKeys, type ApiKeyItem } from '@/api/developer.api';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { formatLongDate } from '@/shared/utils';
import { useSnackbarStore } from '@/stores/snackbar.store';
import ApiKeyCreateButton from '@/components/developer/ApiKeyCreateButton.vue';
import ApiKeyFormModal from '@/components/developer/ApiKeyFormModal.vue';
import ApiKeyDeleteModal from '@/components/developer/ApiKeyDeleteModal.vue';
import { openModal } from '@/shared/modal';

const headers = [
  { title: 'SN#', key: 'sn', sortable: false, width: '80' },
  { title: 'Host', key: 'host', sortable: false, minWidth: '260' },
  { title: 'Mode', key: 'mode', sortable: false, minWidth: '120' },
  { title: 'Public Key', key: 'live_public_key', sortable: false, width: '160' },
  { title: 'Status', key: 'is_active', sortable: false, minWidth: '120' },
  { title: 'Created At', key: 'created_at', sortable: false, minWidth: '180' },
  { title: 'Action', key: 'action', sortable: false, minWidth: '120', align: 'end' as const },
];

type ApiKeyRow = ApiKeyItem & { sn: number };

const allItems = ref<ApiKeyItem[]>([]);
const items = ref<ApiKeyRow[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 10, sortBy: [] });
const snackbar = useSnackbarStore();

function maskKey(value: string | null | undefined): string {
  const text = String(value ?? '').trim();
  if (!text) return '-';
  if (text.length <= 8) return `${text.slice(0, 2)}****${text.slice(-2)}`;
  return `${text.slice(0, 10)}****`;
}

async function copyLivePublicKey(value: string | null | undefined) {
  const text = String(value ?? '').trim();
  if (!text) return;

  try {
    await navigator.clipboard.writeText(text);
    snackbar.show({ message: 'Live public key copied.', color: 'success' });
  } catch {
    snackbar.show({ message: 'Failed to copy live public key.', color: 'error' });
  }
}

function applyPagination() {
  const page = Math.max(1, Number(options.value.page ?? 1));
  const perPage = Math.max(1, Number(options.value.itemsPerPage ?? 10));
  const startIndex = (page - 1) * perPage;
  const paged = allItems.value.slice(startIndex, startIndex + perPage);

  items.value = paged.map((item, index) => ({
    ...item,
    sn: startIndex + index + 1,
  }));
}

async function fetchApiKeys() {
  loading.value = true;
  try {
    allItems.value = await listApiKeys();
    total.value = allItems.value.length;

    const perPage = Math.max(1, Number(options.value.itemsPerPage ?? 10));
    const lastPage = Math.max(1, Math.ceil(total.value / perPage));
    if (options.value.page > lastPage) {
      options.value.page = lastPage;
    }

    applyPagination();
  } finally {
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  applyPagination();
}

function onConfigure(item: ApiKeyItem) {
  openModal(
    ApiKeyFormModal,
    { mode: 'edit', item },
    {
      title: `Edit API Key #${item.id}`,
      size: 'md',
      onSaved: onApiKeyChanged,
    },
  );
}

function onDelete(item: ApiKeyItem) {
  openModal(
    ApiKeyDeleteModal,
    { item },
    {
      title: `Delete API Key #${item.id}`,
      size: 'sm',
      onDeleted: onApiKeyChanged,
    },
  );
}

function onApiKeyChanged() {
  fetchApiKeys();
}

onMounted(fetchApiKeys);
</script>

<style scoped>
.created-at-text {
  font-size: 0.85rem;
  color: rgb(var(--v-theme-primary));
}
</style>
