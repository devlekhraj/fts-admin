<template>
	<v-card flat>
		<v-card-title class="d-flex justify-space-between align-center py-1">
			<span class="font-semibold">Bank Application Form</span>
			<v-spacer></v-spacer>
			<v-btn icon="mdi-close" size="small" color="error" class="cursor-pointer" variant="text" aria-label="Close" :disabled="loading" @click="handleCancel">
				<v-icon>mdi-close</v-icon>
			</v-btn>
		</v-card-title>

		<v-divider></v-divider>

		<v-card-text>
			<v-tabs v-model="activeTab" class="mb-4" align-tabs="center" transition="fade-transition" mode="out-in"
				color="primary" density="comfortable">
				<v-tab value="nabil" class="tab-with-logo">

					<span>Nabil Bank</span>
				</v-tab>
				<v-tab value="siddhartha" class="tab-with-logo">

					<span>Siddhartha Bank</span>
				</v-tab>
			</v-tabs>

			<v-window v-model="activeTab">
				<v-window-item value="nabil">
					<keep-alive>
						<NabilBankForm v-if="activeTab === 'nabil'" :form="nabilForm" :rules="rules" :loading="loading"
							:application="application" :emi-tenure-options="emiTenureOptions"
							:signature-preview="nabilSignaturePreview" />
					</keep-alive>
				</v-window-item>
				<v-window-item value="siddhartha">
					<keep-alive>
						<SiddharthaBankForm v-if="activeTab === 'siddhartha'" :form="siddharthaForm" :rules="rules"
							:loading="loading" :emi-tenure-options="emiTenureOptions" :application="application"
							:signature-preview="signaturePreview" :stamp-preview="stampPreview" />
					</keep-alive>
				</v-window-item>
			</v-window>
		</v-card-text>
	</v-card>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import axios from 'axios'
import { useSnackbar } from '@/composables/snackbar'
import NabilBankForm from './EmiNabilBankForm.vue'
import SiddharthaBankForm from './EmiSiddharthaBankForm.vue'

/* ---------------- Props & Emits ---------------- */
const props = defineProps({
	application: {
		type: Object,
		default: () => ({}),
	},
})
const emit = defineEmits(['close', 'saved'])

/* ---------------- State ---------------- */
const loading = ref(false)

const activeTab = ref('nabil')

const form = reactive({
	id: null,
	title: '',
	sub_title: '',
	slug: '',
})

const nabilForm = reactive({
	cardholder_name: 'Emery Collins',
	card_number: '1253 2424 2424 2424',
	expiry_date: '09/26',
	telephone: '',
	mobile: '(262) 653-1699',
	merchant_name_address: 'Fatafat Sewa Pvt. Ltd, Sitapaila, Kathmandu',
	item_name: 'Iphone 11 Pro Max',
	manufactured_by: 'Iphone 11 Pro Max',
	model_name: 'Iphone 11 Pro Max',
	serial_no: 'Iphone 11 Pro Max',
	loan_amount: '50,0000',
	amount_in_words: 'twelve lakh and four hundred fifty six',
	tenure: 6,
	signature_file: null,
	bank_code: 'nbl',
})

const siddharthaForm = reactive({
	cardholder_name: '',
	card_number: '4 4 5 3 4 5 3 4',
	expiry_date: '09/26',
	address: 'Kathmandu',
	telephone: '',
	mobile: '5507542069',
	email: 'data31087@gmail.com',
	merchant_name_address: 'Fatafat Sewa Pvt. Ltd, Sitapaila, Kathmandu',
	item_name: 'iPhone XR',
	manufactured_by: 'APPLE',
	model_name: 'n/a',
	serial_no: 'n/a',
	emi_amount: '0',
	amount_in_words: '-',
	emi_tenure: 6,
	merchant_name: 'Fatafat Sewa Pvt. Ltd, Sitapaila, Kathmandu',
	requested_by: 'Jiban Kumar Shrestha',
	requested_phone: '9813001000 / 9828757575',
	signature_file: null,
	stamp_file: null,
	bank_code: 'sbl',
})

