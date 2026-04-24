<template>
    <div>
        <v-text-field :model-value="attrs.modelValue" 
        :label="label" prepend-inner-icon="mdi-magnify" clearable style="width: 300px"
        @update:model-value="emit('update:modelValue', $event)"
            v-bind="fieldAttrs" :placeholder="placeholder" variant="outlined" density="compact" hide-details="auto" />
    </div>
</template>

<script setup lang="ts">
import { computed, useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });

defineProps<{
    label: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: unknown): void;
}>();

const attrs = useAttrs() as Record<string, unknown>;
const fieldAttrs = computed(() => {
    const { label: _label, modelValue: _modelValue, 'onUpdate:modelValue': _onUpdateModelValue, ...rest } = attrs;

    return rest;
});
</script>
