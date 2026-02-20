<template>
	<v-card-text class="py-6">
		<v-alert v-if="error" type="error" variant="tonal" class="mb-4">
			{{ error }}
		</v-alert>
		<div class="mt-3 d-flex align-center justify-space-around py-6">
			<div style="width: max-content;" class="d-flex align-center">
				<v-avatar size="40" color="grey-lighten-3">
					<v-icon size="22" color="grey-darken-1">mdi-account-circle</v-icon>
				</v-avatar>
				<div class="ml-3">
					<div class="font-weight-medium text-capitalize">{{ admin.name ?? '-' }}</div>
					<div class="text-medium-emphasis">@{{ admin.username ?? admin.email }}</div>
					
				</div>
			</div>
		</div>
	</v-card-text>

	<v-card-actions class="pb-4">
		<div class="w-100 d-flex align-center justify-space-around">
			<v-btn color="error" variant="tonal" :loading="loading" :disabled="loading" @click="onConfirm">
				<v-icon start>mdi-delete</v-icon>
				Delete
			</v-btn>
		</div>
	</v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteAdmin } from '@/api/admins.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
};

const props = defineProps<{ admin: Admin }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const loading = ref(false);
const error = ref('');
const snackbar = useSnackbarStore();

async function onConfirm() {
	error.value = '';
	if (!props.admin?.id) {
		error.value = 'Admin id is required.';
		return;
	}

	loading.value = true;
	try {
		await deleteAdmin(props.admin.id);
		snackbar.show({ message: 'Admin deleted successfully.', color: 'success' });
		emit('saved', { id: props.admin.id });
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
			const message = next.id?.[0] ?? 'Failed to delete admin.';
			error.value = message;
			snackbar.show({ message, color: 'error' });
			return;
		}
		const message = getErrorMessage(err);
		error.value = message;
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
