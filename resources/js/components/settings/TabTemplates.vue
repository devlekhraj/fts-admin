<template>
	<v-card class="pa-4" variant="flat">
		<v-row>
			<v-col cols="12" lg="10" offset-lg="1">
				<div class="pt-4">
					<div class="text-h6 mb-1">Message Templates</div>
					<div class="text-body-2 text-medium-emphasis mb-4">
						Configure order status notification templates using placeholder variables.
					</div>
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Placed</div>
					<v-textarea v-model="form.temp_order_placed" density="comfortable" variant="outlined" auto-grow
						rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Canceled</div>
					<v-textarea v-model="form.temp_order_canceled" density="comfortable" variant="outlined" auto-grow
						rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Completed</div>
					<v-textarea v-model="form.temp_order_completed" density="comfortable" variant="outlined" auto-grow
						rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Confirmed</div>
					<v-textarea v-model="form.temp_order_confirmed" density="comfortable" variant="outlined" auto-grow
						rows="3" />
				</div>
				<div>
					<div class="text-body-2 text-medium-emphasis mb-1">Dispatched</div>
					<v-textarea v-model="form.temp_order_dispatched" density="comfortable" variant="outlined" auto-grow
						rows="3" />
				</div>

				<v-divider class="my-6"></v-divider>

				<div class="d-flex justify-end">
					<v-btn color="primary" variant="flat" :loading="loading" prepend-icon="mdi-content-save-outline"
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
	temp_order_placed: '',
	temp_order_canceled: '',
	temp_order_completed: '',
	temp_order_confirmed: '',
	temp_order_dispatched: '',
});

function initForm() {
	if (props.data) {
		form.temp_order_placed = props.data.temp_order_placed || '';
		form.temp_order_canceled = props.data.temp_order_canceled || '';
		form.temp_order_completed = props.data.temp_order_completed || '';
		form.temp_order_confirmed = props.data.temp_order_confirmed || '';
		form.temp_order_dispatched = props.data.temp_order_dispatched || '';
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
		await updateSettings('templates', { settings: { ...form } });
		snackbar.show({ message: 'Message templates updated successfully', color: 'success' });
	} catch (err) {
		const message = getErrorMessage(err);
		snackbar.show({ message, color: 'error' });
	} finally {
		loading.value = false;
	}
}
</script>
