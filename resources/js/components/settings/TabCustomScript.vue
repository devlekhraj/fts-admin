<template>
	<v-card class="pa-4" variant="flat">
		<div>
			<div class="pt-4">
				<div class="text-h6 mb-1">Custom Scripts</div>
				<div class="text-body-2 text-medium-emphasis mb-4">
					Manage custom script blocks and third-party integrations.
				</div>
			</div>
			<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Saepe, reiciendis?</p>
		</div>
	</v-card>
</template>

<script setup lang="ts">


type SettingItem = {
	id?: number | string;
	module?: string | null;
	settings?: Record<string, unknown> | null;
	updated_at?: string | null;
};

const props = defineProps<{ data: SettingItem[] }>();


</script>
