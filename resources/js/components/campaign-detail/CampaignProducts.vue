<template>
	<div>
		<!-- Products Table -->
		<v-data-table :headers="headers" :items="data_list || []" class="" density="comfortable" hide-default-footer
			:items-per-page="pagination.per_page">

			<!-- Top slot -->
			<template #top>
				<v-row>
					<v-col cols="12" md="4">
						<div>
							<v-btn color="primary" @click="openModal()">
								<v-icon>mdi-plus</v-icon>
								Assign Products
							</v-btn>
						</div>
					</v-col>
					<v-col cols="12" md="4">
						<div class="d-flex align-center">
							<v-text-field label="Search" v-model="search" density="compact" variant="outlined"
								hide-details></v-text-field>
								<div class="ps-2">
									<v-btn color="primary" variant="outlined" @click="handleSearch()"><v-icon>mdi-magnify</v-icon> Search</v-btn>
								</div>
						</div>
					</v-col>
					<v-col cols="12" md="4">
						<div class="text-right">
							<v-btn variant="outlined" color="primary" @click="updateDiscount()">
								<v-icon start>mdi-percent</v-icon>
								Update Discount To All Item
							</v-btn>
						</div>
					</v-col>
				</v-row>
			</template>

			<!-- Bottom slot -->
			<template #bottom v-if="pagination.total > pagination.per_page">
				<div class="w-100 d-flex justify-space-around pa-4 align-center">
					<div class="d-flex">
						<div class="d-flex align-center">
							<span class="mr-2">Items per page:</span>
							<v-select v-model="pagination.per_page" :items="[10, 20, 30, 50, 100]" hide-details
								variant="outlined" density="compact" style="width: 80px"
								@update:model-value="fetchData()"></v-select>
						</div>

						<div class="d-flex align-center">
							<v-btn :disabled="pagination.current_page <= 1" @click="goToPreviousPage" class="mx-1"
								variant="tonal" color="primary">
								Previous
							</v-btn>

							<span class="mx-2 align-self-center">
								Page {{ pagination.current_page }} of {{ pagination.last_page }}
							</span>

							<v-btn :disabled="pagination.current_page >= pagination.last_page" @click="goToNextPage"
								class="mx-1" variant="tonal" color="primary">
								Next
							</v-btn>
						</div>
					</div>
				</div>
			</template>

			<template #item.name="{ item }">
				<div class="d-flex align-center">
					<div class="pa-2">
						<v-img :src="item.thumb?.url || undefined" alt="" height="60" width="60"
							class="me-2"></v-img>
					</div>
					<div>
						<p class="mb-1" style="font-weight: 500;">{{ item.name }}</p>
					</div>
				</div>
			</template>
			<template #item.price="{ item }">
				<div style="width: max-content;">
					<p class="text-decoration-line-through text-muted">{{ formatAmount(item.price?.original_price) }}</p>
					<p class="text-primary" style="font-weight: 600;">{{ formatAmount(item.price?.discounted_price) }}</p>
				</div>
			</template>
			<template #item.discount="{ item }">
				<div style="width: max-content;">
					<v-chip variant="tonal" size="small" color="primary"
						class="px-4">
						<v-icon size="16" start>
							{{ item.discount?.type === 'percentage' ? 'mdi-percent' : 'mdi-cash' }}
						</v-icon>
						{{ item.discount?.type === 'percentage' ? item.discount?.value + '%' : item.discount?.type }}
					</v-chip>
				</div>
			</template>
			<template #item.actions="{ item }">
				<div style="width: max-content;">
					<v-btn icon size="x-small" color="warning" variant="tonal" class="ml-2" @click="updateItem(item)">
						<v-icon>mdi-pencil</v-icon>
					</v-btn>
					<v-btn icon size="x-small" color="error" variant="tonal" class="ml-2" @click="deleteModal(item)">
						<v-icon>mdi-delete</v-icon>
					</v-btn>
				</div>
			</template>
		</v-data-table>

	</div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { campaignProducts } from '@/api/campaigns.api';
import { formatAmount } from '@/shared/utils'
import { Campaign, CampaignProductListItem } from '@/types/models';
import { useModalStore } from '@/stores/modal.store';
import CampaignProductForm from './modal/CampaignProductForm.vue';
// import CampaignProductEditForm from './modal/CampaignProductEditForm.vue';
// import CampaignDiscountForm from './modal/CampaignDiscountForm.vue';
// import CampaignProductDelete from './modal/CampaignProductDelete.vue';

const modal = useModalStore();

// const globalModal = ref(null)
const data_list = ref<CampaignProductListItem[]>([])
const fetching_data = ref(false)
const search = ref('')
const is_searching = ref(false)

const props = defineProps<{
	campaign: Campaign
}>()

const pagination = reactive({
	current_page: 1,
	last_page: 1,
	per_page: 30,
	total: 0,
	from: 0,
	to: 0,
})

function goToPreviousPage() {
	if (pagination.current_page > 1) {
		pagination.current_page--
		fetchData()
	}
}

function goToNextPage() {
	if (pagination.current_page < pagination.last_page) {
		pagination.current_page++
		fetchData()
	}
}

function handleSearch(){
	is_searching.value = true
	pagination.current_page = 1
	fetchData()
}

async function fetchData() {
	fetching_data.value = true
	try {
		const resp = await campaignProducts(props.campaign.id, {
			name: search.value || '',
			page: pagination.current_page,
			per_page: pagination.per_page,
		}) as any;
		data_list.value = resp.data
		is_searching.value = false

		if (resp.meta) {
			Object.assign(pagination, resp.meta)
		}
	} catch (error) {
		console.error({error})
	} finally {
		fetching_data.value = false
	}
}

const headers = [
	{ title: 'Product Name', key: 'name', sortable: false },
	{ title: 'Price', key: 'price', sortable: true },
	{ title: 'Discount', key: 'discount', sortable: false },
	{ title: 'Actions', key: 'actions', sortable: false },
]

function openModal() {
	modal.open(
		CampaignProductForm,
		{ item: props.campaign },
		{
			size: 'xl',
			title: 'Assign Products',
			onSaved: () => fetchData()
		}
	);
}

function updateItem(item: any) {
	// modal.open(
	// 	CampaignProductEditForm,
	// 	{ item },
	// 	{
	// 		size: 'md',
	// 		title: 'Update Item',
	// 		onSaved: () => fetchData()
	// 	}
	// );
}

function updateDiscount() {
	// modal.open(
	// 	CampaignDiscountForm,
	// 	{ item: props.campaign },
	// 	{
	// 		size: 'md',
	// 		title: 'Update Discount',
	// 		onSaved: () => fetchData()
	// 	}
	// );
}

function deleteModal(item: any) {
	// modal.open(
	// 	CampaignProductDelete,
	// 	{ item },
	// 	{
	// 		size: 'sm',
	// 		title: 'Delete Item',
	// 		onSaved: () => fetchData()
	// 	}
	// );
}

onMounted(() => {
	if (props.campaign?.id) {
		fetchData()
	}
})
</script>

<style scoped></style>