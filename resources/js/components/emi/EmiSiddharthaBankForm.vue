<template>
	<v-form @submit.prevent>
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
			<v-col cols="12" class="py-1">
				<v-text-field v-model="form.merchant_name_address" label="Merchant name and address"
					density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.item_name" label="Name of the item" density="comfortable" variant="outlined"
					:rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.manufactured_by" label="Manufactured by" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.model_name" label="Model No./Name" density="comfortable" variant="outlined"
					:disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.serial_no" label="Serial No." density="comfortable" variant="outlined"
					:disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.emi_amount" label="EMI Loan amount (NPR)" density="comfortable"
					variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.amount_in_words" label="Amount in words" density="comfortable"
					variant="outlined" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
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
				<v-btn color="primary" :loading="loading" @click="handleSubmit()">
					<v-icon>mdi-creation</v-icon>
					Generate
				</v-btn>
			</v-col>
		</v-row>
	</v-form>
</template>

<script setup>
import { ref } from 'vue'
const loading = ref(false)
const props = defineProps({
	form: {
		type: Object,
		required: true,
	},
	application: {
		type: Object,
		required: true,
	},
	rules: {
		type: Object,
		required: true,
	},
	loading: {
		type: Boolean,
		required: true,
	},
	emiTenureOptions: {
		type: Array,
		required: true,
	},
	signaturePreview: {
		type: String,
		default: '',
	},
	stampPreview: {
		type: String,
		default: '',
	},
})

async function handleSubmit() {
	console.log('Submitting Siddhartha Bank Form', props.form)
	console.log('Application:', props.application);

	// const resp = await axios.post(`/emi-applications/${props.application.id}/generate-pdf`, props.form);

	// console.log({resp});
}
</script>
