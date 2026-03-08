<template>
  <v-textarea
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    variant="outlined"
    density="comfortable"
    auto-grow
    :rows="rows"
    :hide-details="hideDetails"
    :placeholder="placeholder" />
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  modelValue?: unknown;
  rows?: number | string;
  hideDetails?: boolean;
  placeholder?: string;
}>(), {
  rows: 3,
  hideDetails: true,
  placeholder: '',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: unknown): void;
}>();
</script>
