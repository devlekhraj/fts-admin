<script setup lang="ts">
import { computed } from 'vue'

type ImageForm = {
    src?: string
    alt?: string
    caption?: string
    width?: number | string
    lockAspectRatio?: boolean
}

const props = withDefaults(defineProps<{
    modelValue?: ImageForm
}>(), {
    modelValue: () => ({}),
})

const emit = defineEmits<{
    (e: 'update:modelValue', value: ImageForm): void
}>()

const form = computed({
    get: () => props.modelValue ?? {},
    set: (value) => emit('update:modelValue', value),
})
</script>

<template>
    <div class="d-flex flex-column ga-3 pa-2">
        <v-text-field
            v-model="form.src"
            label="Image URL"
            placeholder="https://example.com/image.jpg"
        />

        <v-text-field
            v-model="form.alt"
            label="Alt text"
            placeholder="Describe the image for accessibility"
        />

        <v-text-field
            v-model="form.caption"
            label="Caption"
            placeholder="Visible caption below image"
        />
    </div>
</template>