<template>
  <AppPageHeader :title="orderNumber" subtitle="Order Detail">
    <template #actions>

      <WarrantySerialCard :order-id="summary?.id ?? ''" :serial="summary?.warranty_token ?? null"
          @generated="handleWarrantyGenerated" />

      <StatusUpdateAction :order-id="summary?.id ?? ''" :current-status="orderStatus"
          @selected="handleStatusSelected" />

      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <!-- <v-card class="pa-6 pb-0"> -->
  <v-container fluid class="order-overview pa-0">
    <v-row>
      <v-col cols="12" lg="4">
        <v-card class="mb-4">
          <v-card-title>
            Customer Detail
          </v-card-title>
          <v-divider></v-divider>

          <div class="card customer-card">
            <div class="card-head">
              <v-avatar size="44" rounded="lg" color="grey-lighten-3">
                <v-img v-if="customerAvatar" :src="customerAvatar" cover />
                <v-icon v-else size="24" color="grey-darken-1">mdi-account-circle</v-icon>
              </v-avatar>
              <div>
                <div class="text-caption text-medium-emphasis">Customer</div>
                <h3 class="mb-1">{{ customerName }}</h3>
                <div class="text-body-2 text-medium-emphasis">Email: {{ customerEmail }}</div>
                <div class="text-body-2 text-medium-emphasis">Phone: {{ customerMobile }}</div>
              </div>
            </div>
          </div>
        </v-card>
        <v-card>
          <v-card-title>
            Order Progress
          </v-card-title>
          <v-divider></v-divider>
          <OrderTimeline :status-label="orderStatusLabel" :order-date-info="orderDateInfo" :updated-at="orderDateInfo" />
        </v-card>
      </v-col>
      <v-col cols="12" lg="8">
        <div>
          <v-row class="equal-cards" align="stretch">
            <v-col cols="12" md="6">
              <div class="card-wrapper">
                <v-card class="h-100 d-flex flex-column">
                  <v-card-title>
                    Shpped To
                  </v-card-title>
                  <v-divider></v-divider>
                  <v-card-text>
                    <div class="d-flex justify-space-between">
                      <div class="shipping-head">
                        <v-avatar size="44" rounded="lg" color="primary" variant="tonal">
                          <v-icon size="24" color="primary-darken-2">mdi-account-outline</v-icon>
                        </v-avatar>
                        <div>
                          <div class="text-caption text-medium-emphasis">Shipped To</div>
                          <h3 class="mb-1">{{ shippingRecipient }}</h3>
                          <div class="text-body-2 text-medium-emphasis">Email: n/a</div>
                          <div class="text-body-2 text-medium-emphasis">Phone: {{ shippingContact }}</div>
                        </div>
                      </div>

                    </div>
                  </v-card-text>
                </v-card>

              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="card-wrapper">
                <v-card class="h-100 d-flex flex-column">
                  <v-card-title>
                    Shipping Address
                  </v-card-title>
                  <v-divider></v-divider>
                  <v-card-text>
                    <div class="address-lines">
                      <div class="text-caption text-medium-emphasis d-flex align-center ga-2 mb-2">
                        <v-icon size="18" color="primary">mdi-map-marker-outline</v-icon>
                        <span>Shipping Address</span>
                      </div>
                      <div>{{ shippingLine1 }}</div>
                      <div class="text-medium-emphasis">{{ shippingLine2 }}</div>
                      <div class="text-medium-emphasis">{{ shippingLine3 }}</div>
                    </div>
                  </v-card-text>
                </v-card>

              </div>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-card>
                <v-card-title>
                  Order Items
                </v-card-title>
                <v-divider></v-divider>
                <div>
                  <section class="left-pane">
                    <v-table class="product-table" density="comfortable">
                      <thead>
                        <tr>
                          <th class="text-left">Item</th>
                          <th class="text-left">Quantity</th>
                          <th class="text-right">Price</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="row in orderItems" :key="row.id ?? row.product_name">
                          <td class="text-left text-body-2">
                            <div class="d-flex ga-3 align-center py-4">
                              <v-avatar size="48" rounded>
                                <v-img :src="row.product_thumb || 'https://placehold.co/96x96?text=Item'" cover />
                              </v-avatar>
                              <div class="d-flex flex-column ga-1">
                                <span class="product-title">{{ row.product_name || '-' }}</span>
                                <span v-if="row.sku" class="text-caption text-medium-emphasis">SKU: {{ row.sku }}</span>
                              </div>
                            </div>
                          </td>
                          <td class="text-left text-body-2">{{ row.quantity ?? 0 }}</td>
                          <td class="text-right text-body-2 font-weight-medium">
                            {{ formatNPR((row.price ?? 0) * (row.quantity ?? 1)) }}
                          </td>
                        </tr>
                      </tbody>
                    </v-table>
  
                    <v-divider class="my-5" />
  
                    <!-- <div class="section-head mb-3">
                      <h3>Payment Details</h3>
                      <v-chip size="small" color="success" variant="tonal">Paid</v-chip>
                    </div> -->
  
                    <div class="summary-table">
                      <div class="summary-row">
                        <span>Sub Total</span>
                        <strong>{{ formatNPR(paymentSummary.subtotal) }}</strong>
                      </div>
                      <!-- <div class="summary-row">
                          <span>Tax</span>
                          <strong>{{ formatNPR(paymentSummary.tax_total) }}</strong>
                        </div> -->
                      <div class="summary-row">
                        <span>Shipping Fee</span>
                        <strong>{{ formatNPR(paymentSummary.shipping_total) }}</strong>
                      </div>
                      <div class="summary-row">
                        <span>Discount</span>
                        <strong>{{ formatNPR(paymentSummary.discount_total) }}</strong>
                      </div>
                      <v-divider></v-divider>
                      <div class="summary-row total-row">
                        <span>Total</span>
                        <strong>{{ formatNPR(paymentSummary.total) }}</strong>
                      </div>
                      <v-divider></v-divider>
                      <div class="summary-row">
                        <span>Payment Type: </span>
                        <div class="paid-by text-right">
                          <p class="paid-by-method text-primary">{{ orderDetail?.total_summary?.payment_type ?? 'N/A' }}
                          </p>
                          <p class="paid-by-time text-medium-emphasis ">{{ orderDateInfo }}
                          </p>
                        </div>
                      </div>
  
                    </div>
                  </section>
                </div>
              
              </v-card>
            </v-col>
          </v-row>
        </div>

      </v-col>
    </v-row>
  </v-container>
  <!-- </v-card> -->
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { formatNPR } from '@/shared/formatters';
import router from '@/app/router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { formatDateLong } from '@/shared/utils';
import { getOrderDetails } from '@/api/orders.api';
import type { OrderDetailResponse } from '@/api/orders.api';
import WarrantySerialCard from '@/components/order/WarrantySerialCard.vue';
import StatusUpdateAction from '@/components/order/StatusUpdateAction.vue';
import OrderTimeline from '@/components/order/OrderTimeline.vue';

