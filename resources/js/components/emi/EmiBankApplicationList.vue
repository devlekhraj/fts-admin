<template>
	<v-container class="pt-0" fluid>
		<v-row>
			<v-col cols="12" lg="10" offset-lg="1">
				<div>
					<div class="pt-10">
						<div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
							<div>
								<div class="text-h6 mb-1">Application</div>
								<div class="text-body-2 text-medium-emphasis">Generated bank applications and approval status.</div>
							</div>
							<v-btn
								v-if="String(props.data?.status ?? '').toLowerCase() !== 'approved'"
								color="primary"
								variant="flat"
								prepend-icon="mdi-file-plus"
								@click="modalGenerate()">
								Generate Application
							</v-btn>
						</div>
					</div>
					<div>
						<v-data-table :headers="headers" :items="items" density="comfortable" class="elevation-0 border rounded"
							:loading="loading" :hide-default-footer="items.length <= 10">
							<template #item.created_at="{ item }">
								<div class="application-date-cell">{{ item.created_at ?? '--' }}</div>
							</template>
							<template #item.status="{ item }">
								<div class="">
									<v-chip class="text-capitalize"
										label
										:color="(item.status.toLowerCase()) === 'approved' ? 'success' : 'warning'"
										text-color="white" size="small">
										{{ item.status ?? '--' }}
									</v-chip>
								</div>
							</template>
							<template #item.action="{ item }">
								<div class="d-flex justify-end gap-2">
									<v-btn variant="flat" color="primary" class="mr-4" size="small"
										:disabled="!item.path" @click="openFile(item.path)">
										<v-icon>mdi-eye</v-icon> View
									</v-btn>
									<v-btn variant="flat" color="success" class="mr-4" size="small"
										:disabled="!item.path" @click="handleApprove(item)">
										<v-icon>mdi-cog</v-icon> Action
									</v-btn>
								
								</div>
							</template>
						</v-data-table>
					</div>
				</div>
			</v-col>
		</v-row>
	</v-container>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useModalStore } from '@/stores/modal.store';
import EmiRequestForm from '@/components/emi/EmiRequestForm.vue';
import EmiApprovalForm from '@/components/emi/EmiApprovalForm.vue';
import { listApplications } from '@/api/emi-requests.api';
import { formatLongDate } from '@/shared/utils';
import type { DataTableHeader } from 'vuetify';

const headers: DataTableHeader[] = [
	{ title: '#ID', key: 'application_id', align: 'start', width: '200' },
	{ title: 'Bank', key: 'bank', align: 'start', width: '200' },
	{ title: 'Status', key: 'status', align: 'start', width: '100' },
	{ title: 'Date', key: 'created_at', align: 'start', width: '150' },
	{ title: 'Action', key: 'action', align: 'end', sortable: false, width: '160' },
];

const props = defineProps<{ data: Record<string, any> }>();
const modal = useModalStore();
const loading = ref(false);
const records = ref<Record<string, any>[]>([]);

const items = computed(() =>
	records.value.map((record) => ({
		id: record.id,
		application_id: record.application_id,
		bank: record.bank_name ?? '--',
		status: record.status ?? '--',
		created_at: formatLongDate(record.created_at) ?? '--',
		path: record.file_url ?? record.file_path ?? '',
	})),
);

async function fetchApplications() {
	const requestId = String(props.data?.id ?? '');
	if (!requestId) return;
	loading.value = true;
	try {
		const response = await listApplications(requestId);
		records.value = Array.isArray(response?.data) ? response.data : [];
	} finally {
		loading.value = false;
	}
}

function modalGenerate() {
	modal.open(
		EmiRequestForm,
		{ id: props.data?.id },
		{
			title: 'Generate Application',
			size: 'lg',
			onSaved: () => {
				fetchApplications();
			},
		},
	);
}

function openFile(path: string) {
	if (!path) return;
	window.open(path, '_blank');
}

interface EmiApplicationItem {
	id: number | string;
	application_id: string,
	bank: string;
	status: string;
	created_at: string;
	path: string;
}

function handleApprove(item: EmiApplicationItem): void {

	modal.open(
		EmiApprovalForm,
		{
			id: props.data?.id,
			data: {
				...item,
				email: props.data?.email ?? props.data?.user?.email ?? '',
			},
		},
		{
			title: `Approve - ${item.bank}`,
			size: 'lg',
			onSaved: () => {
				fetchApplications();
			},
		},
	);
}


watch(
	() => props.data?.id,
	() => {
		fetchApplications();
	},
	{ immediate: true },
);

onMounted(() => {
	fetchApplications();
});
</script>

<style scoped>
.application-date-cell {
	font-size: 0.8rem;
	color: rgba(var(--v-theme-on-surface), 0.62);
}
</style>
