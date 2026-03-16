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

				<v-divider class="my-6"></v-divider>

				<div class="d-flex justify-end">
					<v-btn
						color="primary"
						variant="flat"
						:loading="loading"
						prepend-icon="mdi-content-save-outline"
						@click="onUpdate">
						Update Settings
					</v-btn>
				</div>
			</v-col>
		</v-row>
	</v-card>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { updateSettings } from '@/api/settings.api';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

const props = defineProps<{ data?: any }>();
const snackbar = useSnackbarStore();
const loading = ref(false);

const form = reactive({
	facebook: '',
	instagram: '',
	twitter: '',
	linkedin: '',
	google: '',
	youtube: '',
});

function initForm() {
	if (props.data) {
		form.facebook = props.data.facebook || '';
		form.instagram = props.data.instagram || '';
		form.twitter = props.data.twitter || '';
		form.linkedin = props.data.linkedin || '';
		form.google = props.data.google || '';
		form.youtube = props.data.youtube || '';
	}
}

watch(
	() => props.data,
	() => initForm(),
	{ immediate: true }
);

async function onUpdate() {
	loading.value = true;
	try {
		await updateSettings('social_profiles', { settings: { ...form } });
		snackbar.show({ message: 'Social profiles updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