const route = useRoute();
const isLoading = ref(false);
const orderDetail = ref<OrderDetailResponse | null>(null);

async function loadOrder() {
  const id = route.params.id;
  if (!id) return;
  isLoading.value = true;
  try {
    orderDetail.value = await getOrderDetails(id as string);
  } catch (e) {
    console.error('Failed to load order details', e);
  } finally {
    isLoading.value = false;
  }
}

onMounted(loadOrder);

const customer = computed(() => orderDetail.value?.customer ?? null);
const customerAvatar = computed(() => String(customer.value?.avatar_url ?? '').trim());
const customerName = computed(() => customer.value?.name || '-');
const customerEmail = computed(() => customer.value?.email || '-');
const customerMobile = computed(() => customer.value?.mobile || '-');
const summary = computed(() => orderDetail.value?.summary ?? null);
const orderNumber = computed(() => summary.value?.order_no || summary.value?.invoice_no || `Order #${summary.value?.id ?? '-'}`);
const orderStatus = computed(() => summary.value?.status || '-');
const orderTotal = computed(() => {
  const total = orderDetail.value?.total_summary?.total ?? orderDetail.value?.total_summary?.sub_total;
  return total !== null && total !== undefined ? formatNPR(Number(total)) : '-';
});

const totalItems = computed(() => {
  const items = orderItems.value;
  if (!items.length) return 0;
  const sum = items.reduce((acc, item) => acc + Number(item.quantity ?? 0), 0);
  return sum || items.length;
});

const orderDateInfo = computed(() => (summary.value?.order_date ? formatDateLong(summary.value.order_date) : '-'));
const orderStatusLabel = computed(() => String(orderStatus.value || '').toLowerCase());


const shippingAddress = computed(() => {
  const raw = (orderDetail.value as Record<string, unknown> | null)?.shipping_address;
  return raw && typeof raw === 'object' ? (raw as Record<string, unknown>) : null;
});

const shippingRecipient = computed(() => orderDetail.value?.receipent?.name || customerName.value);

const shippingContact = computed(() => orderDetail.value?.receipent?.phone || customerMobile.value || '-');

const shippingLine1 = computed(() => String(shippingAddress.value?.landmark ?? '').trim() || '-');
const shippingLine2 = computed(() => {
  const city = String(shippingAddress.value?.city ?? '').trim();
  const district = String(shippingAddress.value?.district ?? '').trim();
  return [city, district].filter(Boolean).join(', ') || '-';
});
const shippingLine3 = computed(() => {
  const province = String(shippingAddress.value?.province ?? '').trim();
  const label = String(shippingAddress.value?.label ?? '').trim();
  return province || label || '-';
});

