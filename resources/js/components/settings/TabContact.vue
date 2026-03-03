<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div class="pt-4">
					<div class="text-h6 mb-1">Contact Settings</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Configure business contact and communication details.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Address</div>
					<v-text-field v-model="form.address" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Email</div>
					<v-text-field v-model="form.email" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Contact Number</div>
					<v-text-field v-model="form.contact_number" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Contact Email</div>
					<v-text-field v-model="form.contact_email" density="comfortable" variant="outlined" />
				</div>
			</v-col>
			
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

const form = reactive({
	address: 'Sitapaila Road - 4 ,Kathmandu',
	email: 'info[@]fatafatsewa.com',
	contact_number: '015917074 / 9828757575',
	contact_email: 'fatafatnp@gmail.com',
});
</script>
