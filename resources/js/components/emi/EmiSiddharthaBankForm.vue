<template>
	<v-form ref="formRef" @submit.prevent="handleSubmit">
		<v-row>
			<v-col cols="12">
				<div class="text-subtitle-2 text-primary">Cardholder Details</div>
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.cardholder_name" label="Name of the cardholder" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.address" label="Address" density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.mobile" label="Mobile No." density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" v-maska="'(###) ###-####'" />
			</v-col>
			<!-- <v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.telephone" label="Telephone No." density="comfortable" variant="outlined"
					:disabled="loading" v-maska="'(###) ###-####'" />
			</v-col> -->
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.email" label="Email" density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.card_number" label="SBL Credit Card No." density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" v-maska="'#### #### #### ####'" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.expiry_date" label="Expiry Date (mm/yy)" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" v-maska="'##/##'" />
			</v-col>

			<v-col cols="12">
				<div class="text-subtitle-2 text-primary">Item Details</div>
			</v-col>
			<!-- <v-col cols="12" class="py-1">
				<v-text-field v-model="form.merchant_name_address" label="Merchant name and address"
					density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col> -->
			<v-col cols="12" md="12" class="py-1">
				<v-text-field v-model="form.item_name" label="Name of the item" density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="4" class="py-1">
				<v-text-field v-model="form.manufactured_by" label="Manufactured by" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="4" class="py-1">
				<v-text-field v-model="form.model_name" label="Model No./Name" density="comfortable" variant="outlined"
					:disabled="loading" />
			</v-col>
			<v-col cols="12" md="4" class="py-1">
				<v-text-field v-model="form.serial_no" label="Serial No." density="comfortable" variant="outlined"
					:disabled="loading" />
			</v-col>
			<v-col cols="12" md="4" class="py-1">
				<v-text-field v-model="form.emi_amount" label="EMI Loan amount (NPR)" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="8" class="py-1">
				<v-text-field v-model="form.amount_in_words" label="Amount in words" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="4" class="py-1">
				<v-select v-model="form.emi_tenure" :items="emiTenureOptions" label="EMI tenure (months)"
					density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>

			<v-col cols="12">
				<div class="text-subtitle-2 text-primary">Merchant Details</div>
			</v-col>
			<v-col cols="12" md="12" class="py-1">
				<v-text-field v-model="form.merchant_name" label="Name of merchant" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.requested_by" label="Requested by" density="comfortable" variant="outlined"
					:disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.requested_phone" label="Phone no." density="comfortable" variant="outlined"
					:disabled="loading" v-maska="'########## / ##########'" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-file-input v-model="form.signature_file" prepend-inner-icon="mdi-image" prepend-icon=""
					label="Signature" density="comfortable" variant="outlined" accept="image/*" :disabled="loading" />
				<v-img v-if="signaturePreview" :src="signaturePreview" max-height="140" class="mt-2" contain />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-file-input v-model="form.stamp_file" prepend-inner-icon="mdi-image" prepend-icon="" label="Stamp"
					density="comfortable" variant="outlined" accept="image/*" :disabled="loading" />
				<v-img v-if="stampPreview" :src="stampPreview" max-height="140" class="mt-2" contain />
			</v-col>
		</v-row>
		<v-row>
			<v-col cols="12" class="d-flex justify-space-around">
				<v-btn color="primary" :loading="loading" type="submit">
					<v-icon>mdi-creation</v-icon>
					Generate
				</v-btn>
			</v-col>
		</v-row>
	</v-form>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { useModalStore } from '@/stores/modal.store';
import { generateApplication } from '@/api/emi-requests.api';

const props = defineProps<{ data?: Record<string, any> }>();

const formRef = ref();
const loading = ref(false);
const modal = useModalStore();
const rules = {
	required: (v: string) => Boolean(v) || 'Required',
};

const emiTenureOptions = ['3', '6', '9', '12', '18', '24'];

const form = reactive({
	bank_code: '',
	cardholder_name: '',
	address: '',
	mobile: '',
	email: '',
	card_number: '',
	expiry_date: '',
	item_name: '',
	manufactured_by: '',
	model_name: '',
	serial_no: '',
	emi_amount: '',
	amount_in_words: '',
	emi_tenure: '',
	merchant_name: '',
	requested_by: '',
	requested_phone: '',
	signature_file: null as File | null,
	stamp_file: null as File | null,
});

const signaturePreview = ref<string | null>(null);
const stampPreview = ref<string | null>(null);
let signatureUrl: string | null = null;
let stampUrl: string | null = null;

watch(
	() => form.signature_file,
	(file) => {
		if (signatureUrl) {
			URL.revokeObjectURL(signatureUrl);
			signatureUrl = null;
		}
		if (file instanceof File) {
			signatureUrl = URL.createObjectURL(file);
			signaturePreview.value = signatureUrl;
		} else {
			signaturePreview.value = null;
		}
	},
);

watch(
	() => form.stamp_file,
	(file) => {
		if (stampUrl) {
			URL.revokeObjectURL(stampUrl);
			stampUrl = null;
		}
		if (file instanceof File) {
			stampUrl = URL.createObjectURL(file);
			stampPreview.value = stampUrl;
		} else {
			stampPreview.value = null;
		}
	},
);

watch(
	() => props.data,
	(data) => {
		if (!data) return;
		form.bank_code = data.bank_code ?? '';
		form.cardholder_name = data?.user?.name?? '';
		form.address = data.address ?? '';
		form.mobile = data?.user?.mobile ?? '';
		form.email = data.email ?? '';
		form.card_number = data.card_number ?? '';
		form.expiry_date = data.card_expiry_date ?? '';
		form.item_name = data.product?.name ?? '';
		form.manufactured_by = '';
		form.model_name = '';
		form.serial_no = '';
		form.emi_amount = data.finance_amount ?? data.product_price ?? '';
		form.amount_in_words = '';
		form.emi_tenure = data.emi_mode ? String(data.emi_mode) : '';
		
	},
	{ immediate: true },
);

async function handleSubmit() {
	const { valid } = await formRef.value?.validate();
	if (!valid) return;
	const requestId = String(props.data?.id ?? '');
	if (!requestId) return;

	loading.value = true;
	try {
		const payload = new FormData();
		Object.entries(form).forEach(([key, value]) => {
			if (value === null || value === undefined || value === '') return;
			if (value instanceof File) {
				payload.append(key, value);
				return;
			}
			payload.append(key, String(value));
		});

		console.log('Payload for generating application:', Object.fromEntries(payload.entries()));
		const { data } = await generateApplication(requestId, payload);
		console.log(data?.message ?? 'Application generated');
		console.log('Generated file path:', data?.path ?? '');
		modal.onSaved?.(data);
		modal.close();
	} finally {
		loading.value = false;
	}
}
</script>
