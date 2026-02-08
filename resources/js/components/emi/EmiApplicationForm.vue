<template>
	<v-card flat>
		<v-card-title class="d-flex justify-space-between align-center py-1">
			<span class="font-semibold">Bank Application Form</span>
			<v-spacer></v-spacer>
			<v-btn icon="mdi-close" size="small" color="error" class="cursor-pointer" variant="text" aria-label="Close"
				@click="$emit('close')">
				<v-icon>mdi-close</v-icon>
			</v-btn>
		</v-card-title>

		<v-divider></v-divider>

		<v-card-text>
			<div class="d-flex flex-column">
					<div class="text-subtitle-2 text-primary py-1">Preferred Bank</div>
					<div class="text-caption text-medium-emphasis mb-2">
						Select the bank where you want to generate the EMI application.
					</div>
					<div style="max-width: 400px;">
						<v-select
							v-model="activeBankCode"
							:items="bankOptions"
							item-title="name"
							item-value="code"
							label="Select Bank"
							placeholder="Select a bank"
							persistent-placeholder
							variant="outlined"
							density="comfortable"
							:loading="banksLoading"
						/>
					</div>
			</div>
			<div class="mt-4">
				<component v-if="activeFormComponent" :is="activeFormComponent" :data="applicationData" />
				<div v-else class="text-center text-medium-emphasis py-6">
					<span>{{ emptyStatePrefix }}</span>
					<span class="text-error font-weight-semibold">{{ selectedBankName }}</span>
					<span>{{ emptyStateSuffix }}</span>
				</div>
			</div>
		</v-card-text>

	</v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch, withDefaults } from 'vue';
import EmiNabilBankForm from './EmiNabilBankForm.vue';
import EmiSiddharthaBankForm from './EmiSiddharthaBankForm.vue';
import { list as listEmiBanks } from '@/api/emi-banks.api';
import { get as getEmiApplication } from '@/api/emi-applications.api';

const props = withDefaults(
	defineProps<{ id?: string | number; data?: Record<string, any> | null }>(),
	{ data: null },
);

const applicationData = ref<Record<string, any> | null>(props.data);

type BankOption = { name: string; code: string };
const bankOptions = ref<BankOption[]>([]);
const banksLoading = ref(false);
const activeBankCode = ref<string | null>(null);

const activeFormComponent = computed(() => {
	switch (activeBankCode.value?.toLocaleLowerCase()) {
		case 'sbl':
			return EmiSiddharthaBankForm;
		case 'nabil':
			return EmiNabilBankForm;
		default:
			return null;
	}
});

const selectedBankName = computed(() => {
	if (!activeBankCode.value) return '';
	return (
		bankOptions.value.find((bank) => bank.code === activeBankCode.value)?.name ??
		activeBankCode.value
	);
});

const emptyStatePrefix = computed(() => {
	if (!activeBankCode.value) {
		return 'Please choose a bank above to generate the application form.';
	}
	return 'The form for "';
});

const emptyStateSuffix = computed(() => {
	if (!activeBankCode.value) return '';
	return '" is not available yet.';
});

async function fetchBanks() {
	banksLoading.value = true;
	try {
		const { data } = await listEmiBanks();
		bankOptions.value = data?.data ?? [];
	} finally {
		banksLoading.value = false;
	}
}

async function fetchApplication() {
	if (!props.id || applicationData.value) return;
	try {
		const { data } = await getEmiApplication(String(props.id));
		applicationData.value = data?.data ?? data ?? null;
	} finally {
	}
}

watch(
	() => props.data,
	(data) => {
		if (data) applicationData.value = data;
	},
);

onMounted(() => {
	fetchBanks();
	fetchApplication();
});
</script>
