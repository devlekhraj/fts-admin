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
				<div class="d-flex justify-space-around py-6">
					<v-btn color="primary" variant="flat" :loading="saving.google" @click="onSaveGoogle">
						<v-icon start>mdi-google</v-icon>
						Save
					</v-btn>
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
				<div class="d-flex justify-space-around py-6">
					<v-btn color="primary" variant="flat" :loading="saving.facebook" @click="onSaveFacebook">
						<v-icon start>mdi-facebook</v-icon>
						Save
					</v-btn>
				</div>
			</v-col>
		</v-row>

		<v-divider class="my-4" />

		<div class="text-subtitle-1 font-weight-medium mb-3">SMS</div>
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div>
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
					<div class="d-flex justify-space-around py-6">
						<v-btn color="primary" variant="flat" :loading="saving.sms" @click="onSaveSms">
							<v-icon start>mdi-message-text-outline</v-icon>
							Save
						</v-btn>
					</div>
				</div>
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

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
const saving = reactive({
	google: false,
	facebook: false,
	sms: false,
});

async function copyValue(value: string) {
	if (!value) return;
	try {
		await navigator.clipboard.writeText(value);
	} catch {
		// no-op
	}
}

async function onSaveGoogle() {
	saving.google = true;
	try {
		console.log('save google_oauth', form.google_oauth);
	} finally {
		saving.google = false;
	}
}

async function onSaveFacebook() {
	saving.facebook = true;
	try {
		console.log('save facebook_oauth', form.facebook_oauth);
	} finally {
		saving.facebook = false;
	}
}

async function onSaveSms() {
	saving.sms = true;
	try {
		console.log('save sms', form.sms);
	} finally {
		saving.sms = false;
	}
}
</script>
