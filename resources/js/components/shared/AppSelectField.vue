<template>
  <v-select
    class="app-field"
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    :items="items"
    density="compact"
    variant="outlined"
    hide-details="auto"
    :clearable="clearable" />
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  modelValue?: unknown;
  items?: unknown[];
  density?: string;
  variant?: string;
  hideDetails?: boolean | string;
  clearable?: boolean;
}>(), {
  items: () => [],
  density: 'compact',
  variant: 'outlined',
  hideDetails: true,
  clearable: true,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: unknown): void;
}>();
</script>

<style scoped>
.app-field :deep(.v-field__input) {
  font-size: 0.82rem;
}

:global(.app-field .v-list-item-title) {
  font-size: 0.82rem;
}

:global(.app-field .v-list-item-subtitle),
:global(.app-field .v-list-item__content) {
  font-size: 0.82rem;
}
:deep(.app-field .v-list-item-title) {
  font-size: 0.82rem;
}
</style>
