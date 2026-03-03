<template>
  <v-container fluid class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <v-card variant="outlined" class="pa-5">
          <div class="d-flex align-start justify-space-between ga-3 flex-wrap">
            <div>
              <div class="text-overline text-medium-emphasis">Invoice</div>
              <div class="text-h6">{{ demoInvoice.number }}</div>
              <div class="text-body-2 text-medium-emphasis mt-1">Date: {{ formatDateTime(demoInvoice.date) }}</div>
            </div>
            <v-btn color="primary" prepend-icon="mdi-download" @click="downloadInvoice">
              Download Invoice
            </v-btn>
          </div>

          <v-divider class="my-4" />

          <v-row>
            <v-col cols="12" md="6">
              <div class="text-caption text-medium-emphasis">Bill To</div>
              <div class="text-body-1 font-weight-medium">{{ demoInvoice.billTo.name }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ demoInvoice.billTo.email }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ demoInvoice.billTo.phone }}</div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="text-caption text-medium-emphasis">Payment</div>
              <div class="text-body-2">Method: {{ demoInvoice.payment.method }}</div>
              <div class="text-body-2">Status: {{ demoInvoice.payment.status }}</div>
              <div class="text-body-2 text-medium-emphasis">Paid At: {{ formatDateTime(demoInvoice.payment.paidAt) }}</div>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <v-table density="comfortable">
            <thead>
              <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="line in demoInvoice.items" :key="line.name">
                <td>{{ line.name }}</td>
                <td class="text-right">{{ line.qty }}</td>
                <td class="text-right">{{ formatNPR(line.price) }}</td>
                <td class="text-right">{{ formatNPR(line.qty * line.price) }}</td>
              </tr>
            </tbody>
          </v-table>

          <div class="invoice-summary mt-4">
            <div class="invoice-row">
              <span>Sub Total</span>
              <strong>{{ formatNPR(demoInvoice.subtotal) }}</strong>
            </div>
            <div class="invoice-row">
              <span>Tax</span>
              <strong>{{ formatNPR(demoInvoice.tax) }}</strong>
            </div>
            <div class="invoice-row">
              <span>Shipping Fee</span>
              <strong>{{ formatNPR(demoInvoice.shipping) }}</strong>
            </div>
            <div class="invoice-row">
              <span>Discount</span>
              <strong>{{ formatNPR(demoInvoice.discount) }}</strong>
            </div>
            <div class="invoice-row total-row">
              <span>Total</span>
              <strong>{{ formatNPR(demoInvoice.total) }}</strong>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { formatDateTime, formatNPR } from '@/shared/formatters';

const demoInvoice = {
  number: 'INV-2026-000512',
  date: '2026-03-04T14:20:00.000000Z',
  billTo: {
    name: 'Riya Shrestha',
    email: 'riya@example.com',
    phone: '9861234567',
  },
  payment: {
    method: 'eSewa',
    status: 'Paid',
    paidAt: '2026-03-04T14:21:00.000000Z',
  },
  items: [
    { name: 'Macbook Pro 14 Inch 512GB M1 Pro', qty: 1, price: 1659.25 },
    { name: 'APPLE 32" R6KD Pro Display XDR', qty: 1, price: 5848.77 },
  ],
  subtotal: 7508.02,
  tax: 0,
  shipping: 20,
  discount: -20,
  total: 7508.02,
};

function downloadInvoice(): void {
  const content = [
    `Invoice Number: ${demoInvoice.number}`,
    `Date: ${formatDateTime(demoInvoice.date)}`,
    `Customer: ${demoInvoice.billTo.name}`,
    `Payment Method: ${demoInvoice.payment.method}`,
    `Payment Status: ${demoInvoice.payment.status}`,
    `Total: ${formatNPR(demoInvoice.total)}`,
  ].join('\n');

  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `${demoInvoice.number}.txt`;
  link.click();
  URL.revokeObjectURL(url);
}
</script>

<style scoped>
.invoice-summary {
  max-width: 340px;
  margin-left: auto;
  display: grid;
  gap: 8px;
}

.invoice-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  font-size: 0.92rem;
}

.total-row {
  border-top: 1px solid rgb(var(--v-theme-outline-variant));
  padding-top: 8px;
  margin-top: 2px;
}
</style>
