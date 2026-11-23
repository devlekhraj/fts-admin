<template>
	<v-btn v-if="auth.can('admins-edit')" variant="outlined" color="primary" size="small"  @click="onEdit">
		Edit
	</v-btn>
	<v-btn v-if="canDelete" variant="outlined" class="ml-2" color="error" size="small"  @click="onDelete">
		Delete
	</v-btn>
</template>

<script setup lang="ts">
import AdminDeleteModal from '@/components/admin/AdminDeleteModal.vue';
import AdminUpdateModal from '@/components/admin/AdminUpdateModal.vue';
import { openModal } from '@/shared/modal';
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth.store';

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
	role?: string | null;
};

const props = defineProps<{ admin: Admin }>();
const auth = useAuthStore();
// Nobody deletes their own account here; the server refuses it too.
const canDelete = computed(() => auth.can('admins-delete') && String(props.admin.id ?? '') !== String(auth.admin?.id ?? ''));
const emit = defineEmits<{
	(e: 'saved', payload?: unknown): void;
	(e: 'deleted', payload?: unknown): void;
}>();

function onEdit() {
	openModal(
		AdminUpdateModal,
		{ admin: props.admin },
		{
			title: 'Update Admin',
			size: 'md',
			onSaved: (payload: unknown) => {
				emit('saved', payload);
			},
		},
	);
}

function onDelete() {
	openModal(
		AdminDeleteModal,
		{ admin: props.admin },
		{
			title: 'Confirm Admin Deletion',
			size: 'sm',
			onSaved: (payload: unknown) => {
				emit('deleted', payload);
			},
		},
	);
}
</script>
