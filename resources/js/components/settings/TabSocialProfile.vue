<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="6" offset-lg="3">
				<div class="pt-4">
					<div class="text-h6 mb-1">Social Profiles</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Manage social media and public profile links.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Facebook</div>
					<v-text-field v-model="form.facebook" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Instagram</div>
					<v-text-field v-model="form.instagram" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Twitter</div>
					<v-text-field v-model="form.twitter" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">LinkedIn</div>
					<v-text-field v-model="form.linkedin" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Google</div>
					<v-text-field v-model="form.google" density="comfortable" variant="outlined" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">YouTube</div>
					<v-text-field v-model="form.youtube" density="comfortable" variant="outlined" />
				</div>
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

defineProps<{ data?: unknown }>();

const form = reactive({
	facebook: 'https://facebook.com/fatafatsewanpl',
	instagram: 'https://instagram.com/fatafatsewanp',
	twitter: 'https://twitter.com/fatafatsewa',
	linkedin: 'https://linkedin.com/comapny/fatafatsewa',
	google: 'https://g.page/r/CQQ3XhlqdcupEAE',
	youtube: '',
});
</script>
