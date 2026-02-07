<template>
  <v-card class="pa-4">
    <v-row>
      <v-col cols="12" sm="6" md="4">
        <div class="text-caption text-medium-emphasis">Vehicle</div>
        <div class="text-body-2">{{ data.vehicle }}</div>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup lang="ts">
defineProps<{ data: Record<string, any> }>();
</script>
