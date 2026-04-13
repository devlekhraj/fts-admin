<template>
	<AppPageHeader title="EMI Request Detail" subtitle="View EMI request information">
		<template #actions>
			<v-btn color="success" variant="flat" prepend-icon="mdi-check-bold">Approve
				Request</v-btn>
			<v-btn color="error" variant="flat" prepend-icon="mdi-close-thick">Reject Request</v-btn>

			<v-btn color="error" variant="outlined" prepend-icon="mdi-delete-outline">Delete</v-btn>

			<v-btn variant="flat" color="primary" @click="goBack">
				<v-icon start>mdi-arrow-left</v-icon>
				Back
			</v-btn>
		</template>
	</AppPageHeader>

	<v-card class="pa-6 mb-6">
		<div class="summary">
			<div class="summary__left">
				<v-avatar size="76" color="primary" variant="tonal" class="mr-4" rounded>
					<v-icon size="36">mdi-clipboard-text</v-icon>
				</v-avatar>
				<div>
					<div class="text-subtitle-1 font-weight-bold">{{ referenceId }}</div>
					<div class="chips">
						<v-chip size="small" color="primary" label variant="tonal">{{ emiType }}</v-chip>
						<v-chip size="small" color="info" label variant="tonal">{{ statusLabel }}</v-chip>
					</div>
					<div class="text-body-2 text-primary mt-2 font-weight-medium">{{ productName }}</div>

					<div class="text-body-2 text-medium-emphasis mt-1 dot-line">
						{{ applicantInfo.name }} · {{ applicantInfo.phone }} · {{ applicantInfo.email }}
					</div>
				</div>
			</div>
			<div class="summary__right">
				<div class="text-body-2 text-medium-emphasis">Submitted On</div>
				<div class="text-body-1">{{ submittedAt }}</div>
			</div>
		</div>
	</v-card>

	<v-row class="mb-4">
		<v-col cols="12" md="7">
			<v-card class="mb-6">
				<v-card-title><v-icon size="18" class="mr-2">mdi-card-account-details-outline</v-icon> Applicant
					Information</v-card-title>
				<v-divider></v-divider>
				<div class="pa-4">
					<div>
						<v-row>
							<v-col cols="12" md="6">
								<v-table>
									<tbody>
										<tr>
											<td class="text-medium-emphasis">Full Name</td>
											<td class="font-weight-medium text-body-2 text-right">{{ applicantInfo.name
											}}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Email</td>
											<td class="font-weight-medium text-body-2 text-right">{{ applicantInfo.email
											}}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Phone</td>
											<td class="font-weight-medium text-body-2 text-right">{{ applicantInfo.phone
											}}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Gender</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												applicantInfo.gender }}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Address</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												applicantInfo.address
											}}
											</td>
										</tr>

									</tbody>
								</v-table>

							</v-col>

							<v-col cols="12" md="6" class="with-divider">
								<div>
									<v-table>
										<tbody>

											<tr>
												<td class="text-medium-emphasis">Date of Birth (AD)</td>
												<td class="font-weight-medium text-body-2 text-right">{{
													applicantInfo.dobAd
												}}
												</td>
											</tr>
											<tr>
												<td class="text-medium-emphasis">Date of Birth (BS)</td>
												<td class="font-weight-medium text-body-2 text-right">{{
													applicantInfo.dobBs
												}}
												</td>
											</tr>
											<tr>
												<td class="text-medium-emphasis">Marital Status</td>
												<td class="font-weight-medium text-body-2 text-right">
													{{ applicantInfo.maritalStatus }}
												</td>
											</tr>
											<tr>
												<td class="text-medium-emphasis">Citizenship Number</td>
												<td class="font-weight-medium text-body-2 text-right">
													{{ applicantInfo.citizenshipNumber }}
												</td>
											</tr>
											<tr>
												<td class="text-medium-emphasis">Agreed to Terms</td>
												<td class="font-weight-medium text-body-2 text-right">{{
													applicantInfo.agreed
												}}
												</td>
											</tr>
										</tbody>
									</v-table>
								</div>
							</v-col>

						</v-row>
					</div>
					<DocGrid class="mt-4" :items="applicantInfo?.documents || []" />
				</div>
			</v-card>

			<v-card class="mb-6">
				<v-card-title><v-icon size="18" class="mr-2">mdi-wallet-outline</v-icon> Finance
					Information</v-card-title>
				<v-divider></v-divider>
				<div class="px-4">
					<v-row>
						<v-col cols="12" md="6">
							<div>
								<v-table>
									<tbody>
										<tr>
											<td class="text-medium-emphasis">Product Name</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												financialInfo.productPrice }}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Down Payment</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												financialInfo.downPayment }}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Finance Amount</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												financialInfo.loanAmount }}
											</td>
										</tr>
									</tbody>
								</v-table>
							</div>
						</v-col>
						<v-col cols="12" md="6">
							<div>
								<v-table>
									<tbody>
										<tr>
											<td class="text-medium-emphasis">Duration</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												financialInfo.duration
											}}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">EMI Per Month</td>
											<td class="font-weight-medium text-body-2 text-right">{{
												financialInfo.emiAmount
											}}
											</td>
										</tr>
										<tr>
											<td class="text-medium-emphasis">Preferred Bank</td>
											<td class="font-weight-medium text-body-2 text-right">{{ financialInfo.bank
											}}
											</td>
										</tr>
									</tbody>
								</v-table>
							</div>
						</v-col>
					</v-row>
				</div>
			</v-card>
			<v-card class="mb-6">
				<v-card-title><v-icon size="18" class="mr-2">mdi-credit-card-outline</v-icon> Credit Card
					Info</v-card-title>
				<v-divider></v-divider>
				<div class="px-4">
					<v-row>
						<v-col cols="12" md="6">
							<v-table>
								<tbody>
									<tr>
										<td class="text-medium-emphasis">Card Holder</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											creditCardInfo.card_holder
										}}</td>
									</tr>
									<tr>
										<td class="text-medium-emphasis">Card Number</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											creditCardInfo.card_number
										}}</td>
									</tr>
									<tr>
										<td class="text-medium-emphasis">Expiry Date</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											creditCardInfo.expiry_date
										}}</td>
									</tr>
									<tr>
										<td class="text-medium-emphasis">Credit Limit</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											creditCardInfo.credit_limit
										}}</td>
									</tr>
								</tbody>
							</v-table>
						</v-col>
						<v-col cols="12" md="6" class="with-divider">
							<v-table>
								<tbody>
									<tr>
										<td class="text-medium-emphasis">Provider</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											creditCardInfo.provider.name }}</td>
									</tr>
									<tr>
										<td class="text-medium-emphasis">Bank Code</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											creditCardInfo.provider.bank_code }}</td>
									</tr>
									<!-- <tr>
										<td class="text-medium-emphasis">Provider ID</td>
										<td class="font-weight-medium text-body-2 text-right">{{ creditCardInfo.provider.id }}</td>
									</tr> -->
								</tbody>
							</v-table>
						</v-col>
					</v-row>
				</div>
			</v-card>
			<v-card class="mb-6">
				<v-card-title><v-icon size="18" class="mr-2">mdi-bank</v-icon> Preferred
					Bank</v-card-title>
				<v-divider></v-divider>
				<div class="px-4">
					<v-row>
						<v-col cols="12" md="6">
							<v-table>
								<tbody>
									<tr>
										<td class="text-medium-emphasis">Bank Name</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											preferredBank.bank_name
										}}</td>
									</tr>
									<tr>
										<td class="text-medium-emphasis">Bank Code</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											preferredBank.bank_code
										}}</td>
									</tr>

								</tbody>
							</v-table>
						</v-col>
						<v-col cols="12" md="6" class="with-divider">
							<v-table>
								<tbody>
									<tr>
										<td class="text-medium-emphasis">Branch Name</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											preferredBank.branch
										}}</td>
									</tr>
									<tr>
										<td class="text-medium-emphasis">Account Number</td>
										<td class="font-weight-medium text-body-2 text-right">{{
											preferredBank.account_number
										}}</td>
									</tr>
								</tbody>
							</v-table>
						</v-col>
					</v-row>
				</div>
			</v-card>

			<v-card class="mb-6">
				<v-card-title><v-icon size="18" class="mr-2">mdi-account-check-outline</v-icon> Guarantor
					Details</v-card-title>
				<v-divider></v-divider>

				<div class="pa-4">
					<div v-for="(guerantor, index) in guarantorList" :key="index">
						<div>
							<v-row>
								<v-col cols="12" md="6">
									<div>
										<v-table>
											<tbody>
												<tr>
													<td class="text-medium-emphasis">Full Name</td>
													<td class="font-weight-medium text-body-2 text-right">{{
														guerantor.name
													}}
													</td>
												</tr>
												<tr>
													<td class="text-medium-emphasis">Phone</td>
													<td class="font-weight-medium text-body-2 text-right">{{
														guerantor.phone
													}}
													</td>
												</tr>
												<tr>
													<td class="text-medium-emphasis">Gender</td>
													<td class="font-weight-medium text-body-2 text-right">{{
														guerantor.gender
													}}
													</td>
												</tr>
											</tbody>
										</v-table>
									</div>
								</v-col>
								<v-col cols="12" md="6">
									<div>
										<v-table>
											<tbody>
												<tr>
													<td class="text-medium-emphasis">Marital Status</td>
													<td class="font-weight-medium text-body-2 text-right">{{
														guerantor.maritalStatus }}
													</td>
												</tr>
												<tr>
													<td class="text-medium-emphasis">Citizenship</td>
													<td class="font-weight-medium text-body-2 text-right">{{
														guerantor.citizenshipNumber }}
													</td>
												</tr>
											</tbody>
										</v-table>
									</div>
								</v-col>
							</v-row>
						</div>
						<div>
							<DocGrid class="mt-4" :items="guerantor?.documents || []" />
						</div>
					</div>
				</div>
			</v-card>
		</v-col>

		<v-col cols="12" md="5">
			<v-card class="mb-4">
				<v-card-title><v-icon size="18" class="mr-2">mdi-timeline-clock-outline</v-icon>Generate
					Application</v-card-title>
				<v-divider></v-divider>
				<div>
					<EmiBankApplicationList :data="application"> </EmiBankApplicationList>
				</div>
			</v-card>
			<v-card class="mb-4">
				<v-card-title><v-icon size="18" class="mr-2">mdi-timeline-clock-outline</v-icon> Activity
					Timeline</v-card-title>
				<v-divider></v-divider>
				<ActivityTimeline :items="timelineItems" dot-color="primary" class="pa-4" />
			</v-card>
		</v-col>
	</v-row>

	<v-alert v-if="loading" type="info" variant="tonal">Loading EMI request detail...</v-alert>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { get as getEmiRequest } from '@/api/emi-requests.api';
