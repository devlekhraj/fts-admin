<template>
	<v-form ref="formRef" @submit.prevent="onSubmit">
		<v-alert v-if="error" type="error" variant="tonal" class="mb-4">
			{{ error }}
		</v-alert>
		<div class="px-6 pt-3">
			<v-container>
				<v-row>
					<v-col cols="12" class="pb-0">
						<v-text-field v-model="form.password" label="New Password"
							:type="showPassword ? 'text' : 'password'" autocomplete="off" variant="outlined"
							density="comfortable" :rules="[rules.required]"
							:error-messages="getErrorMessages('password')" prepend-inner-icon="mdi-lock-outline"
							:append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
							@click:append-inner="togglePasswordVisibility"
							@update:model-value="clearFieldError('password')" />
					</v-col>
					<v-col cols="12" class="pb-0">
						<v-text-field v-model="form.confirm_password" label="Confirm Password"
							:type="showConfirmPassword ? 'text' : 'password'" autocomplete="off" variant="outlined"
							density="comfortable" :rules="[rules.required, rules.passwordMatch]"
							:error-messages="getErrorMessages('confirm_password')" prepend-inner-icon="mdi-lock-check-outline"
							:append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
							@click:append-inner="toggleConfirmPasswordVisibility"
							@update:model-value="clearFieldError('confirm_password')" />
					</v-col>
					<v-col cols="12" class="d-flex justify-space-around pt-8">
						<v-btn color="primary" size="large" variant="tonal" :loading="loading" :disabled="loading" @click="onSubmit">
							<v-icon start>mdi-content-save-outline</v-icon>
							Update
						</v-btn>
					</v-col>
				</v-row>
			</v-container>
		</div>
	</v-form>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { updatePassword } from '@/api/admins.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
	role?: string | null;
};

type PasswordForm = {
	password: string;
	confirm_password: string;
};

const props = defineProps<{ admin: Admin }>();
const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

const formRef = ref();
const error = ref('');
const loading = ref(false);
const snackbar = useSnackbarStore();
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const form = ref<PasswordForm>({
	password: '',
	confirm_password: '',
});
const fieldErrors = ref<Record<string, string[]>>({});

const rules = {
	required: (value: unknown) => (value ? true : 'Required'),
	passwordMatch: () =>
		form.value.password === form.value.confirm_password ? true : 'Passwords do not match',
};

function getErrorMessages(field: string) {
	return fieldErrors.value[field] ?? [];
}

function clearFieldError(field: string) {
	if (!fieldErrors.value[field]) return;
	const next = { ...fieldErrors.value };
	delete next[field];
	fieldErrors.value = next;
}

function togglePasswordVisibility() {
	showPassword.value = !showPassword.value;
}

function toggleConfirmPasswordVisibility() {
	showConfirmPassword.value = !showConfirmPassword.value;
}

async function onSubmit() {
	error.value = '';
	fieldErrors.value = {};
	const result = await formRef.value?.validate?.();
	if (result && result.valid === false) return;
	if (!result && formRef.value?.validate) {
		const ok = await formRef.value.validate();
		if (!ok) return;
	}

	if (!props.admin?.id) {
		error.value = 'Admin id is required.';
		snackbar.show({ message: error.value, color: 'error' });
		return;
	}

	loading.value = true;
	try {
		const payload = {
			id: props.admin.id,
			password: form.value.password,
			confirm_password: form.value.confirm_password,
		};
		const { data } = await updatePassword(payload);
		const updated = data?.data?.updated ?? data?.updated ?? true;
		const message = data?.data?.message ?? data?.message ?? '';
		const field = data?.data?.field ?? data?.field ?? '';
		if (updated === false) {
			if (field) {
				fieldErrors.value = { [field]: [message || 'Invalid value.'] };
			} else {
				error.value = message || 'Failed to update password.';
				snackbar.show({ message: error.value, color: 'error' });
			}
			return;
		}
		snackbar.show({ message: message || 'Password updated successfully.', color: 'success' });
		emit('saved', data ?? payload);
	} catch (err) {
		const response = (err as any)?.response;
		const responseErrors = response?.data?.errors ?? null;
		if (response?.status === 422 && responseErrors && typeof responseErrors === 'object') {
			const next: Record<string, string[]> = {};
			for (const [key, messages] of Object.entries(responseErrors)) {
				if (Array.isArray(messages)) {
					next[key] = messages.map((item) => String(item));
				} else if (messages != null) {
					next[key] = [String(messages)];
				}
			}
			fieldErrors.value = next;
			return;
		}
		const message = getErrorMessage(err);
		console.error('Failed to update password:', message);
		error.value = message;
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
