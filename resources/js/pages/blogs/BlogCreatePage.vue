<template>
  <AppPageHeader title="Create Blog" subtitle="New post" />
  <v-card class="pa-6" max-width="900">
    <v-text-field label="Title" />
    <v-textarea label="Body" rows="6" />
    <v-btn color="primary">Save</v-btn>
  </v-card>
</template>

<script setup lang="ts">
import AppPageHeader from '@/components/AppPageHeader.vue';
</script>
