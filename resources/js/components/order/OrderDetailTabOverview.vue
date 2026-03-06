<template>
  <v-container fluid class="order-overview pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <section class="left-pane">
        <div class="head-actions">
          <!-- <a href="#" class="cancel-link">Cancel Order</a> -->
          <v-btn color="warning"  variant="flat" prepend-icon="mdi-shield-plus-outline">
            Create Warrenty Serial
          </v-btn>
        </div>

        <div class="warranty-box mt-4">
          <div>
            <div class="text-caption text-medium-emphasis">Warranty Serial</div>
            <div class="warranty-row">
              <div class="warranty-code text-success">{{ warrantySerial }}</div>
              <v-btn size="x-small" color="primary" :icon="'mdi-content-copy'" variant="tonal"></v-btn>
            </div>
          </div>
        </div>

        <v-divider class="my-5" />

        <div class="section-head mb-3">
          <h3>Products</h3>
       
        </div>

        <div class="product-list">
          <article v-for="row in orderItems" :key="row.id" class="product-row">
            <v-avatar size="58" rounded>
              <v-img :src="row.image" cover />
            </v-avatar>
            <div class="product-main">
              <div class="product-title">{{ row.name }}</div>
              <div class="product-sub text-medium-emphasis">SKU: {{ row.sku }}</div>
              <div class="product-sub text-medium-emphasis">{{ row.variant }} · Quantity {{ row.qty }}</div>
            </div>
            <div class="product-price">{{ formatNPR(row.subtotal) }}</div>
          </article>
        </div>

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
          <div class="summary-row">
            <span>Tax</span>
            <strong>{{ formatNPR(paymentSummary.tax_total) }}</strong>
          </div>
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
            <strong>{{ formatNPR(item?.total ?? paymentSummary.total) }}</strong>
          </div>
          <v-divider></v-divider>
          <div class="summary-row">
            <span>Paid By: </span>
            <div class="paid-by text-right">
              <p class="paid-by-method text-primary">{{ item?.payment_method?.name ?? 'Cash / eSewa' }}</p>
              <p class="paid-by-time text-medium-emphasis ">{{ formatDateTime(item?.paid_at ?? item?.created_at) }}</p>
            </div>
          </div>
         
        </div>
        </section>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { OrderDetailResponse } from '@/api/orders.api';
import { formatDateTime, formatNPR } from '@/shared/formatters';
defineProps<{
  item: OrderDetailResponse | null;
}>();

const orderItems = ref([
  {
    id: 'i1',
    name: 'Macbook Pro 14 Inch 512GB M1 Pro',
    sku: 'Mac-1000',
    variant: 'Grey',
    qty: 1,
    subtotal: 1659.25,
    image: 'https://placehold.co/120x120?text=Macbook',
  },
  {
    id: 'i2',
    name: 'APPLE 32" R6KD Pro Display XDR',
    sku: 'Mac-5006',
    variant: 'Silver',
    qty: 1,
    subtotal: 5848.77,
    image: 'https://placehold.co/120x120?text=Display',
  },
]);

const paymentSummary = ref({
  subtotal: 7508.02,
  tax_total: 0,
  discount_total: -20,
  shipping_total: 20,
  total: 7508.02,
});

const warrantySerial = ref('WS-2026-ORD5012-01');
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

</style>
