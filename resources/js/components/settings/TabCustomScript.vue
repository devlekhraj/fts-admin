<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="10" offset-lg="1">
				<div class="pt-4">
					<div class="text-h6 mb-1">Custom Scripts</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Manage custom script blocks and third-party integrations for header and footer.
					</div>
				</div>

				<div class="mb-6">
					<div class="text-body-2 text-medium-emphasis mb-1">Header Script (Before &lt;/head&gt;)</div>
					<v-textarea v-model="form.header_script" density="comfortable" variant="outlined" auto-grow rows="4"
						class="font-monospace" hint="Injected before the closing head tag" persistent-hint />
				</div>

				<div class="mb-6">
					<div class="text-body-2 text-medium-emphasis mb-1">Footer Script (Before &lt;/body&gt;)</div>
					<v-textarea v-model="form.footer_script" density="comfortable" variant="outlined" auto-grow rows="4"
						class="font-monospace" hint="Injected before the closing body tag" persistent-hint />
				</div>

				<v-divider class="my-6"></v-divider>

				<div class="d-flex justify-end">
					<v-btn color="primary" variant="flat" :loading="loading" prepend-icon="mdi-content-save-outline"
						@click="onUpdate">
						Update Settings
					</v-btn>
				</div>
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { updateSettings } from '@/api/settings.api';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

const props = defineProps<{ data?: any }>();
const snackbar = useSnackbarStore();
const loading = ref(false);

const form = reactive({
	header_script: '',
	footer_script: '',
});

function initForm() {
	if (props.data) {
		form.header_script = props.data.header_script || '';
		form.footer_script = props.data.footer_script || '';
	}
}

watch(
	() => props.data,
	() => initForm(),
	{ immediate: true }
);

async function onUpdate() {
	loading.value = true;
	try {
		await updateSettings('custom_scripts', { settings: { ...form } });
		snackbar.show({ message: 'Custom scripts updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>

<style scoped>
.font-monospace :deep(textarea) {
	font-family: 'Fira Code', 'Roboto Mono', monospace !important;
	font-size: 0.875rem;
}
</style>
