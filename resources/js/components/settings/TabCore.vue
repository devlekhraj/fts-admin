<template>
	<v-card class="pa-4" variant="flat">
		<div>
			<v-row>
				<v-col cols="12" md="6">
					<div class="pt-4">
						<div class="text-h6 mb-1">Core Settings</div>
						<div class="text-body-2 text-medium-emphasis mb-4">
							Manage brand identity and core description content.
						</div>
					</div>
					<div>
						<div class="text-body-2 text-medium-emphasis mb-1">Brand Name</div>
						<v-text-field v-model="form.brand_name" density="comfortable" variant="outlined" />
					</div>
					<div>
						<div class="text-body-2 text-medium-emphasis mb-1">Brand Acronym</div>
						<v-text-field v-model="form.brand_acronym" density="comfortable" variant="outlined" />
					</div>
					<div>
						<div class="text-body-2 text-medium-emphasis mb-1">Description</div>
						<RichText v-model="form.description" />
					</div>
				</v-col>
			</v-row>
		</div>
	</v-card>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive } from 'vue';

defineProps<{ data?: unknown }>();
const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const form = reactive({
	brand_name: 'Fatafat Sewa',
	brand_acronym: 'Fatafat',
	description: '<h1><strong>Nepal’s first service-oriented Shopping Site</strong></h1>...',
});
</script>
