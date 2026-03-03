<script setup lang="ts">
import { computed, onMounted, ref, shallowRef, watch } from 'vue'
import { http } from '@/api/http'

const props = withDefaults(defineProps<{
    modelValue: string
    minHeight?: string | number
}>(), {
    minHeight: '400px'
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue)
const editorReady = ref(false)
const editorError = ref<string | null>(null)
const editorComponent = shallowRef<any>(null)
const extensions = ref<any[]>([])

watch(content, (val) => emit('update:modelValue', val))
watch(() => props.modelValue, (val) => {
    if (val !== content.value) {
        content.value = val
    }
})

const cssMinHeight = computed(() => {
    if (typeof props.minHeight === 'number') return `${props.minHeight}px`
    return props.minHeight || '400px'
})

const numericMinHeight = computed(() => {
    const raw = cssMinHeight.value
    const parsed = Number.parseInt(raw, 10)
    return Number.isFinite(parsed) ? parsed : 400
})

onMounted(async () => {
    try {
        const mod = await import('vuetify-pro-tiptap')
        await import('vuetify-pro-tiptap/style.css')

        const {
            BaseKit,
            Bold,
            Color,
            TextAlign,
            Heading,
            Highlight,
            History,
            Image,
            Italic,
            Link,
            Strike,
            Table,
            Underline,
            Video,
            BulletList,
            OrderedList,
            VuetifyTiptap,
            Code,
            HorizontalRule,
            Blockquote,
        } = mod

        extensions.value = [
            BaseKit.configure({
                placeholder: {
                    placeholder: 'Type here...'
                }
            }),
            Bold,
            BulletList,
            OrderedList,
            Italic,
            Underline,
            Strike,
            Color,
            Highlight,
            Heading,
            Link,
            Code,
            Blockquote,
            HorizontalRule,
            Image.configure({
                inline: false,
                allowBase64: false,
                upload(file: File) {
                    const formData = new FormData()
                    formData.append('image', file)
                    return http.post('/admin/gallery-upload', formData).then((response: any) => {
                        const payload = response?.data ?? response
                        if (payload?.url) return payload.url
                        return Promise.reject('Upload failed')
                    })
                }
            }),
            Table,
            History,
            TextAlign,
        ]

        editorComponent.value = VuetifyTiptap
        editorReady.value = true
    } catch {
        editorError.value = 'Failed to load editor'
    }
})
</script>

<template>
    <component
        :is="editorComponent"
        v-if="editorReady && editorComponent"
        v-model="content"
        :min-height="numericMinHeight"
        class="p-4"
        :style="{ minHeight: cssMinHeight }"
        :placeholder="'Type here...'"
        :toolbar="true"
        :toolbar-position="'top'"
        :extensions="extensions"
    />
    <v-alert v-else-if="editorError" type="error" variant="tonal" density="comfortable">
        {{ editorError }}
    </v-alert>
    <v-skeleton-loader v-else type="article, actions" />
</template>
