<template>
	<v-card-text class="pt-4">
		<v-tabs v-model="activeTab" density="compact" class="">
			<v-tab v-for="tab in tabs" :key="tab.value" :value="tab.value">
				{{ tab.label }}
			</v-tab>
		</v-tabs>
		<v-divider></v-divider>

		<v-window v-model="activeTab">
			<v-window-item v-for="tab in tabs" :key="tab.value" :value="tab.value" class="pt-4">
				<component :is="tab.component" :admin="props.admin" @saved="handleSaved" />
			</v-window-item>
		</v-window>
	</v-card-text>

</template>

<script setup lang="ts">
import { ref } from 'vue';
import AdminUpdateBasicTab from '@/components/admin/AdminUpdateBasicTab.vue';
import AdminUpdateEmailTab from '@/components/admin/AdminUpdateEmailTab.vue';
import AdminUpdatePasswordTab from '@/components/admin/AdminUpdatePasswordTab.vue';

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
};

const props = defineProps<{ admin: Admin }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const activeTab = ref<'basic' | 'email' | 'password'>('basic');
const tabs = [
	{ value: 'basic' as const, label: 'Basic Info', component: AdminUpdateBasicTab },
	{ value: 'password' as const, label: 'Password', component: AdminUpdatePasswordTab },
	{ value: 'email' as const, label: 'Email', component: AdminUpdateEmailTab },
];

function handleSaved(payload?: unknown) {
	emit('saved', payload);
	emit('close');
}
</script>
