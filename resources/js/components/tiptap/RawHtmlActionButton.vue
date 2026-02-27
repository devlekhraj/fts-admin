<template>
	<ActionButton :editor="editor" :tooltip="tooltipText" :disabled="disabled">
		<VIcon size="large">mdi-code-tags</VIcon>
		<VDialog v-model="dialog" max-width="900" activator="parent">
			<VCard>
				<VCardTitle class="text-h6">Edit HTML</VCardTitle>
				<VCardText>
					<VTextarea
						v-model="html"
						label="HTML"
						variant="outlined"
						density="comfortable"
						rows="12"
						auto-grow
					/>
				</VCardText>
				<VCardActions class="justify-end">
					<VBtn variant="text" @click="dialog = false">Cancel</VBtn>
					<VBtn color="primary" @click="applyHtml">Apply</VBtn>
				</VCardActions>
			</VCard>
		</VDialog>
	</ActionButton>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import type { Editor } from '@tiptap/vue-3';
import { ActionButton } from 'vuetify-pro-tiptap';

const props = defineProps<{
	editor: Editor;
	disabled?: boolean;
	tooltip?: string;
}>();

const dialog = ref(false);
const html = ref('');
const tooltipText = computed(() => props.tooltip ?? 'Edit HTML');

watch(dialog, (isOpen) => {
	if (isOpen) {
		const stored = props.editor?.storage?.rawHtml?.rawHtml;
		html.value = stored || props.editor?.getHTML?.() || '';
	}
});

function applyHtml() {
	if (props.editor?.storage?.rawHtml) {
		props.editor.storage.rawHtml.rawHtml = html.value ?? '';
	}
	props.editor?.commands?.setContent?.(html.value ?? '');
	dialog.value = false;
}
</script>
