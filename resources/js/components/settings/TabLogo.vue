<template>
	<div class="pa-6">
		<div class="pt-4">
			<div class="text-h6 mb-1">Logo Settings</div>
			<div class="text-body-2 text-medium-emphasis mb-4">
				Manage brand logo and login image assets.
			</div>
		</div>
		<div class="d-flex align-center justify-space-between mb-4">
			<div class="text-body-2 text-medium-emphasis">
				Total images: {{ imageItems.length }}
			</div>
		</div>

		<v-table v-if="imageItems.length" density="comfortable">
			<thead>
				<tr>
					<th>Image</th>
					<th>Details</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="item in imageItems" :key="item.key">
					<td class="py-3">
						<div class="table-image-preview rounded">
							<v-img v-if="item.preview" :src="item.preview" cover />
							<div v-else class="d-flex align-center justify-center h-100">
								<v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
							</div>
						</div>
					</td>
					<td class="py-3 details-col">
						<div class="text-body-2 font-weight-medium">{{ item.label }}</div>
						<div class="text-caption text-medium-emphasis">{{ item.url || '-' }}</div>
					</td>
					<td class="py-3">
						<div class="d-flex align-center ga-1">
							<v-btn
								v-if="item.url"
								:href="item.url"
								target="_blank"
								rel="noopener noreferrer"
								icon
								size="x-small"
								variant="tonal"
								color="primary">
								<v-icon size="16">mdi-eye</v-icon>
							</v-btn>
							<v-btn
								v-else
								icon
								size="x-small"
								variant="tonal"
								color="primary"
								disabled>
								<v-icon size="16">mdi-eye</v-icon>
							</v-btn>
							<v-btn icon size="x-small" variant="tonal" color="primary" @click="openPicker(item.key)">
								<v-icon size="16">mdi-image-plus</v-icon>
							</v-btn>
						</div>
					</td>
				</tr>
			</tbody>
		</v-table>

		<div v-else class="empty-images-state">
			<v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
			<div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
			<div class="text-body-2 text-medium-emphasis">Add an image to get started.</div>
		</div>

		<input
			ref="logoFileInput"
			type="file"
			accept="image/*"
			class="d-none"
			@change="onLogoPick" />
		<input
			ref="loginImageFileInput"
			type="file"
			accept="image/*"
			class="d-none"
			@change="onLoginImagePick" />
	</div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';

defineProps<{ data?: unknown }>();

const logoFileInput = ref<HTMLInputElement | null>(null);
const loginImageFileInput = ref<HTMLInputElement | null>(null);
const defaultImage = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="84" height="84"><rect width="100%25" height="100%25" fill="%23f3f4f6"/><text x="50%25" y="54%25" dominant-baseline="middle" text-anchor="middle" fill="%239ca3af" font-size="12" font-family="Arial">No Image</text></svg>';

const form = reactive({
	logo: '/storage/uploads/images/settings/core/1737971940-881.png',
	login_image: '/storage/uploads/images/settings/core/1737971989-204c.png',
});

const imageItems = computed(() => [
	{ key: 'logo', label: 'Logo', url: form.logo, preview: form.logo || defaultImage },
	{ key: 'login_image', label: 'Login Image', url: form.login_image, preview: form.login_image || defaultImage },
]);

function openPicker(key: string) {
	if (key === 'logo') {
		logoFileInput.value?.click();
		return;
	}
	loginImageFileInput.value?.click();
}

function onLogoPick(event: Event) {
	const input = event.target as HTMLInputElement | null;
	const file = input?.files?.[0];
	if (!file) return;
	form.logo = URL.createObjectURL(file);
}

function onLoginImagePick(event: Event) {
	const input = event.target as HTMLInputElement | null;
	const file = input?.files?.[0];
	if (!file) return;
	form.login_image = URL.createObjectURL(file);
}
</script>

<style scoped>
.table-image-preview {
	width: 140px;
	height: 78px;
	background: rgb(var(--v-theme-surface-variant));
	overflow: hidden;
}

.details-col {
	min-width: 400px;
	max-width: 400px;
	word-break: break-word;
}

.empty-images-state {
	min-height: 220px;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-align: center;
	padding: 24px;
}
</style>
