<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="8" offset-lg="2">
				<div class="pt-4">
					<div class="text-h6 mb-1">SEO Settings</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Configure default SEO metadata for the website.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Meta Title</div>
					<v-textarea v-model="form.meta_title" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Meta Description</div>
					<v-textarea v-model="form.meta_description" density="comfortable" variant="outlined" auto-grow rows="4" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Meta Keywords</div>
					<v-textarea v-model="form.meta_keywords" density="comfortable" variant="outlined" auto-grow rows="3" />
				</div>
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

const form = reactive({
	meta_title: 'Online Shopping in Nepal: Best Deals, Prices & 0% EMI',
	meta_description:
		'Enjoy online shopping in Nepal with Fatafat Sewa! Shop mobiles, laptops, drones, cameras, and home appliances with 0% EMI for 18 months and fast delivery.',
	meta_keywords: '',
});
</script>
