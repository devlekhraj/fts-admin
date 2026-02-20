<template>
  <RouterView />
  <GlobalModal />
  <GlobalSnackbar />
</template>

<script setup lang="ts">
import GlobalModal from '@/components/GlobalModal.vue';
import GlobalSnackbar from '@/components/GlobalSnackbar.vue';
</script>
