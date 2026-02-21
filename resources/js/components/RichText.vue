<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue?: string
  disabled?: boolean
  placeholder?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: string): void
}>()

const value = computed({
  get: () => props.modelValue ?? '',
  set: (v: string) => emit('update:modelValue', v),
})
</script>

<template>
  <!-- VuetifyTiptap is globally registered by the plugin -->
  <VuetifyTiptap
    v-model="value"
    :disabled="disabled"
    :placeholder="placeholder || 'Write something…'"
  />
</template>
