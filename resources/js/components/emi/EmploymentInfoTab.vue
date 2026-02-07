<template>
  <v-card class="pa-4">
    <v-row>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Occupation</div>
        <div class="text-body-2">{{ data.occupation }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Length of Employment</div>
        <div class="text-body-2">{{ data.length_of_employment }}</div>
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Monthly Income</div>
        <div class="text-body-2">{{ data.monthly_income }}</div>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
