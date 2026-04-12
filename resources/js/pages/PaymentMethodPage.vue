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
      <div class="d-flex align-center ga-3">
        <v-avatar size="36" color="grey-lighten-4" rounded="lg">
          <v-img v-if="item.thumb || item.logo_url" :src="String(item.thumb || item.logo_url)" :alt="item.name" class="method-thumb" />
          <v-icon v-else size="20" color="grey-darken-1">mdi-credit-card-outline</v-icon>
        </v-avatar>
        <div class="d-flex flex-column">
          <span class="font-weight-medium">{{ item.name }}</span>
          <span class="text-caption text-medium-emphasis">{{ item.slug }}</span>
        </div>
      </div>
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.status ? 'success' : 'warning'">
        {{ item.status ? 'Active' : 'Inactive' }}
      </v-chip>
    </template>
    <template #item.test_mode="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.test_mode ? 'grey' : 'primary'">
        <v-icon
          v-if="!item.test_mode"
          start
          size="10"
          color="primary"
          class="live-blink">
          mdi-circle
        </v-icon>
        {{ item.test_mode ? 'Test Mode' : 'Live Mode' }}
      </v-chip>
    </template>
    <template #item.is_international="{ item }">
      <v-chip size="small" label variant="tonal" :color="item.is_international ? 'info' : 'grey'">
        {{ item.is_international ? 'International' : 'Domestic' }}
      </v-chip>
    </template>
    <template #item.image_counts="{ item }">
      <div class="d-flex align-center ga-1">
        <v-icon size="14" color="medium-emphasis">mdi-image-multiple-outline</v-icon>
        <span class="caption-text">{{ item.image_counts ?? 0 }}</span>
      </div>
    </template>
    <template #item.created_at="{ item }">
      <span class="caption-text">{{ formatLongDate(item.created_at) ?? '-' }}</span>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex justify-end">
        <v-btn
          size="small"
          variant="tonal"
          color="primary"
          class="text-none"
          @click="router.push({ name: 'admin.paymentMethods.detail', params: { id: item.id } })">
          <v-icon start size="16">mdi-eye-outline</v-icon>
          Detail
        </v-btn>
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { listPaymentMethods, type PaymentMethodListItem } from '@/api/payment-methods.api';
import { formatLongDate } from '@/shared/utils';

const headers = [
  { title: 'Payment Method', key: 'name', sortable: false, minWidth: '240' },
  { title: 'Status', key: 'status', sortable: false, minWidth: '120' },
  { title: 'Mode', key: 'test_mode', sortable: false, minWidth: '130' },
  { title: 'Region', key: 'is_international', sortable: false, minWidth: '130' },
  { title: 'Assets', key: 'image_counts', sortable: false, minWidth: '100' },
  { title: 'Created At', key: 'created_at', sortable: false, minWidth: '180' },
  { title: 'Actions', key: 'action', sortable: false, minWidth: '110', align: 'end' as const },
];

const items = ref<PaymentMethodListItem[]>([]);
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
      ...method,
      status: Boolean(method.status),
      test_mode: Boolean(method.test_mode),
      is_international: Boolean(method.is_international),
      image_counts: Number(method.image_counts ?? 0),
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
