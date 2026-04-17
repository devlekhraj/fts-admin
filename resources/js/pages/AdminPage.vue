<template>
	<AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
		:items-per-page="options.itemsPerPage" @update:options="onOptions">
		<template #actions>
			<v-container fluid>
				<v-row>
					<v-col cols="12" md="3">
						<v-text-field v-model="filters.query" density="compact" variant="outlined" label="Search"
							placeholder="Search by name, email, or username" prepend-inner-icon="mdi-magnify"
							hide-details clearable style="min-width: 200px" @update:model-value="onQueryChange" />
					</v-col>
					<v-col cols="12" md="3">
						<v-autocomplete v-model="filters.role" :items="roleOptions" density="compact" variant="outlined"
							label="Role" prepend-inner-icon="mdi-shield-account-outline" hide-details clearable
							style="min-width: 200px" @update:model-value="onRoleChange" />
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
					<v-img v-if="item.avatar_url" :src="item.avatar_url" :alt="item.name ?? 'Admin'" cover />
					<v-icon v-else size="18" color="grey-darken-1">mdi-account-circle</v-icon>
				</v-avatar>
				<div class="ml-2">
					<div class="text-capitalize">{{ item.name ?? '-' }}</div>
					<div class="text-medium-emphasis" style="font-size: 0.8rem;">
						@{{ item.username ?? 'no.username' }}
					</div>
				</div>
			</div>
		</template>
		<template #item.role="{ item }">
			<!-- <v-chip v-if="item.role" size="small" variant="tonal" class="text-uppercase"
				:color="roleColor(item.role)" label>
				
				{{ item.role }}
			</v-chip> -->
			<!-- <span v-else>-</span> -->
			 <v-chip class="small" variant="tonal" size="small" label :color="item.role ? roleColor(item.role) : 'grey-lighten-2'">
				 {{ item.role ?? '-' }}
			 </v-chip>
		</template>
		
		<template #item.action="{ item }" class="justify-end">
			<AdminActionButtons :admin="item" @saved="onAdminUpdated" @deleted="onAdminDeleted" />
		</template>
		<template #item.created_at="{ item }">
			<span class="text-medium-emphasis" style="font-size: 0.8rem;">
				{{ item.created_at ? formatLongDate(item.created_at) :  '-' }}
			</span>
		</template>
	</AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import AdminActionButtons from '@/components/admin/AdminActionButtons.vue';
import AdminCreateButton from '@/components/admin/AdminCreateButton.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listAdmins, type AdminListItem, type AdminListResponse } from '@/api/admins.api';
import { list as listRoles } from '@/api/roles.api';
import { formatLongDate } from '@/shared/utils';

// RoleOption interface
interface RoleOption {
	title: string;
	value: string | number;
}

type Admin = {
	id?: number | string;
	name?: string | null;
	avatar_url?: string | null;
	email?: string | null;
	username?: string | null;
	role?: string | null;
	role_id?: number | string | null;
	created_at?: string | null;
};

const headers = [
	{ title: 'Name', key: 'name', minWidth: '180', sortable: false },
	// { title: 'Username', key: 'username', minWidth: '160', sortable: false },
	{ title: 'Role', key: 'role', minWidth: '160', sortable: false },
	{ title: 'Email', key: 'email', minWidth: '220', sortable: false },
	{ title: 'Created', key: 'created_at', minWidth: '100', sortable: false },
	{ title: 'Actions', key: 'action', sortable: false, minWidth: '100', align: 'end' as const },
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

async function fetchAdmins() {
	loading.value = true;
	try {
		const response: AdminListResponse = await listAdmins({
			page: options.value.page,
			per_page: options.value.itemsPerPage,
			search: filters.value.query || undefined,
			role_id: filters.value.role ?? undefined,
		});

		const list = Array.isArray(response?.data) ? response.data : [];
		items.value = list.map((admin: AdminListItem) => ({
			id: admin.id,
			avatar_url: admin.avatar_url ?? null,
			name: admin.name ?? null,
			email: admin.email ?? null,
			username: admin.username ?? null,
			role: admin.role ?? null,
			role_id: admin.role_id ?? null,
			created_at: admin.created_at ?? null,
		}));
		total.value = Number(response?.meta?.total ?? list.length);
		if (response?.meta?.current_page) {
			options.value.page = Number(response.meta.current_page);
		}
		if (response?.meta?.per_page) {
			options.value.itemsPerPage = Number(response.meta.per_page);
		}
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

function onAdminUpdated() {
	options.value.page = 1;
	fetchAdmins();
}

function onAdminDeleted() {
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

function onSetUsername(_admin: Admin) {
	// TODO: wire set-username flow once route/modal is defined.
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
