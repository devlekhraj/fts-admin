<template>
	<div>
		<!-- <AppPageHeader title="EMI Requests" subtitle="Manage EMI requests" /> -->
		<AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :searchable="false"
			:items-per-page="options.itemsPerPage" @update:options="onOptions">
			<template #actions>
				<v-container fluid>
					<v-row>
						<v-col cols="12" md="3">
							<v-text-field v-model="filters.query" density="compact" variant="outlined" label="Search"
								hide-details clearable style="min-width: 220px" />
						</v-col>
						<v-col cols="12" md="3">
							<v-select v-model="filters.emiType" :items="emiTypeOptions" density="compact" variant="outlined"
								label="EMI Type" hide-details clearable style="min-width: 200px" />
						</v-col>
						<v-col cols="12" md="3">
							<v-select v-model="filters.status" :items="statusOptions" density="compact" variant="outlined"
								label="Status" hide-details clearable style="min-width: 200px" />
						</v-col>
					</v-row>
				</v-container>
			</template>
			<template #item.user="{ item }">
				<div class="d-flex align-center gap-2">
					<v-avatar size="32">
						<v-img :src="item.user?.avatar" alt="User" />
					</v-avatar>
					<span class="ml-3">{{ item.user?.name ?? '-' }}</span>
				</div>
			</template>
			<template #item.product="{ item }">
				<div class="d-flex align-center gap-2">
					<v-avatar size="32" rounded>
						<v-img :src="item.product?.thumb" alt="Product" />
					</v-avatar>
					<span class="ml-3">{{ item.product?.name ?? '-' }}</span>
				</div>
			</template>
			<template #item.time="{ item }">
				<span>{{ formatDateTime(item.time) }}</span>
			</template>
			<template #item.emi_per_month="{ item }">
				<span>{{ formatNPR(item.emi_per_month) }}</span>
			</template>
			<template #item.emi_type="{ item }">
				<div style="min-width: 150px" class="d-flex align-center">
					<v-icon start class="mr-2" :color="getEmiIconColor(item.emi_type)"
						:icon="getEmiTypeIcon(item.emi_type)" />
					<span class="text-capitalize" :class="getEmiIconTextColor(item.emi_type)" style="font-weight: 500;">
						{{ item.emi_type ? item.emi_type.split('_').join(' ') : 'Citizenship' }}
					</span>
				</div>
			</template>
			<template #item.emi_mode="{ item }">
				<span class="text-capitalize">{{ item.emi_mode ?? '-' }}</span>
			</template>
			<template #item.status_label="{ item }">
				<v-chip size="small" :color="statusColor(item.status)" variant="tonal">
					{{ item.status_label ?? item.status ?? '-' }}
				</v-chip>
			</template>
			<template #item.action="{ item }">
				<v-btn variant="tonal" color="primary" size="x-small"
					:to="{ name: 'emi.requests.detail', params: { id: item.id } }" icon>
					<v-icon>mdi-eye</v-icon>
				</v-btn>
			</template>
		</AppDataTable>
	</div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listEmiRequests } from '@/api/emi-requests.api';
import { formatDateTime, formatNPR } from '@/shared/formatters';
import { getEmiIconColor, getEmiIconTextColor, getEmiTypeIcon, statusColor } from '@/shared/emi';

type EmiRequest = Record<string, unknown>;

const headers = [
	{ title: 'REQUEST#', key: 'application_code', sortable: false, minWidth:'120' },
	{ title: 'User', key: 'user', sortable: false,minWidth:'200' },
	{ title: 'Product', key: 'product', sortable: false, minWidth:'250' },
	{ title: 'Time', key: 'time', minWidth: '160', sortable: false },
	{ title: 'Per Month', key: 'emi_per_month', minWidth: '120', sortable: false },
	{ title: 'EMI Type', key: 'emi_type', sortable: false, minWidth:'120' },
	{ title: 'Status', key: 'status_label', sortable: false },
	{ title: 'Actions', key: 'action', sortable: false },
];

const items = ref<EmiRequest[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
	page: 1,
	itemsPerPage: 15,
	sortBy: [],
});
const filters = ref({
	query: '',
	emiType: null as null | string,
	status: null as null | number,
});
const hasLoadedOnce = ref(false);

const emiTypeOptions = [
	{ title: 'Credit Card', value: 'credit_card' },
	{ title: 'New Credit Card', value: 'new_credit_card' },
	{ title: 'Citizenship', value: 'citizenship' },
];

const statusOptions = [
	{ title: 'Pending', value: 0 },
	{ title: 'Processing', value: 1 },
	{ title: 'Approved', value: 2 },
	{ title: 'Finished', value: 3 },
	{ title: 'Cancelled', value: 4 },
];

async function fetchRequests() {
	loading.value = true;
	try {
		const params: Record<string, unknown> = {
			page: options.value.page,
			per_page: options.value.itemsPerPage,
		};

		if (filters.value.query) params.search = filters.value.query;
		if (filters.value.emiType) params.emi_type = filters.value.emiType;
		if (filters.value.status !== null) params.status = filters.value.status;

		const { data } = await listEmiRequests(params);

		const list = Array.isArray(data) ? data : data?.data ?? [];
		items.value = list;
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
	fetchRequests();
}

watch(
	filters,
	() => {
		options.value.page = 1;
		fetchRequests();
	},
	{ deep: true }
);

onMounted(() => {
	if (!hasLoadedOnce.value) {
		fetchRequests();
		hasLoadedOnce.value = true;
	}
});
</script>
