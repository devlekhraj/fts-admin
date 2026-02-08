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
                <td>Name</td>
                <td class="text-right">{{ data.guarantor_name || 'N/A' }}</td>
              </tr>
              <tr>
                <td>Email</td>
                <td class="text-right">{{ data.guarantor_email }}</td>
              </tr>
              <tr>
                <td>Phone</td>
                <td class="text-right">{{ data.guarantor_mobile }}</td>
              </tr>
              <tr>
                <td>Address</td>
                <td class="text-right">{{ data.guarantor_address }}</td>
              </tr>
              <tr>
                <td>Father's Name</td>
                <td class="text-right">{{ data.guarantor_father }}</td>
              </tr>
              <tr>
                <td>Grandfather's Name</td>
                <td class="text-right">{{ data.guarantor_grand_father }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