import { formatNPR } from '@/shared/formatters';
import ActivityTimeline from '@/components/emi/ActivityTimeline.vue';
import DocGrid from '@/components/emi/DocGrid.vue';
import EmiBankApplicationList from '@/components/emi/EmiBankApplicationList.vue';
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

const route = useRoute();
const router = useRouter();


const timeline = [
	{ title: 'Request submitted by System', subtitle: 'Customer submitted EMI request via product page', time: 'Apr 8, 10:15 AM' },
	{ title: 'Documents uploaded by Raj Kumar', subtitle: '4 documents uploaded for verification', time: 'Apr 8, 10:20 AM' },
	{ title: 'Under review by System', subtitle: 'Assigned to verification team', time: 'Apr 8, 11:00 AM' },
	{ title: 'Identity verified by Sarah M.', subtitle: 'Citizenship verified successfully', time: 'Apr 9, 9:30 AM' },
];
const timelineItems = computed(() => [...timeline].reverse());

const productName = computed(() => String(application.value.product?.name ?? '').trim() || 'N/A');

const referenceId = computed(() => {
	const id = (application.value as any)?.id;
	return id ? `#EMIR-${id}` : 'EMIR-7001';
});
const statusLabel = computed(() => (application.value.status ? String(application.value.status) : 'under review'));
const submittedAt = computed(() => String((application.value as any)?.submitted_at ?? 'April 8, 2024 at 10:15 AM'));

