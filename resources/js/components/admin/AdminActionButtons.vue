<template>
	<v-btn variant="flat" color="primary" size="small"  @click="onEdit">
		Edit
	</v-btn>
	<v-btn variant="flat" class="ml-2" color="error" size="small"  @click="onDelete">
		Delete
	</v-btn>
</template>

<script setup lang="ts">
import AdminDeleteModal from '@/components/admin/AdminDeleteModal.vue';
import AdminUpdateModal from '@/components/admin/AdminUpdateModal.vue';
import { openModal } from '@/shared/modal';

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
	role?: string | null;
};

const props = defineProps<{ admin: Admin }>();
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
