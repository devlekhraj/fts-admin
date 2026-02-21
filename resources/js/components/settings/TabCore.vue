<template>
	<v-card class="pa-4" variant="flat">
		<div class="text-medium-emphasis mb-3">
			Updated: {{ moduleItem?.updated_at ?? '-' }}
		</div>
		<div v-if="settingsEntries.length">
			<v-row v-if="generalEntries.length">
				<v-col v-for="[key, value] in generalEntries" :key="key" cols="12" md="6">
					<v-text-field :label="key" :model-value="formatSettingValue(value)" density="comfortable" variant="outlined" />
				</v-col>
			</v-row>

			<v-divider class="my-4" />
			<div class="text-medium-emphasis mb-2">Contact</div>
			<v-row>
				<v-col v-for="[key, value] in contactEntries" :key="key" cols="12" md="6">
					<v-text-field :label="key" :model-value="formatSettingValue(value)" density="comfortable" variant="outlined" />
				</v-col>
			</v-row>

			<v-divider class="my-4" />
			<div class="text-medium-emphasis mb-2">Social</div>
			<v-row>
				<v-col v-for="[key, value] in socialEntries" :key="key" cols="12" md="6">
					<v-text-field :label="socialLabel(key)" :model-value="formatSettingValue(value)" density="comfortable" variant="outlined" />
				</v-col>
			</v-row>

			<v-divider class="my-4" />
			<div class="text-medium-emphasis mb-2">Public SEO</div>
			<v-row>
				<v-col v-for="[key, value] in seoEntries" :key="key" cols="12" md="6">
					<v-text-field :label="seoLabel(key)" :model-value="formatSettingValue(value)" density="comfortable" variant="outlined" />
				</v-col>
			</v-row>

			<v-divider class="my-4" />
			<div class="text-medium-emphasis mb-2">Description</div>
			<div>{{ descriptionEntry ? formatSettingValue(descriptionEntry[1]) : '-' }}</div>
		</div>
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

const moduleItem = computed(() => props.data.find((item) => item.module === 'core'));
const settingsEntries = computed(() => Object.entries(moduleItem.value?.settings ?? {}));
const descriptionEntry = computed(() => settingsEntries.value.find(([key]) => key === 'description'));

const contactKeys = new Set(['email', 'contact_number']);
const socialKeys = new Set([
	'social_facebook',
	'social_twitter',
	'social_instagram',
	'social_linkedin',
	'social_google',
	'social_youtube',
]);
const seoKeys = new Set(['meta_title', 'meta_keywords', 'meta_description']);

const contactEntries = computed(() =>
	settingsEntries.value.filter(([key]) => contactKeys.has(key)),
);
const socialEntries = computed(() =>
	settingsEntries.value.filter(([key]) => socialKeys.has(key)),
);
const seoEntries = computed(() =>
	settingsEntries.value.filter(([key]) => seoKeys.has(key)),
);
const generalEntries = computed(() =>
	settingsEntries.value.filter(
		([key]) =>
			key !== 'description' &&
			!contactKeys.has(key) &&
			!socialKeys.has(key) &&
			!seoKeys.has(key),
	),
);

function socialLabel(key: string) {
	const map: Record<string, string> = {
		social_facebook: 'Facebook',
		social_twitter: 'Twitter',
		social_instagram: 'Instagram',
		social_linkedin: 'Linkedin',
		social_google: 'Google',
		social_youtube: 'Youtube',
	};
	return map[key] ?? key;
}

function seoLabel(key: string) {
	const map: Record<string, string> = {
		meta_title: 'Meta Title',
		meta_keywords: 'Meta Keywords',
		meta_description: 'Meta Description',
	};
	return map[key] ?? key;
}

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