const emiType = computed(() => {
	const raw = String((application.value as any)?.emi_type ?? 'n/a');
	return raw.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
});

const applicant = computed(() => (application.value.user ?? {}) as Record<string, unknown>);

const applicantInfo = computed(() => ({
	name: String(applicant.value.name ?? application.value.name ?? 'N/A'),
	email: String(applicant.value.email ?? 'N/A'),
	phone: String(applicant.value.phone ?? applicant.value.mobile ?? 'N/A'),
	dobAd: String((application.value as any)?.dob_ad ?? '1995-05-15'),
	dobBs: String((application.value as any)?.dob_bs ?? '2052-01-31'),
	gender: String((application.value as any)?.gender ?? 'Male'),
	maritalStatus: String((application.value as any)?.marital_status ?? 'Married'),
	citizenshipNumber: String((application.value as any)?.citizenship_number ?? '45-01-78-12345'),
	address: String((application.value as any)?.address ?? 'Kathmandu, Bagmati Pradesh, Nepal'),
	agreed: (application.value as any)?.agreed_to_terms ? 'Yes' : 'Yes',
	documents: (application.value as any).documents
}));



const financialInfo = computed(() => {
	const price = Number(application.value.product?.price ?? application.value.product_price ?? 0);
	const emi = Number(application.value.emi_per_month ?? 0);
	return {
		productPrice: price > 0 ? formatNPR(price) : 'N/A',
		downPayment: formatNPR(Number((application.value as any)?.down_payment ?? 0)),
		loanAmount: formatNPR(Number((application.value as any)?.loan_amount ?? 0)),
		duration: String((application.value as any)?.duration ?? '12 months'),
		emiAmount: emi > 0 ? formatNPR(emi) : 'N/A',
		bank: String((application.value as any)?.bank ?? 'NIC Asia Bank'),
	};
});

