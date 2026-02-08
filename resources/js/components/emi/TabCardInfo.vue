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
                <td>Credit Card</td>
                <td class="text-right">{{ data.credit_card }}</td>
              </tr>
              <tr>
                <td>Card Holder Name</td>
                <td class="text-right">{{ data.card_holder_name }}</td>
              </tr>
              <tr>
                <td>Card Number</td>
                <td class="text-right">{{ data.card_number }}</td>
              </tr>
              <tr>
                <td>Card Limit</td>
                <td class="text-right"> {{ formatNPR(data.card_limit) }}</td>
              </tr>
              <tr>
                <td>Card Expiry</td>
                <td class="text-right">{{ data.card_expiry_date }}</td>
              </tr>
              <tr>
                <td>Bank</td>
                <td class="text-right">{{ data.bankDetail?.name }}</td>
              </tr>
              <tr>
                <td>Account Number</td>
                <td class="text-right">{{ data.account_number }}</td>
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
