<template>
  <v-card class="pa-4">
    <v-row>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis">EMI Type</div>
        <div class="text-body-2 text-capitalize">{{ data.emi_type }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis">EMI Mode</div>
        <div class="text-body-2 text-capitalize">{{ data.emi_mode }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis">Per Month</div>
        <div class="text-body-2">{{ data.emi_per_month }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <div class="text-caption text-medium-emphasis">Down Payment</div>
        <div class="text-body-2">{{ data.down_payment }}</div>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
