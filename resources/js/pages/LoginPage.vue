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
								density="comfortable" variant="outlined" prepend-inner-icon="mdi-email-outline"
								autocomplete="username" class="mb-3" />
							<v-text-field v-model="password" label="Password" :type="showPassword ? 'text' : 'password'"
								density="comfortable" :rules="[requiredRule]" variant="outlined"
								prepend-inner-icon="mdi-lock-outline"
								:append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
								@click:append-inner="showPassword = !showPassword" autocomplete="current-password" />
							<v-btn type="submit" size="large" variant="flat" color="primary" class="mt-4" block :loading="loading">
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
const showPassword = ref(false);
const loading = ref(false);
const error = ref('');
const form = ref();

const requiredRule = (value: string) => Boolean(value) || 'Required';

function resolveLoginError(caughtError: unknown): string {
	if (typeof caughtError === 'object' && caughtError !== null) {
		const message = (caughtError as {
			response?: { data?: { message?: unknown } };
		}).response?.data?.message;

		if (typeof message === 'string' && message.trim()) {
			return message;
		}
	}

	if (caughtError instanceof Error && caughtError.message) {
		return caughtError.message;
	}

	return 'Login failed';
}

async function onSubmit() {
	if (loading.value) return;

	error.value = '';
	const { valid } = await form.value?.validate();
	if (!valid) return;

	loading.value = true;
	try {
		await authStore.signIn(email.value, password.value);
		await router.push({ name: 'admin.overview' });
	} catch (caughtError: unknown) {
		error.value = resolveLoginError(caughtError);
	} finally {
		loading.value = false;
	}
}
</script>
