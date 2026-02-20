<template>
	<v-btn variant="tonal" color="primary" size="x-small" icon @click="onEdit">
		<v-icon>mdi-cog</v-icon>
	</v-btn>
	<v-btn variant="tonal" class="ml-2" color="error" size="x-small" icon @click="onDelete">
		<v-icon>mdi-delete</v-icon>
	</v-btn>
</template>

<script setup lang="ts">
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
	(e: 'delete', admin: Admin): void;
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
	emit('delete', props.admin);
}
</script>
