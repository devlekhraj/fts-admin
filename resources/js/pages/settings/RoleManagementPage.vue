<template>
	<!-- <AppPageHeader title="Role Management" subtitle="Manage roles and their permissions" /> -->

	<v-expansion-panels v-model="expandedPanels" multiple elevation="0">
		<v-expansion-panel v-for="role in roles" :key="role.id ?? role.name ?? 'unknown-role'">
			<v-expansion-panel-title>
				<div class="d-flex align-center justify-space-between w-100">
					<div class="d-flex align-center">
						<h4 class="text-capitalize text-primary">{{ role.name ?? '-' }}</h4>
						<span class="text-medium-emphasis ml-2">Permissions: {{ role.permissions?.length ?? 0 }}</span>
					</div>
					<div class="d-flex align-center">
						<v-btn icon variant="tonal" size="x-small" color="primary" class="mr-2" @click.stop="onEditRole(role)">
							<v-icon size="16">mdi-cog</v-icon>
						</v-btn>
						<v-btn icon variant="tonal" color="error" size="x-small" @click.stop="onDeleteRole(role)">
							<v-icon size="16">mdi-delete</v-icon>
						</v-btn>
					</div>
				</div>
			</v-expansion-panel-title>
			<v-expansion-panel-text>
				<div class="text-medium-emphasis mb-2">Permissions</div>
				<div v-if="role.permissions?.length" class="d-flex flex-wrap gap-2">
					<v-chip v-for="permission in role.permissions" :key="permission" color="primary" size="small" variant="tonal"
						class="text-uppercase font-bold-medium mr-2 mb-2" label>
						{{ permission }}
					</v-chip>
				</div>
				<div v-else class="text-medium-emphasis">No permissions found.</div>
			</v-expansion-panel-text>
		</v-expansion-panel>
		<div v-if="!roles.length" class="text-medium-emphasis pa-2">No roles found.</div>
	</v-expansion-panels>
</template>

<script setup lang="ts">
// import AppPageHeader from '@/components/AppPageHeader.vue';
import { list as listRoles } from '@/api/roles.api';
import { onMounted, ref } from 'vue';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

type Role = {
	id?: number | string;
	name?: string | null;
	permissions?: string[];
};

const snackbar = useSnackbarStore();
const roles = ref<Role[]>([]);
const expandedPanels = ref<number[]>([]);

async function fetchRoles() {
	try {
		const { data } = await listRoles();
		const list = Array.isArray(data) ? data : data?.data ?? [];
		roles.value = list;
		expandedPanels.value = roles.value.map((_, index) => index);
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	}
}

onMounted(() => {
	fetchRoles();
});

function onEditRole(_role: Role) {
	// TODO: wire edit role modal or route.
	snackbar.show({ message: 'Edit role not implemented yet.', color: 'info' });
}

function onDeleteRole(_role: Role) {
	// TODO: wire delete role confirmation and API.
	snackbar.show({ message: 'Delete role not implemented yet.', color: 'info' });
}
</script>

<style scoped lang="scss">
:deep(.v-toolbar__content) {
	height: unset !important;
}

.v-expansion-panel {
	background-color: #f9f9f9;
}
</style>
