<template>
  <ul class="timeline pa-4">
    <li v-for="(item, idx) in items" :key="idx" class="timeline__item">
      <v-icon size="16" class="dot" color="primary">mdi-check-circle</v-icon>
      <div>
        <div class="font-weight-medium" style="font-size: 0.8rem; font-weight: 400; color: #2467c0;">{{ item.title }}</div>
        <div class="text-caption text-medium-emphasis">{{ item.subtitle }}</div>
        <div class="text-caption text-medium-emphasis">{{ item.time }}</div>
      </div>
    </li>
  </ul>
</template>

<script setup lang="ts">
defineProps<{
  items: Array<{ title: string; subtitle?: string; time?: string }>;
}>();
</script>

<style scoped>
.timeline {
  list-style: none;
  margin: 0;
  padding: 0;
}

.timeline__item {
  display: grid;
  grid-template-columns: 16px 1fr;
  gap: 10px;
  position: relative;
  padding-bottom: 14px;
}

.timeline__item:last-child {
  padding-bottom: 0;
}

.timeline__item::before {
  content: '';
  position: absolute;
  left: 7px;
  top: 12px;
  bottom: -2px;
  width: 2px;
  background: #b1dbff;
}

.timeline__item:last-child::before {
  display: none;
}

.dot {
  position: relative;
  top: 1px;
}
</style>
