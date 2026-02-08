<template>
  <v-container class="pt-0" fluid>
    <v-card>
      <!-- <div class="d-flex justify-end mb-3">
        <v-btn size="small" variant="tonal" @click="openModal">Open Modal</v-btn>
      </div> -->
      <div>
        <div class="emi-info-grid">
          <div class="pt-4">
            <v-table>
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
          <div class="emi-info-divider"></div>
          <div class="pt-4">
            <GenerateApplicationForm :data="data" />
          </div>
        </div>
      </div>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { formatDateTime, formatNPR } from '@/shared/formatters';
// import { useModalStore } from '@/stores/modal.store';
// import DemoTemp from '@/components/emi/DemoTemp.vue';
import GenerateApplicationForm from '@/components/emi/EmiBankApplicationList.vue';

const props = defineProps<{ data: Record<string, any> }>();
// const modal = useModalStore();

// function openModal() {
//   modal.open(
//     DemoTemp,
//     { id: props.data?.id },
//     {
//       size: 'md',
//       // onSaved: (payload) => {
//       //   console.log('saved from modal', {payload});
//       // },
//     },
//   );
// }

</script>

<style scoped>
.emi-info-grid {
  display: grid;
  grid-template-columns: 1fr 1px 2fr;
  gap: 16px;
  align-items: start;
}

.emi-info-divider {
  background: rgba(0, 0, 0, 0.12);
  min-height: 100%;
}

@media (max-width: 960px) {
  .emi-info-grid {
    grid-template-columns: 1fr;
  }

  .emi-info-divider {
    display: none;
  }
}
</style>
