<template>
	<v-container>
		<v-row>
			<v-col cols="12" lg="8" offset-lg="2">
				<div>
					<v-card class="pa-4" variant="flat">

						<div class="pt-4">
							<div class="text-h6 mb-1">Core Settings</div>
							<div class="text-body-2 text-medium-emphasis mb-4">
								Manage brand identity and core description content.
							</div>
						</div>
						<div>
							<div class="text-body-2 text-medium-emphasis mb-1">Brand Name</div>
							<v-text-field v-model="form.brand_name" density="comfortable" variant="outlined" />
						</div>
						<div>
							<div class="text-body-2 text-medium-emphasis mb-1">Brand Acronym</div>
							<v-text-field v-model="form.brand_acronym" density="comfortable" variant="outlined" />
						</div>
						<div>
							<div class="text-body-2 text-medium-emphasis mb-1">Description</div>
							<RichText v-model="form.description" />
						</div>

						<v-divider class="my-6"></v-divider>

						<div class="d-flex justify-end">
							<v-btn
								color="primary"
								variant="flat"
								:loading="loading"
								prepend-icon="mdi-content-save-outline"
								@click="onUpdate">
								Update Settings
							</v-btn>
						</div>

					</v-card>
				</div>

			</v-col>
		</v-row>
	</v-container>
</template>

<script setup lang="ts">
import { defineAsyncComponent, onMounted, reactive, ref, watch } from 'vue';
import { updateSettings } from '@/api/settings.api';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

const props = defineProps<{ data?: any }>();
const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));
const snackbar = useSnackbarStore();
const loading = ref(false);

const form = reactive({
	brand_name: '',
	brand_acronym: '',
	description: '',
});

function initForm() {
	if (props.data) {
		form.brand_name = props.data.brand_name || '';
		form.brand_acronym = props.data.brand_acronym || '';
		form.description = props.data.description || '';
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
		await updateSettings('core', { settings: { ...form } });
		snackbar.show({ message: 'Core settings updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
