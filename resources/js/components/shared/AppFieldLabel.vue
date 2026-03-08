<template>
  <div class="text-caption text-medium-emphasis mb-1">{{ label }}</div>
</template>

<script setup lang="ts">
defineProps<{
  label: string;
}>();
</script>
