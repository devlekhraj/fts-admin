<template>
  <AppPageHeader title="Cart Items" :subtitle="subtitle" />
  <AppDataTable
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    :page="options.page"
    :items-per-page="options.itemsPerPage"
    @update:options="onOptions">
    <template #actions>
      <v-container fluid class="py-4">
        <v-row align="center">
          <v-col cols="12" md="6" lg="4">
            <div class="d-flex align-center ga-3">
              <AppTextField
                v-model="search"
                label=""
                placeholder="Search ..."
                prepend-inner-icon="mdi-magnify"
                hide-details
                clearable
                style="min-width: 360px"
                @keyup.enter="onSearch"
                @click:clear="onClearSearch" />
                <div>
                  <AppSearchButton :loading="fetchingState" @click="onSearch" />
                </div>
            </div>
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
    <template #item.customer="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.avatar" :src="item.avatar" :alt="item.customer" cover />
          <v-icon v-else size="16" color="grey-darken-1">mdi-account-circle</v-icon>
        </v-avatar>
        <span class="text-capitalize">{{ item.customer ?? '-' }}</span>
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
    <template #item.action="{ item }" class="justify-end">
      <v-btn size="small" variant="outlined" color="primary" @click="onView(item)">
        Details
      </v-btn>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { timeAgo } from '@/shared/utils';
import { listCarts, type CartListItem } from '@/api/carts.api';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';

const headers = [
  { title: 'Name', key: 'customer' ,minWidth:'250'},
  { title: 'Items', key: 'items',minWidth:'150' },
  { title: 'Status', key: 'status', minWidth:'120' },
  { title: 'Updated', key: 'updatedAt', minWidth:'150' },
  { title: 'Action', key: 'action', width:'120', align: 'end' as const },
];

const router = useRouter();
const fetchingState = ref(false);
const items = ref<{ id: number | string; customer: string; avatar?: string | null; items: number; updatedAt: string; isProceed: boolean }[]>([]);
const total = ref(0);
const activeTotal = ref<number | null>(null);
const loading = ref(false);
const search = ref('');
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 10, sortBy: [] });

const subtitle = computed(() => {
  if (typeof activeTotal.value === 'number') return `Active carts (${activeTotal.value})`;
  return 'Active carts';
});

function onView(item: any) {
  router.push({ name: 'admin.orders.cart.detail', params: { id: item.id } });
}

function onSearch() {
  options.value.page = 1;
  fetchingState.value = true;
  fetchCarts();
}

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchCarts();
}

async function fetchCarts() {
  loading.value = true;
  try {
    const response = await listCarts({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
    });
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
    activeTotal.value = typeof response?.meta?.active_total === 'number'
      ? Number(response.meta.active_total)
      : items.value.filter((cart) => !cart.isProceed).length;
    if (response?.meta?.current_page) options.value.page = Number(response.meta.current_page);
    if (response?.meta?.per_page) options.value.itemsPerPage = Number(response.meta.per_page);
  } finally {
    fetchingState.value = false;
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  fetchCarts();
}

onMounted(fetchCarts);
</script>
