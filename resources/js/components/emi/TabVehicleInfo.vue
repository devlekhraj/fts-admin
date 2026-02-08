<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <v-card class="pa-4">
          <v-table>
            <thead>
              <tr>
                <th>Field</th>
                <th class="text-right">Value</th>
              </tr>
            </thead>
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
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { formatNPR } from '@/shared/formatters';

defineProps<{ data: Record<string, any> }>();
</script>
