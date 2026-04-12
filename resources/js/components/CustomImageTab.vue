<script setup lang="ts">
import { reactive, ref, watch } from 'vue'

type ImageForm = {
    src?: string
    alt?: string
    caption?: string
    width?: number | string
    lockAspectRatio?: boolean
}

const props = withDefaults(defineProps<{
    modelValue?: ImageForm
    upload?: (file: File) => Promise<string>
    t?: (key: string) => string
}>(), {
    modelValue: () => ({}),
    upload: undefined,
})

const emit = defineEmits<{
    (e: 'update:modelValue', value: ImageForm): void
}>()

const uploading = ref(false)
const error = ref<string | null>(null)

const form = reactive<ImageForm>({})

watch(
    () => props.modelValue,
    (val) => {
        const next = val ?? {}
        Object.assign(form, {
            src: next.src ?? '',
            alt: next.alt ?? '',
            caption: next.caption ?? '',
            width: next.width,
            lockAspectRatio: next.lockAspectRatio,
        })
    },
    { immediate: true, deep: true },
)

watch(
    form,
    (val) => {
        emit('update:modelValue', { ...val })
    },
    { deep: true },
)

function fileToBase64(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = () => resolve(String(reader.result))
        reader.onerror = () => reject(new Error('Failed to read image'))
        reader.readAsDataURL(file)
    })
}

async function onFileChange(file: File | File[] | null) {
    const selected = Array.isArray(file) ? file[0] : file
    if (!selected) return

    uploading.value = true
    error.value = null

    try {
        const src = props.upload
            ? await props.upload(selected)
            : await fileToBase64(selected)

        form.src = src
        form.alt = form.alt || selected.name
        form.caption = form.caption || selected.name
    } catch (e: any) {
        error.value = e?.message || 'Upload failed'
    } finally {
        uploading.value = false
    }
}
</script>

<template>
    <div class="d-flex flex-column ga-3 pa-2">
        <v-file-input
            label="Choose image"
            accept="image/*"
            prepend-icon="mdi-image"
            :loading="uploading"
            @update:model-value="onFileChange"
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

        <v-alert
            v-if="error"
            type="error"
            variant="tonal"
            density="comfortable"
        >
            {{ error }}
        </v-alert>
    </div>
</template>