const signaturePreview = ref('')
const stampPreview = ref('')
const nabilSignaturePreview = ref('')

const getFileUrl = (value) => {
	const file = Array.isArray(value) ? value[0] : value
	return file ? URL.createObjectURL(file) : ''
}

const revokeUrl = (url) => {
	if (url) URL.revokeObjectURL(url)
}

watch(
	() => siddharthaForm.signature_file,
	(next, prev) => {
		revokeUrl(signaturePreview.value)
		signaturePreview.value = getFileUrl(next)
	}
)

watch(
	() => siddharthaForm.stamp_file,
	(next, prev) => {
		revokeUrl(stampPreview.value)
		stampPreview.value = getFileUrl(next)
	}
)

watch(
	() => nabilForm.signature_file,
	(next, prev) => {
		revokeUrl(nabilSignaturePreview.value)
		nabilSignaturePreview.value = getFileUrl(next)
	}
)

onBeforeUnmount(() => {
	revokeUrl(signaturePreview.value)
	revokeUrl(stampPreview.value)
	revokeUrl(nabilSignaturePreview.value)
})

const serverErrors = reactive({
	title: null,
	sub_title: null,
	slug: null,
})

const slugEdited = ref(false)
const isEditing = computed(() => !!form.id)

const { showSuccess, showError } = useSnackbar()

/* ---------------- Utils ---------------- */
function slugify(text) {
	return text
		.toLowerCase()
		.trim()
		.replace(/[\s_]+/g, '-')       // Replace spaces/underscores with -
		.replace(/[^\w\-]+/g, '')      // Remove non-word chars
		.replace(/\-\-+/g, '-')        // Collapse multiple -
		.replace(/^-+|-+$/g, '')       // Trim start/end -
}

/* Auto-generate slug if user has not edited */
watch(() => form.title, (newtitle) => {
	if (!slugEdited.value && newtitle) {
		form.slug = slugify(newtitle)
	}
})

function onSlugInput() {
	slugEdited.value = true
}

// function resetSlug() {
// 	form.slug = slugify(form.title)
// 	slugEdited.value = false
// }

/* ---------------- Validation Rules ---------------- */
const rules = {
	required: v => !!v || 'This field is required',
	slug: v =>
		!v || /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(v) ||
		'Only lowercase letters, numbers, and hyphens are allowed',
}

const emiTenureOptions = [6, 9, 12, 18]

/* ---------------- Actions ---------------- */
// function resetForm() {
// 	Object.assign(form, { id: null, title: '', slug: '' })
// 	Object.keys(serverErrors).forEach(key => (serverErrors[key] = null))
// 	slugEdited.value = false
// }

function handleCancel() {
	// formRef.value?.reset()
	// resetForm()
	emit('close')
}

onMounted(() => {
	if (props.item?.id) {
		Object.assign(form, props.item)
		slugEdited.value = !!props.item.slug
	}
})

async function submitForm() {
	Object.keys(serverErrors).forEach(key => (serverErrors[key] = null))
	if (loading.value) return
	await handleSubmit()
}

async function handleSubmit() {
	try {
		loading.value = true

		const method = form.id ? 'PUT' : 'POST'
		const url = form.id
			? `/admin/product-categories/${form.id}`
			: '/admin/product-categories'

		// const resp = await axios({ method, url, data: form })
		const resp = await axios({
			method: 'POST',
			url: '/product-categories',
			data: {
				id: form.id,
				title: form.title,
				sub_title: form.sub_title,
				slug: form.slug,
			},
		});
		console.log(resp);

		showSuccess(resp?.message || (form.id ? 'Category updated' : 'Category created'))
		// emit('saved',, resp.data) // 🔹 emit saved data
		emit('close')
		// resetForm()

	} catch (error) {
		if (error.response?.status === 422) {
			const errors = error.response.data?.errors || {}
			Object.keys(errors).forEach(key => {
				serverErrors[key] = errors[key]
			})
		} else {
			showError(error?.response?.data?.message || 'An error occurred')
		}
		console.error('Category save failed', error)
	} finally {
		loading.value = false
	}
}
</script>

<style scoped></style>
