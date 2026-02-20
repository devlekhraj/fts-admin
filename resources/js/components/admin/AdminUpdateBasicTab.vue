<template>
	<v-form ref="formRef" @submit.prevent="onSubmit">
		<div class="px-6 pt-3">
			<v-container>
				<v-row>
					<v-col cols="12" md="12" class="pb-0">
						<v-text-field v-model="form.name" label="Name" variant="outlined" density="comfortable"
							:rules="[rules.required]" :error-messages="getErrorMessages('name')"
							prepend-inner-icon="mdi-account" @update:model-value="clearFieldError('name')" />
					</v-col>
					<v-col cols="12" md="6" class="pb-0">
						<v-text-field v-model="form.username" label="Username" variant="outlined" density="comfortable"
							autocomplete="off"
							:rules="[rules.required, rules.noSpaces, rules.lowercase, rules.allowedChars, rules.dotPlacement]"
							:error-messages="getErrorMessages('username')"
							prepend-inner-icon="mdi-account-circle-outline" @update:model-value="onUsernameInput" />
					</v-col>
					<v-col cols="12" md="6" class="pb-0">
						<v-autocomplete v-model="form.role_id" :items="roleOptions" label="Role" variant="outlined"
							density="comfortable" clearable :rules="[rules.required]"
							:error-messages="getErrorMessages('role_id')"
							prepend-inner-icon="mdi-shield-account-outline"
							@update:model-value="clearFieldError('role_id')" />
					</v-col>
					<v-col cols="12" class="d-flex justify-space-around pt-8">
						<v-btn color="primary" size="large" variant="tonal" :loading="loading" :disabled="loading"
							@click="onSubmit">
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
import { onMounted, ref } from 'vue';
import { list as listRoles } from '@/api/roles.api';
import { updateBasicInfo } from '@/api/admins.api';
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

type RoleOption = { title: string; value: string | number };

type BasicForm = {
	name: string;
	username: string;
	role_id: string | number | null;
};

const props = defineProps<{ admin: Admin }>();
const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

const formRef = ref();
const roleOptions = ref<RoleOption[]>([]);
const error = ref('');
const snackbar = useSnackbarStore();
const loading = ref(false);
const form = ref<BasicForm>({
	name: props.admin?.name ?? '',
	username: props.admin?.username ?? '',
	role_id: props.admin?.role_id ?? null,
});
const fieldErrors = ref<Record<string, string[]>>({});

const rules = {
	required: (value: unknown) => (value ? true : 'Required'),
	noSpaces: (value: string) =>
		!value || !/\s/.test(value) ? true : 'Username must not contain spaces',
	lowercase: (value: string) =>
		!value || value === value.toLowerCase() ? true : 'Username must be lowercase',
	allowedChars: (value: string) =>
		!value || /^[a-z0-9_.-]+$/.test(value)
			? true
			: 'Only letters, numbers, underscore, dash, and dot are allowed',
	dotPlacement: (value: string) =>
		!value || (!value.startsWith('.') && !value.endsWith('.'))
			? true
			: 'Dot cannot be the first or last character',
};

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

function onUsernameInput(value: string) {
	if (typeof value === 'string') {
		form.value.username = value.toLowerCase();
	}
	clearFieldError('username');
}

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

	if (!props.admin?.id) {
		error.value = 'Admin id is required.';
		return;
	}

	loading.value = true;
	try {
		const payload = {
			id: props.admin.id,
			name: form.value.name,
			username: form.value.username,
			role_id: form.value.role_id,
		};
		const { data } = await updateBasicInfo(payload);
		const updated = data?.data?.updated ?? data?.updated ?? true;
		const message = data?.data?.message ?? data?.message ?? '';
		const field = data?.data?.field ?? data?.field ?? '';
		if (updated === false) {
			if (field) {
				fieldErrors.value = { [field]: [message || 'Invalid value.'] };
			} else {
				error.value = message || 'Failed to update admin.';
				snackbar.show({ message: error.value, color: 'error' });
			}
			return;
		}
		snackbar.show({ message: message || 'Admin updated successfully.', color: 'success' });
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
		console.error('Failed to update admin:', message);
		error.value = message;
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}

onMounted(fetchRoles);
</script>
