<template>
  <AppPageHeader title="Cart Detail" :subtitle="`Cart #${id}`">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="$router.push({ name: 'admin.orders.cart' })">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>
  <v-card flat class="pa-4" :loading="loading">
    <div class="d-flex align-center ga-3 mb-4">
      <v-avatar size="40" color="grey-lighten-3" rounded>
        <v-img v-if="cart?.customer?.avatar" :src="cart.customer.avatar" :alt="cart?.customer?.name ?? 'Customer'" cover />
        <v-icon v-else size="24" color="grey-darken-1">mdi-account-circle</v-icon>
      </v-avatar>
      <div class="d-flex flex-column">
        <div class="font-weight-medium">{{ cart?.customer?.name ?? '-' }}</div>
        <div class="text-caption text-medium-emphasis">{{ cart?.customer?.address ?? '-' }}</div>
      </div>
    </div>

    <v-table density="comfortable" class="mb-4">
      <thead>
        <tr>
          <th class="text-left">Item</th>
          <th class="text-right">Qty</th>
          <th class="text-right">Price</th>
          <th class="text-right">Line Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in cart?.items ?? []" :key="item.id">
          <td>{{ item.description }}</td>
          <td class="text-right">{{ item.quantity }}</td>
          <td class="text-right">{{ formatNPR(item.price) }}</td>
          <td class="text-right">{{ formatNPR(item.line_total) }}</td>
        </tr>
        <template v-for="item in cart?.items ?? []" :key="`${item.id}-attrs`">
          <tr v-if="item.product_attributes && Object.keys(item.product_attributes).length">
            <td colspan="4" class="ps-6 text-caption text-medium-emphasis">
              <span v-for="(val, key, idx) in item.product_attributes" :key="`${item.id}-${key}`">
                <strong>{{ key }}:</strong> {{ val }}<span v-if="idx < Object.keys(item.product_attributes).length - 1"> • </span>
              </span>
            </td>
          </tr>
        </template>
        <tr v-if="(cart?.items?.length ?? 0) === 0">
          <td colspan="4" class="text-center text-medium-emphasis py-4">No items found.</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="text-right font-weight-medium">Total</td>
          <td class="text-right font-weight-medium">{{ formatNPR(cart?.total ?? 0) }}</td>
        </tr>
      </tfoot>
    </v-table>

    <div class="d-flex align-center ga-3">
      <v-chip size="small" label variant="tonal" :color="cart?.is_proceed ? 'success' : 'error'">
        {{ cart?.is_proceed ? 'Yes' : 'No' }}
      </v-chip>
      <span class="text-medium-emphasis">Updated {{ cart?.updated_at ? timeAgo(cart.updated_at) : '-' }}</span>
    </div>
  </v-card>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getCart, type CartDetail } from '@/api/carts.api';
import { timeAgo } from '@/shared/utils';
import { formatNPR } from '@/shared/formatters';

const route = useRoute();
const id = route.params.id as string | number;
const cart = ref<CartDetail | null>(null);
const loading = ref(false);


async function fetchCart() {
  loading.value = true;
  try {
    const response = await getCart(id);
    cart.value = response?.data ?? null;
  } finally {
    loading.value = false;
  }
}

onMounted(fetchCart);
</script>
