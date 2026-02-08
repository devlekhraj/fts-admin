<template>
	<v-form @submit.prevent>
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
				<v-text-field v-model="form.loan_amount" label="Amount of Nabil Installment Loan (Rs.)"
					density="comfortable" variant="outlined" :rules="[rules.required]" :disabled="loading" />
			</v-col>
			<v-col cols="12" md="6" class="py-1">
				<v-text-field v-model="form.amount_in_words" label="Amount in Words" density="comfortable"
					variant="outlined" :disabled="loading" />
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
const emit = defineEmits(['submit'])

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
})

function handleSubmit() {
	console.log('Submitting Nabil Bank Form', props.form)

	console.log('Application:', props.application);
	// emit('submit', { ...props.form })
}

</script>
