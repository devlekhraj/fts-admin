<template>
	<v-container fluid class="pa-0">
		<div class="d-flex align-center justify-space-between mb-4">
			<v-btn variant="text" @click="goBack">
				<v-icon start>mdi-arrow-left</v-icon>
				Back
			</v-btn>
		</div>

		<v-card class="pa-4 mb-4">
			<div class="d-flex align-center gap-4">
				<v-avatar size="64" rounded>
					<v-img v-if="application.user?.avatar" :src="application?.user?.avatar" alt="User" />
					<v-img v-else src="https://placehold.co/64" alt="User" />
					<!-- <v-icon v-else>mdi-account</v-icon> -->
				</v-avatar>
				<div class="ml-4">
					<div class="text-subtitle-1 font-weight-semibold">{{ application.user?.name ?? 'N/A' }}</div>
					<div class="text-caption text-medium-emphasis">{{ application.user?.email ?? 'N/A' }}</div>
					<div class="text-caption text-medium-emphasis">{{ application.user?.phone ?? 'N/A' }}</div>
				</div>
				<v-spacer />
				<v-chip size="small" variant="tonal" :color="statusChipColor">{{ application.status }}</v-chip>
			</div>
		</v-card>

		<v-card>
			<v-tabs v-model="tab" density="comfortable" show-arrows color="primary">
				<v-tab v-for="item in tabs" :key="item.value" :value="item.value">
					<v-icon start size="16">{{ item.icon }}</v-icon>
					{{ item.label }}
				</v-tab>
			</v-tabs>
		</v-card>
		<v-divider class="mt-0"></v-divider>

		<v-window v-model="tab">
			<v-window-item :value="tab">
				<component :is="activeComponent" :data="application" />
			</v-window-item>
		</v-window>
	</v-container>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { get as getEmiRequest } from '@/api/emi-requests.api';
import TabEmiInfo from '@/components/emi/TabEmiInfo.vue';
import TabEmploymentInfo from '@/components/emi/TabEmploymentInfo.vue';
import TabCardInfo from '@/components/emi/TabCardInfo.vue';
import TabGuarantorInfo from '@/components/emi/TabGuarantorInfo.vue';
import TabVehicleInfo from '@/components/emi/TabVehicleInfo.vue';
import TabDocuments from '@/components/emi/TabDocuments.vue';
import TabMerchant from '@/components/emi/TabMerchant.vue';

const route = useRoute();
const router = useRouter();
const tabComponents = {
	emi: TabEmiInfo,
	employment: TabEmploymentInfo,
	card: TabCardInfo,
	guarantor: TabGuarantorInfo,
	vehicle: TabVehicleInfo,
	documents: TabDocuments,
	merchant: TabMerchant,
} as const;

type TabKey = keyof typeof tabComponents;
const tab = ref<TabKey>('emi');

const tabs = [
	{ value: 'emi', label: 'EMI Info', icon: 'mdi-cash' },
	{ value: 'documents', label: 'Documents', icon: 'mdi-file-document-outline' },
	{ value: 'card', label: 'Card Info', icon: 'mdi-credit-card-outline' },
	{ value: 'employment', label: 'Employment Info', icon: 'mdi-briefcase-outline' },
	{ value: 'guarantor', label: 'Guarantor Info', icon: 'mdi-account-tie-outline' },
	{ value: 'vehicle', label: 'Vehicle Info', icon: 'mdi-motorbike' },
	// { value: 'merchant', label: 'Merchant Info', icon: 'mdi-account' },
];

const activeComponent = computed(() => tabComponents[tab.value] ?? TabEmiInfo);

interface ApplicationUser {
	name?: string;
	email?: string;
	phone?: string;
	avatar?: string;
}

interface Application {
	user?: ApplicationUser;
	status?: string;
	// Add other properties as needed
}

const application = ref<Application>({
	user: {
		name: '',
		email: '',
		phone: '',
		avatar: '',
	},
	status: '',
});

const statusChipColor = computed(() => {
	const status = String(application.value.status ?? '').toLowerCase();
	if (status === 'approved') return 'success';
	if (status === 'pending') return 'warning';
	if (status === 'processing') return 'info';
	return 'secondary';
});

async function fetchDetail() {
	const id = String(route.params.id ?? '');
	if (!id) return;
	try {
		const { data } = await getEmiRequest(id);
		application.value = {
			...application.value,
			...data,
		} as any;
	} catch {
		// keep demo data on error
	}
}

function goBack() {
	router.back();
}

onMounted(fetchDetail);
</script>
