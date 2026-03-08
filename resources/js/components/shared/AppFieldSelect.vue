<template>
  <v-select
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    :items="normalizedItems"
    variant="outlined"
    density="comfortable"
    :hide-details="hideDetails"
    :clearable="clearable"
    :menu-props="{ contentClass: menuClass }">
    <template #item="{ props: itemProps, item }">
      <v-list-item v-bind="itemProps" :title="toTitleCase(item.title)" />
    </template>
    <template #selection="{ item }">
      <span>{{ toTitleCase(item.title) }}</span>
    </template>
  </v-select>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  modelValue?: string | null;
  items?: unknown[];
  hideDetails?: boolean;
  clearable?: boolean;
  menuClass?: string;
}>(), {
  items: () => [],
  hideDetails: true,
  clearable: true,
  menuClass: 'app-field-select-menu',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | null): void;
}>();

const normalizedItems = computed<string[]>(() => {
  const seen = new Set<string>();
  return (props.items ?? [])
    .map((value) => String(value ?? '').trim())
    .filter((value) => {
      if (!value) return false;
      const key = canonicalKey(value);
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
});

function canonicalKey(value: unknown): string {
  return String(value ?? '')
    .normalize('NFKC')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();
}

function toTitleCase(value: unknown): string {
  return String(value ?? '')
    .toLowerCase()
    .replace(/\b\w/g, (char) => char.toUpperCase());
}
</script>

<style scoped>
:global(.app-field-select-menu .v-list-item-title) {
  font-size: 0.8rem;
}
</style>
