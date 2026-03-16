<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div class="pt-4">
					<div class="text-h6 mb-1">SMTP Settings</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Set outgoing mail server and sender configuration.
					</div>
				</div>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail Host</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field v-model="form.mail_host" density="comfortable" variant="outlined" hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail Port</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field v-model="form.mail_port" density="comfortable" variant="outlined" hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail Mailer</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field v-model="form.mail_mailer" density="comfortable" variant="outlined" hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail Username</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field v-model="form.mail_username" density="comfortable" variant="outlined" hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail Password</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field
							v-model="form.mail_password"
							density="comfortable"
							variant="outlined"
							type="password"
							hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail From Name</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field v-model="form.mail_from_name" density="comfortable" variant="outlined" hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail Encryption</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field v-model="form.mail_encryption" density="comfortable" variant="outlined" hide-details />
					</v-col>
				</v-row>
				<v-row class="align-center">
					<v-col cols="12" md="4"><label class="text-body-2">Mail From Address</label></v-col>
					<v-col cols="12" md="8">
						<v-text-field
							v-model="form.mail_from_address"
							density="comfortable"
							variant="outlined"
							hide-details />
					</v-col>
				</v-row>

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
	mail_host: '',
	mail_port: '',
	mail_mailer: '',
	mail_username: '',
	mail_password: '',
	mail_from_name: '',
	mail_encryption: '',
	mail_from_address: '',
});

function initForm() {
	if (props.data) {
		form.mail_host = props.data.mail_host || '';
		form.mail_port = props.data.mail_port || '';
		form.mail_mailer = props.data.mail_mailer || '';
		form.mail_username = props.data.mail_username || '';
		form.mail_password = props.data.mail_password || '';
		form.mail_from_name = props.data.mail_from_name || '';
		form.mail_encryption = props.data.mail_encryption || '';
		form.mail_from_address = props.data.mail_from_address || '';
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
		await updateSettings('smtp', { settings: { ...form } });
		snackbar.show({ message: 'SMTP settings updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
