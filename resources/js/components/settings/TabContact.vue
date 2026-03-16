<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div class="pt-4">
					<div class="text-h6 mb-1">Contact Settings</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Configure business contact and communication details.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Address</div>
					<v-text-field v-model="form.address" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Email</div>
					<v-text-field v-model="form.email" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Phone Number</div>
					<v-text-field v-model="form.phone" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mobile Number</div>
					<v-text-field v-model="form.mobile" density="comfortable" variant="outlined" />
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
	address: '',
	email: '',
	phone: '',
	mobile: '',
});

function initForm() {
	if (props.data) {
		form.address = props.data.address || '';
		form.email = props.data.email || '';
		form.phone = props.data.phone || '';
		form.mobile = props.data.mobile || '';
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
		await updateSettings('contact', { settings: { ...form } });
		snackbar.show({ message: 'Contact settings updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
