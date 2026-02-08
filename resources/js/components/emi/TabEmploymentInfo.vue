<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="border rounded">
          <v-table>
            <tbody>
              <tr>
                <td>Occupation</td>
                <td class="text-right">{{ data.occupation }}</td>
              </tr>
              <tr>
                <td>Occupation Type</td>
                <td class="text-right">{{ data.occupation_type }}</td>
              </tr>
              <tr>
                <td>Length of Employment</td>
                <td class="text-right">{{ data.length_of_employment }} yrs</td>
              </tr>
              <tr>
                <td>Monthly Income</td>
                <td class="text-right">Rs. {{ formatNPR(data.monthly_income) }}</td>
              </tr>
              <tr>
                <td>Monthly Expenses</td>
                <td class="text-right">Rs. {{ formatNPR(data.monthly_expenses) }}</td>
              </tr>
              <tr>
                <td>No. of Dependents</td>
                <td class="text-right">{{ data.no_of_dependents }}</td>
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
