<template>
	<v-container class="fill-height" fluid>
		<v-row align="center" justify="center">
			<v-col cols="12" sm="8" md="5" lg="4">
				<v-card class="pa-6">
          <div class="d-flex justify-center mb-4">
            <div class="text-center">
              <v-icon size="64" color="primary">mdi-shield-account</v-icon>
              <div class="text-subtitle-1 font-weight-semibold mt-2">Fatafat Admin</div>
              <div class="text-caption text-medium-emphasis">Sign in to continue</div>
            </div>
          </div>
					<v-card-text>
						<v-alert v-if="error" type="error" variant="tonal" class="mb-4">
							{{ error }}
						</v-alert>
						<v-form ref="form" @submit.prevent="onSubmit">
							<v-text-field v-model="email" label="Email" type="email" :rules="[requiredRule]"
								variant="outlined" prepend-inner-icon="mdi-email-outline" autocomplete="username"
								class="mb-3" />
							<v-text-field v-model="password" label="Password" type="password" :rules="[requiredRule]"
								variant="outlined" prepend-inner-icon="mdi-lock-outline"
								autocomplete="current-password" />
							<v-btn type="submit" size="x-large" color="primary" class="mt-4" block :loading="loading">
								Sign in
							</v-btn>
						</v-form>
					</v-card-text>
				</v-card>
			</v-col>
		</v-row>
	</v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth.store';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');
const form = ref();

const requiredRule = (value: string) => Boolean(value) || 'Required';

async function onSubmit() {
	error.value = '';
	const { valid } = await form.value?.validate();
	if (!valid) return;

	loading.value = true;
	try {
		await authStore.login(email.value, password.value);
		await authStore.fetchMe();
		await router.push('/dashboard');
	} catch (err: any) {
		error.value = err?.response?.data?.message ?? 'Login failed';
	} finally {
		loading.value = false;
	}
}
</script>
