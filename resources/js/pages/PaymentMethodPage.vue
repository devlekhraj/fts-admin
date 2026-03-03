<template>
  <AppPageHeader title="Payment Methods" subtitle="Manage available payment methods" />

  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    @update:options="onOptions">
    <template #item.name="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.logo_url" :src="item.logo_url" :alt="item.name" class="method-thumb" />
          <v-icon v-else size="18" color="grey-darken-1">mdi-credit-card-outline</v-icon>
        </v-avatar>
        <span>{{ item.name }}</span>
      </div>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'warning'">
        {{ item.status ? 'Active' : 'Inactive' }}
      </v-chip>
    </template>
    <template #item.test_mode="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.test_mode ? 'gray' : 'primary'">
        <v-icon
          v-if="!item.test_mode"
          start
          size="10"
          color="primary"
          class="live-blink">
          mdi-circle
        </v-icon>
        {{ item.test_mode ? 'Test' : 'Live' }}
      </v-chip>
    </template>
    <template #item.is_international="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.is_international ? 'info' : 'grey'">
        {{ item.is_international ? 'Yes' : 'No' }}
      </v-chip>
    </template>
    <template #item.created_at="{ item }">
      <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center ga-1">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="router.push({ name: 'admin.paymentMethods.detail', params: { id: item.id } })">
          <v-icon size="16">mdi-eye</v-icon>
        </v-btn>
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
import { listPaymentMethods, type PaymentMethodListItem } from '@/api/payment-methods.api';
import { formatLongDate } from '@/shared/utils';

const headers = [
  { title: 'Name', key: 'name', sortable: false, minWidth: '220' },
  { title: 'Slug', key: 'slug', sortable: false, minWidth: '180' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '130' },
  { title: 'Test Mode', key: 'test_mode', sortable: false, minWidth: '130' },
  { title: 'International', key: 'is_international', sortable: false, minWidth: '140' },
  { title: 'Created At', key: 'created_at', sortable: false, minWidth: '180' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '110' },
];

type PaymentMethod = {
  id: number | string;
  name: string;
  slug: string;
  status: boolean;
  test_mode: boolean;
  is_international: boolean;
  logo_url: string;
  created_at: string;
};

const items = ref<PaymentMethod[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

async function fetchPaymentMethods() {
  loading.value = true;
  try {
    const response = await listPaymentMethods({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
    });

    const list = Array.isArray(response) ? response : response?.data ?? [];
    items.value = list.map((method: PaymentMethodListItem) => ({
      id: method.id,
      name: method.name ?? '-',
      slug: method.slug ?? '-',
      status: Boolean(method.status),
      test_mode: Boolean(method.test_mode),
      is_international: Boolean(method.is_international),
      logo_url: typeof method.logo_url === 'string' ? method.logo_url : '',
      created_at: method.created_at ?? '-',
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
  fetchPaymentMethods();
}

onMounted(() => {
  if (!hasLoadedOnce.value) {
    fetchPaymentMethods();
    hasLoadedOnce.value = true;
  }
});
</script>

<style scoped>
:deep(.method-thumb .v-img__img) {
  object-fit: contain;
}

.live-blink {
  animation: live-blink 1.1s infinite ease-in-out;
}

@keyframes live-blink {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.25;
  }
}
</style>
