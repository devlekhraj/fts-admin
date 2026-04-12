<template>
	<AppPageHeader title="Settings" subtitle="System settings">
		<!-- <template #actions>
			<SettingsActions />
		</template> -->
	</AppPageHeader>


	<v-container fluid>
		<v-card>
			<v-tabs v-model="activeTab" density="compact">
				<v-tab v-for="tab in tabItems" :key="tab.id" color="primary" :value="tab.module">
					{{ tab.name || 'Unnamed Module' }}
				</v-tab>
			</v-tabs>
			<v-divider></v-divider>
		
		
			<v-window v-model="activeTab">
				<v-window-item v-for="tab in tabItems" :key="tab.id" :value="tab.module">
					<component :is="tab.component" :data="settingsData[tab.module]" />
				</v-window-item>
			</v-window>
		</v-card>
	</v-container>

</template>

<script setup lang="ts">
import { markRaw, onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
// import SettingsActions from '@/components/settings/SettingsActions.vue';
import { listSettings } from '@/api/settings.api';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';
import TabCore from '@/components/settings/TabCore.vue';
import TabContact from '@/components/settings/TabContact.vue';
import TabCustomScript from '@/components/settings/TabCustomScript.vue';
import TabLogo from '@/components/settings/TabLogo.vue';
import TabSeo from '@/components/settings/TabSeo.vue';
import TabSmtp from '@/components/settings/TabSmtp.vue';
import TabCredential from '@/components/settings/TabCredential.vue';
import TabSocialProfile from '@/components/settings/TabSocialProfile.vue';
import TabTemplates from '@/components/settings/TabTemplates.vue';

const snackbar = useSnackbarStore();
const loading = ref(false);
const tabConfigMap: Record<string, { name: string; component: any }> = {
	core: { name: 'Core', component: markRaw(TabCore) },
	logo: { name: 'Logo', component: markRaw(TabLogo) },
	contact: { name: 'Contact', component: markRaw(TabContact) },
	smtp: { name: 'SMTP', component: markRaw(TabSmtp) },
	credentials: { name: 'Credentials', component: markRaw(TabCredential) },
	social_profiles: { name: 'Social Profiles', component: markRaw(TabSocialProfile) },
	custom_scripts: { name: 'Custom Scripts', component: markRaw(TabCustomScript) },
	seo: { name: 'SEO', component: markRaw(TabSeo) },
	templates: { name: 'Templates', component: markRaw(TabTemplates) },
};
const tabItems = ref<Array<{ id?: number | string; name?: string | null; module: string; component?: any }>>([
	{ id: 'core', name: tabConfigMap.core.name, module: 'core', component: tabConfigMap.core.component },
	{ id: 'logo', name: tabConfigMap.logo.name, module: 'logo', component: tabConfigMap.logo.component },
	{ id: 'contact', name: tabConfigMap.contact.name, module: 'contact', component: tabConfigMap.contact.component },
	{ id: 'smtp', name: tabConfigMap.smtp.name, module: 'smtp', component: tabConfigMap.smtp.component },
	{
		id: 'credentials',
		name: tabConfigMap.credentials.name,
		module: 'credentials',
		component: tabConfigMap.credentials.component,
	},
	{
		id: 'social_profiles',
		name: tabConfigMap.social_profiles.name,
		module: 'social_profiles',
		component: tabConfigMap.social_profiles.component,
	},
	{
		id: 'custom_scripts',
		name: tabConfigMap.custom_scripts.name,
		module: 'custom_scripts',
		component: tabConfigMap.custom_scripts.component,
	},
	{ id: 'seo', name: tabConfigMap.seo.name, module: 'seo', component: tabConfigMap.seo.component },
	{
		id: 'templates',
		name: tabConfigMap.templates.name,
		module: 'templates',
		component: tabConfigMap.templates.component,
	},
]);
const activeTab = ref<string>('core');
const settingsData = ref<Record<string, any>>({});

async function fetchSettings() {
	loading.value = true;
	try {
		const response = await listSettings();
		if (response && response.data) {
			const mapped: Record<string, any> = {};
			response.data.forEach((item: any) => {
				mapped[item.module] = item.settings;
			});
			settingsData.value = mapped;
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
