<template>
  <v-container class="pt-0" fluid>
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div>
          <div class="pt-10">
            <div class="text-h6 mb-1">Vehicle Info</div>
            <div class="text-body-2 text-medium-emphasis mb-4">Vehicle and financing details for this EMI request.</div>
          </div>
             <v-table density="comfortable" class="rounded border">
           
            <tbody>
              <tr>
                <td>Vehicle</td>
                <td class="text-right">{{ data.vehicle }}</td>
              </tr>
              <tr>
                <td>Down Payment</td>
                <td class="text-right"> {{ formatNPR(data.down_payment) }}</td>
              </tr>
              <tr>
                <td>Finance Amount</td>
                <td class="text-right"> {{ formatNPR(data.finance_amount) }}</td>
              </tr>
              <tr>
                <td>Finance Type</td>
                <td class="text-right">{{ data.emi_type }}</td>
              </tr>
              <tr>
                <td>Residental Status</td>
                <td class="text-right">{{ data.residental_status }}</td>
              </tr>
            </tbody>
          </v-table>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { formatNPR } from '@/shared/formatters';

defineProps<{ data: Record<string, any> }>();
</script>