const orderItems = computed(() => {
  const items = (orderDetail.value as Record<string, unknown> | null)?.order_items;
  return Array.isArray(items) ? items : [];
});

const paymentSummary = computed(() => ({
  subtotal: Number(orderDetail.value?.total_summary?.sub_total ?? 0),
  tax_total: 0,
  discount_total: Number(orderDetail.value?.total_summary?.discount_total ?? 0),
  shipping_total: Number(orderDetail.value?.total_summary?.shipping_cost ?? 0),
  total: Number(orderDetail.value?.total_summary?.total ?? orderDetail.value?.total_summary?.sub_total ?? 0),
}));

function handleWarrantyGenerated(token: string) {
  if (orderDetail.value?.summary) {
    orderDetail.value.summary.warranty_token = token;
  }
}

function handleStatusSelected(payload: { status: string; orderId: string | number }) {
  if (orderDetail.value?.summary) {
    orderDetail.value.summary.status = payload.status;
  }
  // refresh full order to sync timeline and summary
  loadOrder();
}

function goBack() {
  router.push({ name: 'admin.orders.list' });
}
</script>

<style scoped>
.left-pane {
  border: 1px solid rgb(var(--v-theme-outline-variant));
  border-radius: 10px;
  padding: 14px;
}

.head-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.cancel-link {
  font-size: 0.86rem;
  text-decoration: underline;
  color: rgb(var(--v-theme-on-surface));
}


.warranty-code {
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.warranty-row {
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 12px;
}


.section-head h3 {
  font-size: 1rem;
  margin: 0;
}

.product-list {
  display: grid;
  gap: 12px;
}

.product-row {
  display: grid;
  grid-template-columns: 58px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
}

.product-title {
  font-size: 0.92rem;
  font-weight: 600;
}

.product-sub {
  font-size: 0.8rem;
  margin-top: 2px;
}

.product-price {
  font-size: 0.9rem;
  font-weight: 700;
  white-space: nowrap;
}

.summary-table {
  display: grid;
  gap: 10px;
  max-width: 420px;
  width: 100%;
  margin-left: auto;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  font-size: 0.9rem;
}

.paid-by {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.paid-by-method {
  font-size: 0.9rem;
  letter-spacing: 0.01em;
  line-height: 1.2;
}

.paid-by-time {
  /* margin: 4px 0 0; */
  font-size: 0.77rem;
  /* font-weight: 500; */
  /* line-height: 1.2; */
}

.total-row {
  padding-top: 8px;
  border-top: 1px solid rgb(var(--v-theme-outline-variant));
}

.top-grid {
  display: grid;
  gap: 14px;
}

.card {
  border: 1px solid rgb(var(--v-theme-outline-variant));
  border-radius: 10px;
  padding: 14px;
  background: rgb(var(--v-theme-surface));
}

.card-head,
.shipping-head {
  display: flex;
  align-items: center;
  gap: 12px;
}

.customer-meta {
  display: grid;
  gap: 8px;
}

.meta-row {
  display: flex;
  gap: 10px;
  font-size: 0.9rem;
}

.meta-label {}

.meta-value {
  font-weight: 600;
}

.address-lines div {
  font-size: 0.9rem;
  line-height: 1.4;
}

.order-card-actions {
  display: flex;
  gap: 12px;
  align-items: stretch;
  flex-wrap: wrap;
  justify-content: space-between;
}

.equal-cards .v-col > .card-wrapper,
.equal-cards .v-col > .card-wrapper > .v-card {
  height: 100%;
}

.equal-cards .card-wrapper {
  display: flex;
}

.equal-cards .card-wrapper > .v-card {
  flex: 1;
}

.timeline {
  list-style: none;
  padding: 0;
  margin: 0;
  position: relative;
}

.timeline-item {
  position: relative;
  padding-left: 40px;
}

/* Vertical line */
.timeline-item::before {
  content: '';
  position: absolute;
  left: 13px;
  top: 0;
  width: 2px;
  height: 100%;
  background: #dee2e6;
}

/* Active line */
.timeline-item.active::before {
  background: #0d6efd;
}

/* Marker */
.timeline-marker {
  position: absolute;
  left: 6px;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #dee2e6;
  border: 2px solid #fff;
  box-shadow: 0 0 0 2px #dee2e6;
}

/* Active marker */
.timeline-item.active .timeline-marker {
  background: #0d6efd;
  box-shadow: 0 0 0 2px #0d6efd;
}

/* Content */
.timeline-content {
  padding-bottom: 16px;
}

.timeline-title {
  font-weight: 600;
  margin-bottom: 4px;
}

.timeline-meta {
  font-size: 12px;
  color: #6c757d;
}

@media (min-width: 960px) {
  .top-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: start;
  }

  .customer-card {
    justify-self: start;
  }

}
.v-card .v-card-title {
    line-height: 1.6;
    font-weight: 600 !important;
    font-size: medium !important;
}
</style>
