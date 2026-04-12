<script setup lang="ts">
import { computed, defineComponent, markRaw, onMounted, ref, shallowRef, watch } from 'vue'
import CustomImageTab from './CustomImageTab.vue'
import CustomURLTab from './CustomURLTab.vue'

const props = withDefaults(defineProps<{
    modelValue: string
    minHeight?: string | number
    usage?: {
        usage_type: string
        usage_id: string | number
        directory?: string
        meta?: Record<string, unknown>
    }
    onImageEdit?: (attrs: Record<string, any>) => boolean | void
}>(), {
    minHeight: '400px'
})

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()

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

function fileToBase64(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()

        reader.onload = () => resolve(String(reader.result))
        reader.onerror = () => reject(new Error('Failed to read image'))
        reader.readAsDataURL(file)
    })
}

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

        const CustomImage = Image.extend({
            addAttributes() {
                return {
                    ...(this.parent?.() ?? {}),
                    alt: {
                        default: null,
                    },
                    caption: {
                        default: null,
                    },
                }
            },

            parseHTML() {
                return [
                    {
                        tag: 'figure.editor-image',
                        getAttrs: (element: HTMLElement) => {
                            const img = element.querySelector('img') as HTMLElement | null
                            if (!img) return false
                            const captionEl = element.querySelector('figcaption') as HTMLElement | null
                            const caption = captionEl?.textContent ?? null
                            const attrs: Record<string, any> = {}
                            Array.from(img.attributes).forEach((attr) => {
                                if (attr.name === 'class') return
                                attrs[attr.name] = attr.value
                            })
                            attrs.caption = caption && caption.trim() ? caption : null
                            return attrs
                        },
                    },
                    {
                        tag: 'img[src]',
                        getAttrs: (element: HTMLElement) => {
                            const attrs: Record<string, any> = {}
                            Array.from(element.attributes).forEach((attr) => {
                                if (attr.name === 'class') return
                                attrs[attr.name] = attr.value
                            })
                            return attrs
                        },
                    },
                ]
            },

            addNodeView() {
                return ({ node, editor, getPos }) => {
                    const figure = document.createElement('figure')
                    figure.classList.add('editor-image')

                    const img = document.createElement('img')
                    const { caption, ...attrs } = node.attrs ?? {}
                    Object.entries(attrs || {}).forEach(([key, value]) => {
                        if (value === null || value === undefined || key === 'class') return
                        img.setAttribute(key, String(value))
                    })

                    figure.appendChild(img)

                    const figcaption = document.createElement('figcaption')
                    figcaption.classList.add('editor-image-caption')
                    figcaption.contentEditable = 'true'
                    figcaption.innerText = caption ? String(caption) : ''
                    const updateCaption = (value: string) => {
                        const pos = getPos?.()
                        if (typeof pos !== 'number') return
                        editor.commands.command(({ tr }) => {
                            tr.setNodeMarkup(pos, undefined, { ...node.attrs, caption: value })
                            return true
                        })
                    }
                    figcaption.addEventListener('input', (event: Event) => {
                        const value = (event.target as HTMLElement)?.innerText ?? ''
                        updateCaption(value)
                    })
                    figure.appendChild(figcaption)

                    return { dom: figure }
                }
            },

            renderHTML({ HTMLAttributes }: { HTMLAttributes: Record<string, any> }) {
                const { caption, ...attrs } = HTMLAttributes

                if (!caption || !String(caption).trim()) {
                    return ['img', attrs]
                }

                return [
                    'figure',
                    { class: 'editor-image' },
                    ['img', attrs],
                    ['figcaption', { class: 'editor-image-caption' }, caption],
                ]
            },
        })

        extensions.value = [
            BaseKit.configure({
                placeholder: {
                    placeholder: 'Type here...'
                },
                bubble: {
                    list: {
                        text: [
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'divider',
                            'color',
                            'highlight',
                            'divider',
                            'link',
                        ],
                        image: [
                            'float-left',
                            'float-none',
                            'float-right',
                            'divider',
                            'image-size-large',
                            'image-size-medium',
                            'image-size-small',
                            'image',
                            'remove',
                        ],
                    },
                },
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
            CustomImage.configure({
                inline: false,
                allowBase64: true,
                imageTabs: [
                    {
                        name: 'UPLOAD',
                        component: markRaw(CustomImageTab),
                    },
                    {
                        name: 'URL',
                        component: markRaw(CustomURLTab),
                    },
                ],
                hiddenTabs: ['upload', 'url'],
                async upload(file: File) {
                    return await fileToBase64(file)
                }
            }),
            Table,
            History,
            TextAlign,
        ]

        editorComponent.value = VuetifyTiptap
        editorReady.value = true
    } catch (error: any) {
        editorError.value = error?.message || 'Failed to load editor'
    }
})
</script>

<template>
    <div class="rich-text-wrapper border rounded-lg overflow-hidden position-relative">

        <component :is="editorComponent" v-if="editorReady && editorComponent" v-model="content"
            :min-height="numericMinHeight" class="p-4" :style="{ minHeight: cssMinHeight }"
            :placeholder="'Type here...'" :toolbar="true" :toolbar-position="'top'" :extensions="extensions" />



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

:deep(.editor-image) {
    display: inline-block;
    margin: 16px 0;
    max-width: 100%;
}

:deep(.editor-image img) {
    max-width: 100%;
    height: auto;
    display: block;
    border-radius: 8px;
}

:deep(.editor-image-caption) {
    text-align: center;
    font-size: 12px;
    line-height: 1.4;
    color: rgba(0, 0, 0, 0.6);
    margin-top: 6px;
}
</style>
