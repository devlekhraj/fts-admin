<template>
  <AppPageHeader title="Settings" subtitle="System settings" />
  <v-card class="pa-6" max-width="900">
    <v-text-field label="Site Name" />
    <v-text-field label="Support Email" />
    <v-btn color="primary">Save</v-btn>
  </v-card>
</template>

<script setup lang="ts">
import AppPageHeader from '@/components/AppPageHeader.vue';
</script>
