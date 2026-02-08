<template>
	<v-card flat>
		<v-card-title class="d-flex justify-space-between align-center py-1">
			<span class="font-semibold">Bank Application Form</span>
			<v-spacer></v-spacer>
			<v-btn icon="mdi-close" size="small" color="error" class="cursor-pointer" variant="text" aria-label="Close"
				@click="$emit('close')">
				<v-icon>mdi-close</v-icon>
			</v-btn>
		</v-card-title>

		<v-divider></v-divider>

		<v-card-text>
			<v-tabs v-model="activeTab" density="comfortable">
				<v-tab value="nabil">Nabil Bank</v-tab>
				<v-tab value="siddhartha">Siddhartha Bank</v-tab>
			</v-tabs>
			<div class="mt-4">
				<component :is="activeFormComponent" />
			</div>
		</v-card-text>

	</v-card>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import EmiNabilBankForm from './EmiNabilBankForm.vue';
import EmiSiddharthaBankForm from './EmiSiddharthaBankForm.vue';

const activeTab = ref('nabil');

const activeFormComponent = computed(() => {
	return activeTab.value === 'siddhartha' ? EmiSiddharthaBankForm : EmiNabilBankForm;
});
</script>
