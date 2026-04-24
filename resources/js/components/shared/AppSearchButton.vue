<template>
  <v-btn :color="color" :variant="variant" :height="height" :type="type" :disabled="isDisabled" v-bind="buttonAttrs"
    @click="onClick">
    <template #prepend>
      <v-progress-circular v-if="loading" indeterminate :size="spinnerSize" :width="spinnerWidth"
        :color="spinnerColor" />
      <v-icon v-else-if="icon" size="16">{{ icon }}</v-icon>
    </template>
    <slot>{{ text }}</slot>
  </v-btn>
</template>

<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import type { PropType } from 'vue';

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<{
  loading?: boolean;
  text?: string;
  icon?: string;
  spinnerColor?: string;
  spinnerSize?: number | string;
  spinnerWidth?: number | string;
  color?: string;
  variant?: 'flat' | 'text' | 'tonal' | 'outlined' | 'plain' | 'elevated';
  height?: number | string;
  type?: 'button' | 'submit' | 'reset';
}>(), {
  loading: false,
  text: 'Search',
  icon: 'mdi-magnify',
  spinnerColor: 'white',
  spinnerSize: 16,
  spinnerWidth: 2,
  color: 'primary',
  variant: 'flat',
  height: 38,
  type: 'button',
});

const emit = defineEmits<{ (e: 'click', event: MouseEvent): void }>();

const attrs = useAttrs() as Record<string, unknown>;

const isDisabled = computed(() => Boolean(props.loading) || Boolean(attrs.disabled));

const buttonAttrs = computed(() => {
  // We handle disabled ourselves (merge with loading) to preserve spinner state.
  const { disabled: _disabled, ...rest } = attrs;
  return rest;
});

function onClick(event: MouseEvent) {
  if (props.loading) return;
  emit('click', event);
}
</script>

