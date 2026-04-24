<template>
  <AppPageHeader title="Wish Pot" subtitle="Wishlist items" />
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
                placeholder="Search by customer/product..."
                prepend-inner-icon="mdi-magnify"
                hide-details
                clearable
                style="min-width: 360px"
                @keyup.enter="onSearch"
                @click:clear="onClearSearch" />
              <AppSearchButton :loading="loading" @click="onSearch" />
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
          <v-img v-if="item.customerAvatar" :src="item.customerAvatar" :alt="item.customerName" cover />
          <v-icon v-else size="16" color="grey-darken-1">mdi-account-circle</v-icon>
        </v-avatar>
        <span>{{ item.customerName ?? '-' }}</span>
      </div>
    </template>

    <template #item.product="{ item }">
      <div class="d-flex align-center ga-2">
        <v-avatar size="28" color="grey-lighten-3" rounded>
          <v-img v-if="item.productThumb" :src="item.productThumb" :alt="item.productName" cover />
          <v-icon v-else size="16" color="grey-darken-1">mdi-image-outline</v-icon>
        </v-avatar>
        <span>{{ item.productName ?? '-' }}</span>
      </div>
    </template>

    <template #item.addedAt="{ item }">
      <span class="text-medium-emphasis">{{ item.addedAt }}</span>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { timeAgo } from '@/shared/utils';
import { listWishlists, type WishlistListItem } from '@/api/wishlists.api';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSearchButton from '@/components/shared/AppSearchButton.vue';

const headers = [
  { title: 'SN#', key: 'sn', width: '80' },
  { title: 'Customer', key: 'customer' },
  { title: 'Product', key: 'product' },
  { title: 'Created At', key: 'addedAt' },
];

type WishRow = {
  id: number | string;
  sn: number;
  customerName: string;
  customerAvatar?: string | null;
  productName: string;
  productThumb?: string | null;
  addedAt: string;
};

const items = ref<WishRow[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');
const options = ref<DataTableOptions>({ page: 1, itemsPerPage: 10, sortBy: [] });

function onSearch() {
  options.value.page = 1;
  fetchWishlists();
}

function onClearSearch() {
  search.value = '';
  options.value.page = 1;
  fetchWishlists();
}

async function fetchWishlists() {
  loading.value = true;
  try {
    const response = await listWishlists({
      page: options.value.page,
      per_page: options.value.itemsPerPage,
      search: search.value.trim() || undefined,
    });

    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list.map((wish: WishlistListItem, index) => ({
      id: wish.id ?? `${wish.user_id}-${wish.product_id}`,
      sn: (options.value.page - 1) * options.value.itemsPerPage + index + 1,
      customerName: wish.customer?.name ?? '-',
      customerAvatar: wish.customer?.avatar ?? null,
      productName: wish.product?.name ?? '-',
      productThumb: wish.product?.thumb ?? null,
      addedAt: wish.created_at ? timeAgo(wish.created_at) : '-',
    }));

    total.value = Number(response?.meta?.total ?? list.length);
    if (response?.meta?.current_page) options.value.page = Number(response.meta.current_page);
    if (response?.meta?.per_page) options.value.itemsPerPage = Number(response.meta.per_page);
  } finally {
    loading.value = false;
  }
}

function onOptions(next: DataTableOptions) {
  options.value = next;
  fetchWishlists();
}

onMounted(fetchWishlists);
</script>
