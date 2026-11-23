<template>
  <v-container class="d-flex justify-center py-12">
    <v-card max-width="520" class="text-center pa-8" variant="outlined">
      <v-avatar color="warning" variant="tonal" size="72" class="mb-4">
        <v-icon icon="mdi-lock-outline" size="36" />
      </v-avatar>
      <div class="text-h5 font-weight-bold mb-2">You do not have access to this page</div>
      <p class="text-body-1 text-medium-emphasis mb-1">
        Your role{{ roleName ? ` (${roleName})` : '' }} does not include it.
      </p>
      <p class="text-body-2 text-medium-emphasis mb-6">
        If you need it, ask an administrator to update your role.
      </p>
      <v-btn color="primary" variant="flat" :to="{ name: 'admin.overview' }" prepend-icon="mdi-home-outline">Go to the dashboard</v-btn>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth.store';

const auth = useAuthStore();
const roleName = computed(() => (typeof auth.admin?.role === 'string' ? auth.admin.role : ''));
</script>
