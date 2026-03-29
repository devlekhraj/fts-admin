<template>
  <AppPageHeader title="Wish Detail" :subtitle="`Wish #${id}`" />
  <v-card flat class="pa-4">
    <div class="text-body-1">Details coming soon.</div>
  </v-card>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';

const route = useRoute();
const id = route.params.id as string | number;
</script>
