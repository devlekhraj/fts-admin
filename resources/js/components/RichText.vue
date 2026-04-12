<script setup lang="ts">
import { computed, onMounted, ref, shallowRef, watch } from 'vue'
import { http } from '@/api/http'

const props = withDefaults(defineProps<{
    modelValue: string
    minHeight?: string | number
    usage?: {
        usage_type: string
        usage_id: string | number
        directory?: string
        meta?: Record<string, unknown>
    }
}>(), {
    minHeight: '400px'
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue)
const editorReady = ref(false)
const editorError = ref<string | null>(null)
const editorComponent = shallowRef<any>(null)
const extensions = ref<any[]>([])
const isCodeView = ref(false)

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
            BulletList,
            OrderedList,
            VuetifyTiptap,
            HorizontalRule,
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
            HorizontalRule,
            Image.configure({
                inline: false,
                allowBase64: false,
                upload(file: File) {
                    const formData = new FormData()
                    formData.append('file', file)
                    formData.append('source', 'upload')
                    const usageType = props.usage?.usage_type
                    const usageId = props.usage?.usage_id

                    if (!usageType || usageId === undefined || usageId === null) {
                        return Promise.reject('Missing usage_type or usage_id')
                    }
                    formData.append('usage_type', String(usageType))
                    formData.append('usage_id', String(usageId))
                    if (props.usage?.directory !== undefined && props.usage?.directory !== null) {
                        formData.append('directory', String(props.usage.directory))
                    }
                    if (props.usage?.meta && typeof props.usage.meta === 'object') {
                        formData.append('meta', JSON.stringify(props.usage.meta))
                    }

                    // return http.post('/admin/image-assign', formData).then((response: any) => {
                    return http.post('/admin/file-assign', formData).then((response: any) => {
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
    <div class="rich-text-wrapper border rounded-lg overflow-hidden position-relative">
        <!-- Mode Toggle Button -->
        <!-- <div class="mode-toggle-actions pa-1 d-flex justify-end border-b bg-grey-lighten-4">
            <v-btn size="small" variant="text" :color="isCodeView ? 'primary' : 'grey-darken-2'"
                prepend-icon="mdi-code-tags" block class="justify-start px-3" @click="isCodeView = !isCodeView">
                {{ isCodeView ? 'Visual Editor' : 'HTML Source' }}
            </v-btn>
        </div> -->

        <!-- Visual Editor -->
        <component :is="editorComponent" v-if="editorReady && editorComponent" v-model="content"
            :min-height="numericMinHeight" class="p-4" :style="{ minHeight: cssMinHeight }"
            :placeholder="'Type here...'" :toolbar="true" :toolbar-position="'top'" :extensions="extensions" />

        <!-- Code Editor -->
        <!-- <v-textarea v-else-if="editorReady && isCodeView" v-model="content" variant="plain" hide-details
            class="html-code-editor" bg-color="grey-darken-4" base-color="grey-lighten-3" color="primary"
            persistent-hint no-resize auto-grow rows="5" /> -->

        <v-alert v-else-if="editorError" type="error" variant="tonal" density="comfortable">
            {{ editorError }}
        </v-alert>
        <v-skeleton-loader v-else type="article, actions" />
    </div>
</template>

<style scoped>
.rich-text-wrapper {
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.mode-toggle-actions {
    height: 36px;
    z-index: 5;
}

.html-code-editor :deep(textarea) {
    font-family: 'Fira Code', 'Roboto Mono', monospace !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
    padding: 16px !important;
}
</style>
