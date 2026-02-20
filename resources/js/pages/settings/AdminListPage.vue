<template>
	<AppDataTable :headers="headers" :items="items" :total="total" :loading="loading"
		:items-per-page="options.itemsPerPage" @update:options="onOptions">
		<template #actions>
			<v-container fluid>
				<v-row>
					<v-col cols="12" md="3">
						<v-text-field v-model="filters.query" density="compact" variant="outlined" label="Search"
							placeholder="Search by name, email, or username" prepend-inner-icon="mdi-magnify"
							hide-details clearable style="min-width: 200px"
							@update:model-value="onQueryChange" />
					</v-col>
					<v-col cols="12" md="3">
						<v-autocomplete v-model="filters.role" :items="roleOptions" density="compact" variant="outlined"
							label="Role" prepend-inner-icon="mdi-shield-account-outline" hide-details clearable
							style="min-width: 200px"
							@update:model-value="onRoleChange" />
					</v-col>
					<v-col cols="12" md="2" class="d-flex align-center">
						<v-btn color="primary" variant="tonal" height="40" @click="onSearch">
							<v-icon start>mdi-magnify</v-icon>
							Search
						</v-btn>
					</v-col>
					<v-col cols="12" md="4" class="d-flex align-center justify-end">
						<AdminCreateButton @saved="onAdminCreated" />
					</v-col>
				</v-row>
				<v-row>
					<v-col cols="12">
						<div class="text-medium-emphasis">
							<span class="text-primary" style="font-size: smaller;">
								Total: {{ total }} Items found.
							</span>
						</div>
					</v-col>
				</v-row>
			</v-container>
		</template>
		<template #item.name="{ item }">
			<div class="d-flex align-center gap-2">
				<v-avatar size="28" color="grey-lighten-3">
					<v-icon size="18" color="grey-darken-1">mdi-account-circle</v-icon>
				</v-avatar>
				<span class="ml-2 text-capitalize">{{ item.name ?? '-' }}</span>
			</div>
		</template>
		<template #item.role="{ item }">
			<v-chip v-if="item.role" size="small" variant="tonal" class="text-uppercase font-weight-bold" :color="roleColor(item.role)" label>
				<v-icon start size="14">{{ roleIcon(item.role) }}</v-icon>
				{{ item.role }}
			</v-chip>
			<span v-else>-</span>
		</template>
		<template #item.username="{ item }">
			<div class="d-flex align-center gap-2">
				<span>{{ item.username ?? '-' }}</span>
				<v-btn v-if="item.username" icon variant="text" size="x-small" @click="copyUsername(item.username)">
					<v-icon size="16">mdi-content-copy</v-icon>
				</v-btn>
			</div>
		</template>
		<template #item.action="{ item }">
			<v-btn variant="tonal" color="primary" size="x-small" icon @click="onEdit(item)">
				<v-icon>mdi-cog</v-icon>
			</v-btn>
			<v-btn variant="tonal" class="ml-2" color="error" size="x-small" icon>
				<v-icon>mdi-delete</v-icon>
			</v-btn>

		</template>
	</AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import AdminCreateButton from '@/components/admin/AdminCreateButton.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listAdmins } from '@/api/admins.api';
import { list as listRoles } from '@/api/roles.api';

// RoleOption interface
interface RoleOption {
	title: string;
	value: string | number;
}

type Admin = {
	id?: number | string;
	name?: string | null;
	email?: string | null;
	username?: string | null;
	role_id?: number | string | null;
	role?: string | null;
};

const headers = [
	{ title: 'Name', key: 'name', minWidth: '180' },
	{ title: 'Username', key: 'username', minWidth: '160' },
	{ title: 'Role', key: 'role', minWidth: '160' },
	{ title: 'Email', key: 'email', minWidth: '220' },
	{ title: 'Created', key: 'created_at', minWidth: '100' },
	{ title: 'Actions', key: 'action', sortable: false, minWidth: '100' },
];

const items = ref<Admin[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
	page: 1,
	itemsPerPage: 12,
	sortBy: [],
});
const filters = ref({
	query: '',
	role: null as null | string | number,
});
const roleOptions = ref<Array<{ title: string; value: string | number }>>([]);
const hasLoadedOnce = ref(false);

function normalizeRole(admin: Admin): string | null {
	return admin.role ?? null;
}

async function fetchAdmins() {
	loading.value = true;
	try {
		const { data } = await listAdmins({
			page: options.value.page,
			per_page: options.value.itemsPerPage,
			search: filters.value.query || undefined,
			role_id: filters.value.role ?? undefined,
		});

		const list = Array.isArray(data) ? data : data?.data ?? [];
		items.value = list.map((admin: Admin) => ({
			...admin,
			role: normalizeRole(admin),
		}));
		total.value = data?.total ?? data?.meta?.total ?? list.length;
	} finally {
		loading.value = false;
	}
}

function onOptions(next: DataTableOptions) {
	options.value = next;
	if (!hasLoadedOnce.value) {
		hasLoadedOnce.value = true;
	}
	fetchAdmins();
}

function onSearch() {
	options.value.page = 1;
	fetchAdmins();
}

function onQueryChange(value: string | null) {
	if (!value) {
		options.value.page = 1;
		fetchAdmins();
	}
}

	function onRoleChange(value: string | number | null) {
		if (!value) {
			options.value.page = 1;
			fetchAdmins();
		}
	}

	function onAdminCreated() {
		options.value.page = 1;
		fetchAdmins();
	}

async function fetchRoles() {
	const { data } = await listRoles();
	const list = Array.isArray(data) ? data : data?.data ?? [];
	roleOptions.value = list.map((role: { id?: string | number; name?: string }) => ({
		title: role?.name ?? '',
		value: role?.id ?? '',
	})).filter((role: RoleOption) => role.title && role.value !== '');
}

function onEdit(_admin: Admin) {
	// TODO: wire edit action once route/page is defined.
}

async function copyUsername(username: string) {
	try {
		await navigator.clipboard.writeText(username);
	} catch {
		// No-op: clipboard may be blocked by the browser.
	}
}

function roleColor(role: string) {
	const key = role.trim().toLowerCase().replace(/\s+/g, '-');
	const palette: Record<string, string> = {
		'content-writer': 'warning',
		administrator: 'primary',
		manager: 'success',
		csr: 'orange',
	};

	if (palette[key]) return palette[key];
	if (key.includes('admin')) return 'primary';
	if (key.includes('manager')) return 'success';
	if (key.includes('writer')) return 'warning';
	if (key.includes('csr')) return 'orange';
	return 'grey';
}

function roleIcon(role: string) {
	const key = role.trim().toLowerCase().replace(/\s+/g, '-');
	const icons: Record<string, string> = {
		'content-writer': 'mdi-pencil',
		administrator: 'mdi-shield-crown-outline',
		manager: 'mdi-briefcase-account-outline',
		csr: 'mdi-headset',
	};

	if (icons[key]) return icons[key];
	if (key.includes('admin')) return 'mdi-shield-account-outline';
	if (key.includes('manager')) return 'mdi-briefcase-account-outline';
	if (key.includes('writer')) return 'mdi-pencil';
	if (key.includes('csr')) return 'mdi-headset';
	return 'mdi-account-circle-outline';
}

onMounted(() => {
	if (!hasLoadedOnce.value) {
		fetchAdmins();
		hasLoadedOnce.value = true;
	}
	fetchRoles();
});

// Server-side filtering is triggered by the Search button.
</script>

<style scoped lang="scss">
:deep(.v-toolbar__content) {
	height: unset !important;
}
</style>
