<template>
	<AppPageHeader title="EMI Request Detail" subtitle="View EMI request information">
		<template #actions>
			<v-btn variant="tonal" color="primary" @click="goBack">
				<v-icon start>mdi-arrow-left</v-icon>
				Back
			</v-btn>
		</template>
	</AppPageHeader>

	<v-card class="pa-6">
		<div class="top-grid">
			<div class="thumb-cell">
				<v-avatar size="112" rounded="lg" color="grey-lighten-3">
					<v-img v-if="productThumb" :src="productThumb" contain />
					<v-icon v-else size="32" color="grey-darken-1">mdi-package-variant-closed</v-icon>
				</v-avatar>
			</div>
			<div>
				<div class="text-h6">{{ productName }}</div>
				<div class="text-body-2 text-medium-emphasis mt-2">Price: {{ displayProductPrice }}</div>
				<div class="text-body-2 text-medium-emphasis mt-1">EMI Per Month: {{ displayEmiPerMonth }}</div>
				<div class="text-body-2 text-medium-emphasis mt-1">Requested By: {{ requestedBy }}</div>
				<div class="mt-3">
					<v-chip size="small" variant="tonal" :color="statusChipColor">
						{{ application.status || 'N/A' }}
					</v-chip>
				</div>
			</div>
		</div>

		<div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading EMI request detail...</div>
	</v-card>

	<v-card class="mt-4">
		<v-tabs v-model="tab" density="comfortable" show-arrows color="primary">
			<v-tab v-for="item in tabs" :key="item.value" :value="item.value">
				<v-icon start size="16">{{ item.icon }}</v-icon>
				{{ item.label }}
			</v-tab>
		</v-tabs>
		<v-divider />
		<v-window v-model="tab">
			<v-window-item v-for="item in tabs" :key="item.value" :value="item.value">
				<component :is="tabComponents[item.value]" :data="application" />
			</v-window-item>
		</v-window>
	</v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { get as getEmiRequest } from '@/api/emi-requests.api';
import { formatNPR } from '@/shared/formatters';
import TabEmiInfo from '@/components/emi/TabEmiInfo.vue';
import TabRequestedBy from '@/components/emi/TabRequestedBy.vue';
import TabEmploymentInfo from '@/components/emi/TabEmploymentInfo.vue';
import TabCardInfo from '@/components/emi/TabCardInfo.vue';
import TabGuarantorInfo from '@/components/emi/TabGuarantorInfo.vue';
import TabVehicleInfo from '@/components/emi/TabVehicleInfo.vue';
import TabDocuments from '@/components/emi/TabDocuments.vue';
import TabMerchant from '@/components/emi/TabMerchant.vue';
import EmiBankApplicationList from '@/components/emi/EmiBankApplicationList.vue';

const route = useRoute();
const router = useRouter();
const tabComponents = {
	emi: TabEmiInfo,
	application: EmiBankApplicationList,
	requestedBy: TabRequestedBy,
	employment: TabEmploymentInfo,
	card: TabCardInfo,
	guarantor: TabGuarantorInfo,
	vehicle: TabVehicleInfo,
	documents: TabDocuments,
	merchant: TabMerchant,
} as const;

type TabKey = keyof typeof tabComponents;
const tab = ref<TabKey>('emi');

const tabs: Array<{ value: TabKey; label: string; icon: string }> = [
	{ value: 'emi', label: 'EMI Info', icon: 'mdi-cash' },
	{ value: 'application', label: 'Application', icon: 'mdi-file-document-multiple-outline' },
	{ value: 'requestedBy', label: 'Requested By', icon: 'mdi-account-outline' },
	{ value: 'guarantor', label: 'Guarantor Info', icon: 'mdi-account-tie-outline' },
	{ value: 'documents', label: 'Documents', icon: 'mdi-file-document-outline' },
	{ value: 'card', label: 'Card Info', icon: 'mdi-credit-card-outline' },
	{ value: 'employment', label: 'Employment Info', icon: 'mdi-briefcase-outline' },
	{ value: 'vehicle', label: 'Vehicle Info', icon: 'mdi-motorbike' },
	// { value: 'merchant', label: 'Merchant Info', icon: 'mdi-account' },
];

interface ApplicationUser {
	name?: string;
	email?: string;
	phone?: string;
	mobile?: string;
	avatar?: string;
}

interface ApplicationProduct {
	name?: string;
	price?: number | string;
	thumb?: string;
}

interface Application {
	user?: ApplicationUser;
	product?: ApplicationProduct;
	name?: string;
	product_price?: number | string;
	emi_per_month?: number | string;
	product_attributes?: unknown;
	status?: string;
}

const application = ref<Application>({
	user: {
		name: '',
		email: '',
		phone: '',
		avatar: '',
	},
	product: {
		name: '',
		price: '',
		thumb: '',
	},
	status: '',
});
const loading = ref(false);

const statusChipColor = computed(() => {
	const status = String(application.value.status ?? '').toLowerCase();
	if (status === 'approved') return 'success';
	if (status === 'pending') return 'warning';
	if (status === 'processing') return 'info';
	return 'secondary';
});

const productName = computed(() => String(application.value.product?.name ?? '').trim() || 'N/A');

const requestedBy = computed(() => String(application.value.user?.name ?? application.value.name ?? '').trim() || 'N/A');

const displayProductPrice = computed(() => {
	const amount = Number(application.value.product?.price ?? application.value.product_price ?? 0);
	return amount > 0 ? formatNPR(amount) : 'N/A';
});

const displayEmiPerMonth = computed(() => {
	const amount = Number(application.value.emi_per_month ?? 0);
	return amount > 0 ? formatNPR(amount) : 'N/A';
});

const productThumb = computed(() => {
	const directThumb = String(application.value.product?.thumb ?? '').trim();
	if (directThumb) return directThumb;

	const attrs = application.value.product_attributes;
	if (attrs && typeof attrs === 'object' && !Array.isArray(attrs)) {
		const record = attrs as Record<string, unknown>;
		const fallback = String(record.thumb ?? record.image ?? record.image_url ?? '').trim();
		if (fallback) return fallback;
	}

	return '';
});

async function fetchDetail() {
	const id = String(route.params.id ?? '');
	if (!id) return;
	loading.value = true;
	try {
		const detail = await getEmiRequest(id);
		application.value = {
			...application.value,
			...detail,
		} as any;
	} catch {
		// keep demo data on error
	} finally {
		loading.value = false;
	}
}

function goBack() {
	router.back();
}

onMounted(fetchDetail);
</script>

<style scoped>
.top-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 16px;
	align-items: center;
}

.thumb-cell {
	display: flex;
	justify-content: center;
}

@media (min-width: 960px) {
	.top-grid {
		grid-template-columns: 128px minmax(0, 1fr);
		gap: 20px;
	}

	.thumb-cell {
		justify-content: flex-start;
	}
}
</style>
