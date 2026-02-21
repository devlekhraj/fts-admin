<template>
	<v-card class="pa-4" variant="flat">
		<div class="text-medium-emphasis mb-3">
			Updated: {{ moduleItem?.updated_at ?? '-' }}
		</div>
		<v-row v-if="settingsEntries.length">
			<v-col v-for="[key, value] in settingsEntries" :key="key" cols="12" md="6">
				<div class="text-medium-emphasis" style="font-size: 0.8rem;">{{ key }}</div>
				<div class="mt-1">{{ formatSettingValue(value) }}</div>
			</v-col>
		</v-row>
		<div v-else class="text-medium-emphasis">No settings found.</div>
	</v-card>
</template>

<script setup lang="ts">
import { computed } from 'vue';

type SettingItem = {
	id?: number | string;
	module?: string | null;
	settings?: Record<string, unknown> | null;
	updated_at?: string | null;
};

const props = defineProps<{ data: SettingItem[] }>();

const moduleItem = computed(() => props.data.find((item) => item.module === 'email_settings'));
const settingsEntries = computed(() => Object.entries(moduleItem.value?.settings ?? {}));

function formatSettingValue(value: unknown) {
	if (value == null) return '-';
	if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') return String(value);
	try {
		return JSON.stringify(value);
	} catch {
		return String(value);
	}
}
</script>
