<template>
  <v-text-field
    class="app-field"
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    :density="density"
    :variant="variant"
    :hide-details="hideDetails" />
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  modelValue?: unknown;
  density?: string;
  variant?: string;
  hideDetails?: boolean | string;
}>(), {
  density: 'compact',
  variant: 'outlined',
  hideDetails: true,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: unknown): void;
}>();
</script>

<style scoped>
.app-field :deep(.v-field__input) {
  font-size: 0.82rem;
}
</style>
