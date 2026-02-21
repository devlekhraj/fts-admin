<template>
	<v-btn color="primary" @click="open">Add Setting</v-btn>
</template>

<script setup lang="ts">
import SettingForm from '@/components/settings/SettingForm.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
	openModal(
		SettingForm,
		{},
		{
			title: 'Add Setting',
			size: 'lg',
			onSaved: (payload: unknown) => {
				emit('saved', payload);
			},
		},
	);
}
</script>
