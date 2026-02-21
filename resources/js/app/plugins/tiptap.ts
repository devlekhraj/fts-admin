// src/plugins/tiptap.ts
import { createVuetifyProTipTap, VuetifyViewer } from 'vuetify-pro-tiptap'
import {
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
    CodeBlock,
    HorizontalRule,
    Blockquote
} from 'vuetify-pro-tiptap'

import 'vuetify-pro-tiptap/style.css'

// Optional: if you want a custom image picker tab, create this component.
// If you don’t need it, remove imageTabs below.
// import SelectImage from '@/components/SelectImage.vue'

export const vuetifyProTipTap = createVuetifyProTipTap({
  lang: 'en',
  fallbackLang: 'en',
  components: {
    VuetifyTiptap,
    VuetifyViewer,
  },
  extensions: [
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
    CodeBlock,
    Blockquote,
    HorizontalRule,
    Image.configure({
        inline: false,
        allowBase64: true,
        upload(file: File) {
            return new Promise((resolve) => {
                const reader = new FileReader()
                reader.onload = () => resolve(reader.result as string)
                reader.readAsDataURL(file)
            })
        }
    }),
    Video,
    Table,
    History,
    TextAlign,
  ],
})
