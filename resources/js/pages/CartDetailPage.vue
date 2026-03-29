<template>
  <AppPageHeader title="Cart Detail" :subtitle="`Cart #${id}`">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="$router.push({ name: 'admin.orders.cart' })">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>
  <v-card flat class="pa-4 py-10" :loading="loading">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
          <div class="d-flex align-center ga-3">
            <v-avatar size="40" color="grey-lighten-3" rounded>
              <v-img v-if="cart?.customer?.avatar" :src="cart.customer.avatar" :alt="cart?.customer?.name ?? 'Customer'"
                cover />
              <v-icon v-else size="24" color="grey-darken-1">mdi-account-circle</v-icon>
            </v-avatar>
            <div class="">
              <div class="font-weight-medium">{{ cart?.customer?.name ?? '-' }}</div>
            <v-chip :color="cart?.is_proceed ? 'success' : 'warning'" label size="small">{{
              cart?.is_proceed ? 'Processed' : 'Pending' }}</v-chip>
            </div>
          </div>
          <div class="d-flex align-center ga-3">
            <span class="text-medium-emphasis updated-text">Updated {{ cart?.updated_at ? timeAgo(cart.updated_at) : '-'
            }}</span>
          </div>
        </div>
        <v-divider></v-divider>
        <div class="py-10">
          <h3>Cart Items:</h3>
          <v-table density="comfortable" class="mb-4 border rounded" style="font-size: 0.82rem;">
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
                <td>
                  <div class="d-flex align-center ga-3 py-2">
                    <v-avatar size="100" color="grey-lighten-4" rounded>
                      <v-img v-if="item?.thumb" :src="String(item.thumb)" :alt="item.description" cover />
                      <v-icon v-else size="18" color="grey-darken-1">mdi-image-off-outline</v-icon>
                    </v-avatar>
                    <div class="d-flex flex-column">
                      <span>{{ item.description }}</span>
                      <div v-if="item.product_attributes && Object.keys(item.product_attributes).length" class="text-caption text-medium-emphasis">
                        <span v-for="(attrEntry, idx) in Object.entries(item.product_attributes)" :key="`${item.id}-${attrEntry[0]}`">
                          <strong>{{ attrEntry[0] }}:</strong> {{ attrEntry[1] }}<span v-if="idx < Object.entries(item.product_attributes).length - 1"> • </span>
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="text-right">{{ item.quantity }}</td>
                <td class="text-right">{{ formatNPR(item.price) }}</td>
                <td class="text-right">{{ formatNPR(item.line_total) }}</td>
              </tr>

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
        </div>
      </v-col>
    </v-row>

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

<style scoped>
.updated-text {
  font-size: 0.82rem;
}
</style>