const preferredBank = computed(() => {

	return {
		account_number: String((application.value as any)?.request_bank?.account_number ?? ''),
		bank_code: String((application.value as any)?.request_bank?.bank_code ?? ''),
		bank_name: String((application.value as any)?.request_bank?.bank_name ?? ''),
		branch: String((application.value as any)?.request_bank?.branch ?? ''),
	}
});

const creditCardInfo = computed(() => {
	const cc = (application.value as any)?.credit_card ?? {};
	return {
		id: cc.id ?? 'N/A',
		emi_request_id: cc.emi_request_id ?? 'N/A',
		card_number: cc.card_number ?? 'N/A',
		card_holder: cc.card_holder ?? 'N/A',
		expiry_date: cc.expiry_date ?? 'N/A',
		credit_limit: cc.credit_limit ? formatNPR(cc.credit_limit) : 'N/A',
		provider: {
			id: cc.provider?.id ?? 'N/A',
			name: cc.provider?.name ?? 'N/A',
			bank_code: cc.provider?.bank_code ?? 'N/A',
		},
	};
});

const guarantorList = computed(() => {
	const g = (application.value as any)?.guarantors;
	const list = Array.isArray(g) ? g : g ? [g] : [];
	return list.map((item: any) => ({
		name: String(item?.name ?? 'Suman Shrestha'),
		phone: String(item?.phone ?? '+977 98111 22233'),
		gender: String(item?.gender ?? 'Male'),
		maritalStatus: String(item?.marital_status ?? 'Married'),
		citizenshipNumber: String(item?.citizenship_number ?? '45-01-78-67890'),
		documents: item?.documents ?? [],
	}));
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
.doc-section-title {
	line-height: 1.6;
	font-size: 1rem;
	font-weight: 600;
}

.doc-item-title {
	font-weight: 500;
	font-size: 0.8rem;
	text-transform: capitalize;
}

.v-card .v-card-title {
	line-height: 1.6;
	font-size: 1rem;
	font-weight: 600;
}

.summary {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.summary__left {
	display: flex;
	align-items: center;
}

.summary__right {
	text-align: right;
}

.dot-line {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.chips {
	display: flex;
	gap: 8px;
	margin: 6px 0 2px;
}

.section-title {
	display: flex;
	align-items: center;
	font-weight: 600;
	margin-bottom: 12px;
}

.info-grid {
	display: flex;
	flex-direction: column;
	gap: 6px;
}

.with-divider {
	border-left: 1px solid var(--v-border-color, #e0e0e0);
	padding-left: 16px;
}

.docs-grid {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 10px;
}

.doc-card {
	min-height: 110px;
}

.doc-thumb {
	border-radius: 4px;
	overflow: hidden;
	display: flex;
	align-items: center;
	justify-content: center;
	height: 140px;
	background: #f6f8fb;
	padding: 10px;
}

.thumb {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.dot-line {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

@media (max-width: 600px) {
	.summary {
		flex-direction: column;
		align-items: flex-start;
		gap: 12px;
	}

	.summary__right {
		text-align: left;
	}

	.with-divider {
		border-left: none;
		padding-left: 0;
		margin-top: 8px;
	}

	.dot-line {
		white-space: normal;
	}

	.docs-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}
</style>
