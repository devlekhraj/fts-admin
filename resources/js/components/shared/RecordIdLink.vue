<template>
  <router-link :to="to" class="record-id-link" :title="title ?? 'Open details'">
    <slot>{{ label }}</slot>
  </router-link>
</template>

<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router';

// A request, order or application number in a list that opens its detail page.
defineProps<{ to: RouteLocationRaw; label?: string | number; title?: string }>();
</script>

<style scoped>
.record-id-link {
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  text-decoration: none;
  white-space: nowrap;
}
.record-id-link:hover,
.record-id-link:focus-visible {
  text-decoration: underline;
}
</style>
