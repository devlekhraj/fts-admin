<template>
	<AppPageHeader title="Settings" subtitle="System settings">
		<template #actions>
			<SettingsActions />
		</template>
	</AppPageHeader>

	<v-tabs v-model="activeTab" density="compact">
		<v-tab v-for="tab in tabItems" :key="tab.id" color="primary" :value="tab.module">
			{{ tab.name || 'Unnamed Module' }}
		</v-tab>
	</v-tabs>
  <v-divider></v-divider>


	<v-window v-model="activeTab">
		<v-window-item v-for="tab in tabItems" :key="tab.id" :value="tab.module">
			<component :is="tab.component" :data="settings" />
		</v-window-item>
	</v-window>
  
</template>

<script setup lang="ts">
import { computed, markRaw, onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import SettingsActions from '@/components/settings/SettingsActions.vue';
import { listSettings } from '@/api/settings.api';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';
import TabCore from '@/components/settings/TabCore.vue';
import TabCustomScript from '@/components/settings/TabCustomScript.vue';
import TabEcommerce from '@/components/settings/TabEcommerce.vue';
import TabEmailSettings from '@/components/settings/TabEmailSettings.vue';
import TabSmsSettings from '@/components/settings/TabSmsSettings.vue';
import TabSocialSettings from '@/components/settings/TabSocialSettings.vue';

type SettingItem = {
	id?: number | string;
	module?: string | null;
	name?: string | null;
	settings?: Record<string, unknown> | null;
	updated_at?: string | null;
};

const snackbar = useSnackbarStore();
const loading = ref(false);
const settings = ref<SettingItem[]>([]);
const tabItems = ref<Array<{ id?: number | string; name?: string | null; module?: string | null; component?: any }>>([]);
const activeTab = ref<string>('');
const componentMap: Record<string, any> = {
	core: markRaw(TabCore),
	custom_scripts: markRaw(TabCustomScript),
	ecommerce: markRaw(TabEcommerce),
	email_settings: markRaw(TabEmailSettings),
	sms_settings: markRaw(TabSmsSettings),
	social_settings: markRaw(TabSocialSettings),
};
const activeComponent = computed(() => {
	const item = tabItems.value.find((tab) => tab.module === activeTab.value);
	return item?.component ?? null;
});

async function fetchSettings() {
	loading.value = true;
	try {
		const { data } = await listSettings();
		const list = Array.isArray(data) ? data : data?.data ?? [];
		settings.value = list;
		tabItems.value = list.map((item: SettingItem) => ({
			id: item.id,
			name: item.name,
			module: item.module,
			component: item.module ? componentMap[item.module] : null,
		}));
		if (!activeTab.value && settings.value.length > 0) {
			activeTab.value = String(settings.value[0].module ?? '');
		}
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}

onMounted(fetchSettings);
</script>
