<template>
  <AppPageHeader title="Edit Product" subtitle="Update product" />
  <v-card class="pa-6" max-width="900">
    <v-text-field label="Name" />
    <v-text-field label="Price" />
    <v-textarea label="Description" rows="4" />
    <v-btn color="primary">Update</v-btn>
  </v-card>
</template>

<script setup lang="ts">
import AppPageHeader from '@/components/AppPageHeader.vue';
</script>
