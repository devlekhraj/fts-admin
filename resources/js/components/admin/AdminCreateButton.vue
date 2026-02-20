<template>
	<v-btn color="primary" variant="flat" height="40" @click="open">
		<v-icon start>mdi-account-plus</v-icon>
		Add Admin
	</v-btn>
</template>

<script setup lang="ts">
import AdminCreateModal from '@/components/admin/AdminCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
	openModal(
		AdminCreateModal,
		{},
		{
			title: 'Add Admin',
			size: 'md',
			onSaved: (payload: unknown) => {
				emit('saved', payload);
			},
		},
	);
}
</script>
