<template>
  <div class="card customer-card">
    <ul class="timeline">
      <li v-for="(step, index) in steps" :key="step" :class="[
        'timeline-item',
        {
          active: index <= activeIndex && !isCanceledActive(index, step),
          completed: step === 'Completed' && index <= activeIndex,
          canceled: isCanceledActive(index, step),
          pending: index > activeIndex && !isCanceledActive(index, step),
        },
      ]">
        <div class="timeline-marker" :class="{
          active: index <= activeIndex && !isCanceledActive(index, step),
          completed: step === 'Completed' && index <= activeIndex,
          canceled: isCanceledActive(index, step),
          pending: index > activeIndex && !isCanceledActive(index, step),
        }"></div>
        <div class="timeline-content">
          <div class="timeline-title">{{ step }}</div>
          <div class="timeline-meta" v-if="index === 0 && orderDateInfo">{{ orderDateInfo }}</div>
          <div class="timeline-meta" v-else-if="index === activeIndex">
            <span>Current status</span>
            <span v-if="updatedAt" class="meta-date"> · {{ updatedAt }}</span>
          </div>
          <div class="timeline-meta" v-else-if="index < activeIndex">
            Completed
          </div>
          <div class="timeline-meta" v-else>
            Pending
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  statusLabel?: string;
  orderDateInfo?: string;
  updatedAt?: string;
}>();

const steps = ['Draft', 'Placed', 'Confirmed', 'Dispatched', 'Completed', 'Canceled'];

const activeIndex = computed(() => {
  const label = String(props.statusLabel ?? '').toLowerCase();
  const idx = steps.findIndex((s) => s.toLowerCase() === label);
  return idx >= 0 ? idx : 0;
});

const isCanceledActive = (index: number, step: string) => step === 'Canceled' && index === activeIndex.value;
</script>

<style scoped>
.card {
  border: 1px solid rgb(var(--v-theme-outline-variant));
  border-radius: 10px;
  padding: 14px;
  background: rgb(var(--v-theme-surface));
}

.timeline {
  list-style: none;
  padding: 0;
  margin: 0;
}

.timeline-item {
  display: grid;
  grid-template-columns: 18px 1fr;
  gap: 10px;
  position: relative;
  padding-bottom: 14px;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid rgb(var(--v-theme-outline));
  background: rgb(var(--v-theme-surface));
  position: relative;
  top: 4px;
}

.timeline-item.active .timeline-marker,
.timeline-item.completed .timeline-marker {
  background: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}

.timeline-item.canceled .timeline-marker {
  background: rgb(var(--v-theme-error));
  border-color: rgb(var(--v-theme-error));
}

.timeline-item.pending .timeline-marker,
.timeline-marker.pending {
  background: rgb(var(--v-theme-surface-variant, '#c5c5c5'));
  border-color: rgb(var(--v-theme-outline-variant));
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 5px;
  top: 16px;
  bottom: 0;
  width: 2px;
  background: #c5c5c5;
}

.timeline-item.active::before,
.timeline-item.completed::before {
  background: rgb(var(--v-theme-primary));
}

.timeline-item.canceled::before {
  background: rgb(var(--v-theme-error));
}

.timeline-item.pending::before {
  background: #c5c5c5;
}

.timeline-item:last-child::before {
  display: none;
}

.timeline-title {
  font-weight: 500;
  font-size: 0.8rem;
}

.timeline-item.active .timeline-title {
  color: rgb(var(--v-theme-primary));
}

.timeline-meta {
  font-size: 0.82rem;
  color: #656565;
}
</style>
