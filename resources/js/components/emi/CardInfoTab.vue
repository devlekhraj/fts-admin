<template>
  <v-card class="pa-4">
    <v-row>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Card Holder</div>
        <div class="text-body-2">{{ data.card_holder_name }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Card Number</div>
        <div class="text-body-2">{{ data.card_number }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Expiry</div>
        <div class="text-body-2">{{ data.card_expiry_date }}</div>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
