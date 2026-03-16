<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="10" offset-lg="1">>
				<div class="pt-4">
					<div class="text-h6 mb-1">SEO Settings</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Configure default SEO metadata for the website.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Meta Title</div>
					<v-textarea v-model="form.meta_title" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Meta Description</div>
					<v-textarea v-model="form.meta_description" density="comfortable" variant="outlined" auto-grow
						rows="4" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Meta Keywords</div>
					<v-textarea v-model="form.meta_keywords" density="comfortable" variant="outlined" auto-grow
						rows="3" />
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
	meta_title: '',
	meta_description: '',
	meta_keywords: '',
});

function initForm() {
	if (props.data) {
		form.meta_title = props.data.meta_title || '';
		form.meta_description = props.data.meta_description || '';
		form.meta_keywords = props.data.meta_keywords || '';
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
		await updateSettings('seo', { settings: { ...form } });
		snackbar.show({ message: 'SEO settings updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
