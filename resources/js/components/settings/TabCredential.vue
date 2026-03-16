<template>
	<v-card class="pa-4" variant="flat">
		<div class="pt-4">
			<div class="text-h6 mb-1">Social Credentials</div>
			<div class="text-body-2 text-medium-emphasis mb-4">
				Configure OAuth and SMS provider secrets.
			</div>
		</div>

		<div class="text-subtitle-1 font-weight-medium mb-3">Google OAuth</div>
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Client ID</div>
					<v-text-field v-model="form.google_oauth.client_id" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Client Secret</div>
					<div class="d-flex align-center ga-2">
						<v-text-field
							v-model="form.google_oauth.client_secret"
							density="comfortable"
							variant="outlined"
							hide-details
							:type="visible.googleClientSecret ? 'text' : 'password'" />
						<v-btn
							icon
							size="small"
							variant="tonal"
							color="primary"
							@click="visible.googleClientSecret = !visible.googleClientSecret">
							<v-icon>{{ visible.googleClientSecret ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
						</v-btn>
						<v-btn icon size="small" variant="tonal" color="primary" @click="copyValue(form.google_oauth.client_secret)">
							<v-icon>mdi-content-copy</v-icon>
						</v-btn>
					</div>
				</div>
			</v-col>
		</v-row>

		<v-divider class="my-4" />

		<div class="text-subtitle-1 font-weight-medium mb-3">Facebook OAuth</div>
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">App ID</div>
					<v-text-field v-model="form.facebook_oauth.app_id" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">App Secret</div>
					<div class="d-flex align-center ga-2">
						<v-text-field
							v-model="form.facebook_oauth.app_secret"
							density="comfortable"
							variant="outlined"
							hide-details
							:type="visible.facebookAppSecret ? 'text' : 'password'" />
						<v-btn
							icon
							size="small"
							variant="tonal"
							color="primary"
							@click="visible.facebookAppSecret = !visible.facebookAppSecret">
							<v-icon>{{ visible.facebookAppSecret ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
						</v-btn>
						<v-btn icon size="small" variant="tonal" color="primary" @click="copyValue(form.facebook_oauth.app_secret)">
							<v-icon>mdi-content-copy</v-icon>
						</v-btn>
					</div>
				</div>
			</v-col>
		</v-row>

		<v-divider class="my-4" />

		<div class="text-subtitle-1 font-weight-medium mb-3">SMS Provider</div>
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">API Endpoint</div>
					<v-text-field v-model="form.sms.api_endpoint" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Token</div>
					<div class="d-flex align-center ga-2">
						<v-text-field
							v-model="form.sms.token"
							density="comfortable"
							variant="outlined"
							hide-details
							:type="visible.smsToken ? 'text' : 'password'" />
						<v-btn
							icon
							size="small"
							variant="tonal"
							color="primary"
							@click="visible.smsToken = !visible.smsToken">
							<v-icon>{{ visible.smsToken ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
						</v-btn>
						<v-btn icon size="small" variant="tonal" color="primary" @click="copyValue(form.sms.token)">
							<v-icon>mdi-content-copy</v-icon>
						</v-btn>
					</div>
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
	google_oauth: {
		client_id: '',
		client_secret: '',
	},
	facebook_oauth: {
		app_id: '',
		app_secret: '',
	},
	sms: {
		token: '',
		api_endpoint: '',
	},
});

const visible = reactive({
	googleClientSecret: false,
	facebookAppSecret: false,
	smsToken: false,
});

function initForm() {
	if (props.data) {
		if (props.data.google_oauth) {
			form.google_oauth.client_id = props.data.google_oauth.client_id || '';
			form.google_oauth.client_secret = props.data.google_oauth.client_secret || '';
		}
		if (props.data.facebook_oauth) {
			form.facebook_oauth.app_id = props.data.facebook_oauth.app_id || '';
			form.facebook_oauth.app_secret = props.data.facebook_oauth.app_secret || '';
		}
		if (props.data.sms) {
			form.sms.token = props.data.sms.token || '';
			form.sms.api_endpoint = props.data.sms.api_endpoint || '';
		}
	}
}

watch(
	() => props.data,
	() => initForm(),
	{ immediate: true }
);

async function copyValue(value: string) {
	if (!value) return;
	try {
		await navigator.clipboard.writeText(value);
		snackbar.show({ message: 'Copied to clipboard', color: 'success' });
	} catch {
		// no-op
	}
}

async function onUpdate() {
	loading.value = true;
	try {
		await updateSettings('credentials', { settings: { ...form } });
		snackbar.show({ message: 'Social credentials updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
