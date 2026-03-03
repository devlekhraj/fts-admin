<template>
	<v-card class="pa-4" variant="flat">
	
		<v-row>
			<v-col cols="12" lg="4" offset-lg="4">
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail Host</div>
					<v-text-field v-model="form.smtp.mail_host" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail Port</div>
					<v-text-field v-model="form.smtp.mail_port" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail Mailer</div>
					<v-text-field v-model="form.smtp.mail_mailer" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail Username</div>
					<v-text-field v-model="form.smtp.mail_username" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail Password</div>
					<v-text-field v-model="form.smtp.mail_password" density="comfortable" variant="outlined" type="password" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail From Name</div>
					<v-text-field v-model="form.smtp.mail_from_name" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail Encryption</div>
					<v-text-field v-model="form.smtp.mail_encryption" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Mail From Address</div>
					<v-text-field v-model="form.smtp.mail_from_address" density="comfortable" variant="outlined" />
				</div>
			</v-col>
		</v-row>

		<v-divider class="my-4" />

		<div class="text-subtitle-1 font-weight-medium mb-3">Google OAuth</div>
		<v-row>
			<v-col cols="12" md="4">
				<div class="text-body-2 text-medium-emphasis mb-1">Client ID</div>
				<v-text-field v-model="form.google_oauth.client_id" density="comfortable" variant="outlined" />
			</v-col>
			<v-col cols="12" md="4">
				<div class="text-body-2 text-medium-emphasis mb-1">Client Secret</div>
				<v-text-field v-model="form.google_oauth.client_secret" density="comfortable" variant="outlined" type="password" />
			</v-col>
		</v-row>

		<v-divider class="my-4" />

		<div class="text-subtitle-1 font-weight-medium mb-3">Facebook OAuth</div>
		<v-row>
			<v-col cols="12" md="4">
				<div class="text-body-2 text-medium-emphasis mb-1">App ID</div>
				<v-text-field v-model="form.facebook_oauth.app_id" density="comfortable" variant="outlined" />
			</v-col>
			<v-col cols="12" md="4">
				<div class="text-body-2 text-medium-emphasis mb-1">App Secret</div>
				<v-text-field v-model="form.facebook_oauth.app_secret" density="comfortable" variant="outlined" type="password" />
			</v-col>
		</v-row>

		<v-divider class="my-4" />

		<div class="text-subtitle-1 font-weight-medium mb-3">SMS</div>
		<v-row>
			<v-col cols="12" md="4">
				<div class="text-body-2 text-medium-emphasis mb-1">Token</div>
				<v-text-field v-model="form.sms.token" density="comfortable" variant="outlined" type="password" />
			</v-col>
			<v-col cols="12" md="4">
				<div class="text-body-2 text-medium-emphasis mb-1">API Endpoint</div>
				<v-text-field v-model="form.sms.api_endpoint" density="comfortable" variant="outlined" />
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

const form = reactive({
	smtp: {
		mail_host: '',
		mail_port: '',
		mail_mailer: '',
		mail_username: '',
		mail_password: '',
		mail_from_name: '',
		mail_encryption: '',
		mail_from_address: '',
	},
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
</script>
