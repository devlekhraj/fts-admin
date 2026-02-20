<template>
	<v-form ref="formRef" @submit.prevent="onSubmit">
		<v-alert v-if="error" type="error" variant="tonal" class="mb-4">
			{{ error }}
		</v-alert>
		<div class="px-6 pt-3">
			<v-container>
				<v-row>
					<v-col cols="12" md="12" class="pb-0">
						<v-text-field v-model="form.email" label="New Email" type="email" variant="outlined"
							density="comfortable" :rules="[rules.required, rules.email]"
							:error-messages="getErrorMessages('email')" prepend-inner-icon="mdi-email-outline"
							@update:model-value="clearFieldError('email')" />
					</v-col>
					<v-col cols="12" md="7" class="pb-0">
						<v-text-field v-model="verificationCode" label="Verification Code" variant="outlined"
							density="comfortable" :rules="[rules.required]"
							:error-messages="getErrorMessages('verification_code')"
							prepend-inner-icon="mdi-shield-key-outline"
							@update:model-value="clearFieldError('verification_code')" />
					</v-col>
					<v-col cols="12" md="5" class="pb-0">
						<v-btn variant="tonal" color="primary" size="large" :disabled="loading" @click="onSendCode">
							<v-icon start>mdi-email-send-outline</v-icon>
							Send Code
						</v-btn>
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

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
	role?: string | null;
};

type EmailForm = {
	email: string;
};

const props = defineProps<{ admin: Admin }>();
const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

const formRef = ref();
const error = ref('');
const loading = ref(false);
const form = ref<EmailForm>({
	email: props.admin?.email ?? '',
});
const verificationCode = ref('');
const fieldErrors = ref<Record<string, string[]>>({});

const rules = {
	required: (value: unknown) => (value ? true : 'Required'),
	email: (value: string) =>
		!value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? true : 'Invalid email',
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

function onSendCode() {
	// TODO: wire send verification code API.
	clearFieldError('email');
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
		emit('saved', {
			id: props.admin?.id,
			email: form.value.email,
			verification_code: verificationCode.value || undefined,
			tab: 'email',
		});
	} finally {
		loading.value = false;
	}
}
</script>
