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
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

const form = reactive({
	mail_host: 'smtp.gmail.com',
	mail_port: '587',
	mail_mailer: 'smtp',
	mail_username: 'fatafatnp@gmail.com',
	mail_password: 'ujej mach gsse lwxi',
	mail_from_name: 'Fatafat Sewa',
	mail_encryption: 'tls',
	mail_from_address: 'fatafatnp@gmail.com',
});
</script>
