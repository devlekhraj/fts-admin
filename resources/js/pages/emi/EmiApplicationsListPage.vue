<template>
	<div>
		<!-- <AppPageHeader title="EMI Applications" subtitle="Manage EMI applications" /> -->
		<AppDataTable :headers="headers" :items="items" :total="total" :loading="loading"
			:items-per-page="options.itemsPerPage" @update:options="onOptions">
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
					:to="{ name: 'emi.applications.detail', params: { id: item.id } }" icon>
					<v-icon>mdi-eye</v-icon>
				</v-btn>
			</template>
		</AppDataTable>
	</div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listEmiApplications } from '@/api/emi-applications.api';
import { formatDateTime, formatNPR } from '@/shared/formatters';
import { getEmiIconColor, getEmiIconTextColor, getEmiTypeIcon, statusColor } from '@/shared/emi';

type EmiApplication = Record<string, unknown>;

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

const items = ref<EmiApplication[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
	page: 1,
	itemsPerPage: 15,
	sortBy: [],
});
const hasLoadedOnce = ref(false);

async function fetchApplications() {
	loading.value = true;
	try {
		const { data } = await listEmiApplications({
			page: options.value.page,
			per_page: options.value.itemsPerPage,
		});

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
	fetchApplications();
}

onMounted(() => {
	if (!hasLoadedOnce.value) {
		fetchApplications();
		hasLoadedOnce.value = true;
	}
});
</script>
