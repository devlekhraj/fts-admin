<template>
		<v-card-text class="py-6">
			<v-alert v-if="error" type="error" variant="tonal" class="mb-4">
				{{ error }}
			</v-alert>
			<v-form ref="formRef" @submit.prevent="onSubmit">
				<v-row>
					<v-col cols="12" md="12" class="pb-0">
						<v-text-field v-model="form.name" label="Name" variant="outlined" density="comfortable"
							:rules="[rules.required]" :error-messages="getErrorMessages('name')"
							prepend-inner-icon="mdi-account" @update:model-value="clearFieldError('name')" />
					</v-col>
					<v-col cols="12" md="12" class="pb-0">
						<v-text-field v-model="form.email" label="Email" type="email" variant="outlined"
							density="comfortable" :rules="[rules.required, rules.email]"
							:error-messages="getErrorMessages('email')" prepend-inner-icon="mdi-email-outline"
							@update:model-value="clearFieldError('email')" />
					</v-col>
					<v-col cols="12" md="6" class="pb-0">
						<v-text-field v-model="form.username" label="Username" variant="outlined" density="comfortable"
							autocomplete="off" :rules="[rules.required]" :error-messages="getErrorMessages('username')"
							prepend-inner-icon="mdi-account-circle-outline" @update:model-value="clearFieldError('username')" />
					</v-col>
					<v-col cols="12" md="6" class="pb-0">
						<v-autocomplete v-model="form.role_id" :items="roleOptions" label="Role" variant="outlined"
							density="comfortable" clearable :rules="[rules.required]" :error-messages="getErrorMessages('role_id')"
							prepend-inner-icon="mdi-shield-account-outline" @update:model-value="clearFieldError('role_id')" />
					</v-col>
					<v-col cols="12" md="6" class="pb-0">
						<v-text-field v-model="form.password" label="Password" :type="showPassword ? 'text' : 'password'"
							autocomplete="off" variant="outlined" density="comfortable" :rules="[rules.required]"
							:error-messages="getErrorMessages('password')" prepend-inner-icon="mdi-lock-outline"
							:append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
							@click:append-inner="showPassword = !showPassword" @update:model-value="clearFieldError('password')" />
					</v-col>
					<v-col cols="12" md="6" class="pb-0">
						<v-text-field v-model="form.confirm_password" label="Confirm Password"
							:type="showConfirmPassword ? 'text' : 'password'" autocomplete="off" variant="outlined"
							density="comfortable" :rules="[rules.required, rules.passwordMatch]"
							:error-messages="getErrorMessages('confirm_password')" prepend-inner-icon="mdi-lock-check-outline"
							:append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
							@click:append-inner="showConfirmPassword = !showConfirmPassword"
							@update:model-value="clearFieldError('confirm_password')" />
					</v-col>
				</v-row>

		</v-form>
	</v-card-text>
	<v-card-actions class="pb-4">
		<div class="w-100 text-center">
			<v-btn color="primary" variant="tonal" class="px-5" :loading="loading" :disabled="loading" @click="onSubmit">
				<v-icon start>mdi-content-save-outline</v-icon>
				submit
			</v-btn>
		</div>
	</v-card-actions>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { create as createAdmin } from '@/api/admins.api';
import { getErrorMessage } from '@/shared/errors';
import { list as listRoles } from '@/api/roles.api';

const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

type RoleOption = { title: string; value: string | number };

	const formRef = ref();
	const roleOptions = ref<RoleOption[]>([]);
	const error = ref('');
	const form = ref({
		name: '',
		email: '',
		username: '',
		role_id: null as null | string | number,
		password: '',
		confirm_password: '',
	});
	const fieldErrors = ref<Record<string, string[]>>({});
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

async function fetchRoles() {
	const { data } = await listRoles();
	const list = Array.isArray(data) ? data : data?.data ?? [];
	roleOptions.value = list
		.map((role: { id?: string | number; name?: string }) => ({
			title: role?.name ?? '',
			value: role?.id ?? '',
		}))
		.filter((role: RoleOption) => role.title && role.value !== '');
}

	const rules = {
		required: (value: unknown) => (value ? true : 'Required'),
		email: (value: string) =>
			!value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? true : 'Invalid email',
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

	async function onSubmit() {
		error.value = '';
		fieldErrors.value = {};
		const result = await formRef.value?.validate?.();
		if (result && result.valid === false) return;
		if (!result && formRef.value?.validate) {
		const ok = await formRef.value.validate();
		if (!ok) return;
	}

	loading.value = true;
		try {
			const payload = {
				name: form.value.name,
				email: form.value.email,
				username: form.value.username,
				role_id: form.value.role_id,
				password: form.value.password,
				confirm_password: form.value.confirm_password,
			};
			const { data } = await createAdmin(payload);
			const created = data?.data?.created ?? data?.created ?? false;
			const message = data?.data?.message ?? data?.message ?? '';
			const field = data?.data?.field ?? data?.field ?? '';
			if (!created) {
				if (field) {
					fieldErrors.value = { [field]: [message || 'Invalid value.'] };
				} else {
					const lowered = message.toLowerCase();
					if (lowered.includes('email')) {
						fieldErrors.value = { email: [message] };
					} else if (lowered.includes('username')) {
						fieldErrors.value = { username: [message] };
					} else {
						error.value = message || 'Failed to create admin.';
					}
				}
				return;
			}
			emit('saved', data ?? payload);
			emit('close');
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
			console.error('Failed to create admin:', message);
			error.value = message;
		} finally {
			loading.value = false;
		}
	}

onMounted(fetchRoles);
</script>
