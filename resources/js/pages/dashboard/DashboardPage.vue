<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h5 mb-2">Dashboard</h1>
        <div v-if="admin" class="text-body-2">
          {{ admin.name ?? 'Admin' }} ({{ admin.email ?? 'no-email' }})
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '../../stores/auth.store';

const authStore = useAuthStore();
const admin = computed(() => authStore.admin);
</script>
