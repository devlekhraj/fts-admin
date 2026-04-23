<template>
  <v-container fluid class="py-0">
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <div class="text-h5 font-weight-medium">{{ title }}</div>
        <div v-if="subtitle" class="text-body-2 text-medium-emphasis">{{ subtitle }}</div>
      </div>
      <div class="d-flex align-center ga-2">
        <slot name="actions" />
      </div>
    </div>
  </v-container>
</template>

<script setup lang="ts">
defineProps<{ title: string; subtitle?: string }>();
</script>
