<template>
  <AppPageHeader title="EMI Banks" subtitle="Manage EMI partner banks">
    <template #actions>
      <EmiBankCreateButton @saved="onBankCreated" />
    </template>
  </AppPageHeader>

  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
    :items-per-page="options.itemsPerPage" @update:options="onOptions">
    <template #item.sn="{ index }">
      <span>{{ (options.page - 1) * options.itemsPerPage + index + 1 }}</span>
    </template>

    <template #item.name="{ item }">
      <span>{{ item.name ?? '-' }}</span>
    </template>

    <template #item.code="{ item }">
      <div class="d-flex align-center ga-1">
        <v-chip size="small" label color="primary">
          {{ item.code ?? '-' }}
        </v-chip>
        <v-btn v-if="item.code" icon size="small" variant="text" color=""
          @click.stop="copyCode(item.code as string)">
          <v-icon size="14">mdi-content-copy</v-icon>
        </v-btn>
      </div>
    </template>

    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn size="small" variant="tonal" color="primary"
          @click="router.push({ name: 'admin.emi.banks.detail', params: { id: item.id } })">
          View
        </v-btn>
        <EmiBankEditButton :bank="item" @saved="onBankUpdated" />
        <EmiBankDeleteButton :bank="item" @deleted="onBankDeleted" />
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listEmiBanks, type EmiBankListItem } from '@/api/emi-banks.api';
import EmiBankDeleteButton from '@/components/emi/EmiBankDeleteButton.vue';
import EmiBankEditButton from '@/components/emi/EmiBankEditButton.vue';
import EmiBankCreateButton from '@/components/emi/EmiBankCreateButton.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

type EmiBank = {
  id: number | string;
  name: string;
  code: string;
};

const headers = [
  { title: 'SN', key: 'sn', sortable: false, maxWidth: '80', minWidth:'80' },
  { title: 'Name', key: 'name', sortable: false, minWidth: '200' },
  { title: 'Code', key: 'code', sortable: false, maxWidth: '100', minWidth:'100' },
  { title: 'Actions', key: 'action', sortable: false, maxWidth: '100', minWidth: '100' },
];

const items = ref<EmiBank[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();
const snackbar = useSnackbarStore();

async function fetchBanks() {
  loading.value = true;
  try {
    const response = await listEmiBanks({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    });

    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list.map((bank: EmiBankListItem) => ({
      id: bank.id,
      name: bank.name ?? '-',
      code: bank.code ?? '-',
    }));
    total.value = Number(response?.total ?? list.length);
    if ((response as any)?.current_page) {
      options.value.page = Number((response as any).current_page);
    }
    if ((response as any)?.per_page) {
      options.value.itemsPerPage = Number((response as any).per_page);
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
  fetchBanks();
}

function onBankUpdated() {
  fetchBanks();
}

function onBankDeleted() {
  options.value.page = 1;
  fetchBanks();
}

function onBankCreated(payload?: unknown) {
  const created: any = payload ?? {};
  if (created?.id) {
    router.push({ name: 'admin.emi.banks.detail', params: { id: created.id } });
    return;
  }
  options.value.page = 1;
  fetchBanks();
}

async function copyCode(code: string) {
  try {
    await navigator.clipboard?.writeText(code);
    snackbar.show({ message: `Bank code : ${code} copied`, color: 'success' });
  } catch {
    snackbar.show({ message: 'Failed to copy code', color: 'error' });
  }
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchBanks();
    hasLoadedOnce.value = true;
  }
});
</script>
