<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="10" offset-lg="1">
        <div>
          <div class="pt-10">
            <div class="text-h6 mb-1">EMI Info</div>
            <div class="text-body-2 text-medium-emphasis mb-4">EMI request summary and payment breakdown.</div>
          </div>
          <v-table density="comfortable" class="border rounded">
            <tbody>
              <tr>
                <td>Request Code</td>
                <td class="text-right">{{ data.application_code }}</td>
              </tr>
              <tr>
                <td>EMI Type</td>
                <td class="text-right">n/a</td>
              </tr>
              <tr>
                <td>User</td>
                <td class="text-right">{{ data.user?.name || 'N/A' }}</td>
              </tr>
              <tr>
                <td>Product</td>
                <td class="text-right">{{ data.product?.name || 'N/A' }}</td>
              </tr>
              <tr>
                <td>Price</td>
                <td class="text-right">{{ formatNPR(data.product_price) }}</td>
              </tr>
              <tr>
                <td>Service Charge</td>
                <td class="text-right">{{ formatNPR(data.service_charge) }}</td>
              </tr>
              <tr>
                <td>EMI Amount</td>
                <td class="text-right">{{ formatNPR(data.emi_per_month) }}</td>
              </tr>
              <tr>
                <td>EMI Mode</td>
                <td class="text-right">{{ data.emi_mode }} Months</td>
              </tr>
              <tr v-if="data.emi_finance_id">
                <td>Finance From</td>
                <td class="text-right">{{ data.finance.name }}</td>
              </tr>
              <tr v-if="data.bank">
                <td>Bank Name</td>
                <td class="text-right">{{ data?.bankDetail?.name }}</td>
              </tr>
              <tr>
                <td>Applied On</td>
                <td class="text-right">{{ formatDateTime(data.created_at) }}</td>
              </tr>
            </tbody>
          </v-table>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { formatDateTime, formatNPR } from '@/shared/formatters';

const props = defineProps<{ data: Record<string, any> }>();

</script>

<style scoped></style>
