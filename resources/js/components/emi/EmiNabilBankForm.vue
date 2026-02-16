<template>
	<v-form ref="formRef" @submit.prevent="handleSubmit">
		<v-row>
			<v-col cols="12">
				<div class="text-subtitle-2 text-primary">Cardholder Details</div>
			</v-col>
			<v-col cols="12" class="py-1">
				<v-text-field v-model="form.cardholder_name" label="Name of the cardholder" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.card_number" label="Nabil Credit Card Number" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" v-maska="'#### #### #### ####'" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.expiry_date" label="Expiry Date (mm/yy)" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" v-maska="'##/##'" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.mobile" label="Mobile Number" density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" v-maska="'(###) ###-####'" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.telephone" label="Telephone Number" density="comfortable" variant="outlined"
					:disabled="loading" v-maska="'(###) ###-####'" />
			</v-col>



			<v-col cols="12">
				<div class="text-subtitle-2 text-primary">Item Details</div>
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.item_name" label="Name of the Item" density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.manufactured_by" label="Manufactured By" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.model_name" label="Model Number/Name" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.serial_no" label="Serial Number" density="comfortable" variant="outlined"
					:disabled="loading" />
			</v-col>

			<v-col cols="12" md="6" class="py-1">
				<v-select v-model="form.tenure" :items="emiTenureOptions" label="Installment tenure (Months)"
				density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			
			<v-col cols="12" md="6" class="py-1">
				<v-file-input v-model="form.signature_file" prepend-icon="" prepend-inner-icon="mdi-image" label="Signature of Cardholder" density="comfortable"
				variant="outlined" accept="image/*" :disabled="loading" />
				<v-img v-if="signaturePreview" :src="signaturePreview" max-height="140" class="mt-2" contain />
			</v-col>
			<v-col cols="12" md="3" class="py-1">
				<v-text-field v-model="form.installment_amount" label="Installment Amount"
					density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="9" class="py-1">
				<v-text-field v-model="form.amount_in_words" label="Amount in Words" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>

			<v-col cols="12">
				<div class="text-subtitle-2 text-primary">Merchant Details</div>
			</v-col>
			<v-col cols="12" class="py-1">
				<v-text-field v-model="form.merchant_name_address" label="Name and address of the Merchant"
					density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
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
	bank_code:'',
	cardholder_name: '',
	card_number: '',
	expiry_date: '',
	mobile: '',
	telephone: '',
	item_name: '',
	manufactured_by: '',
	model_name: '',
	serial_no: '',
	installment_amount: '',
	amount_in_words: '',
	tenure: '',
	signature_file: null as File | null,
	merchant_name_address: '',
});

const signaturePreview = ref<string | null>(null);
let signatureUrl: string | null = null;

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
	() => props.data,
	(data) => {
		if (!data) return;
		form.bank_code = data.bank_code ?? '';
		form.cardholder_name = data?.user?.name?? '';
		form.card_number = data.card_number ?? '';
		form.expiry_date = data.card_expiry_date ?? '';
		form.mobile = data?.user?.mobile ?? '';
		form.telephone = '';
		form.item_name = data.product?.name ?? '';
		form.manufactured_by = '';
		form.model_name = '';
		form.serial_no = '';
		form.installment_amount = data.finance_amount ?? data.product_price ?? '';
		form.amount_in_words = '';
		form.tenure = data.emi_mode ? String(data.emi_mode) : '';
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
