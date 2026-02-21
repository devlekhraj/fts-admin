<template>
	<v-card-text class="py-6">
		<v-form ref="formRef" @submit.prevent="onSubmit">
			<v-row>
				<v-col cols="12" md="6" class="pb-0">
					<v-text-field
						v-model="form.label"
						label="Label"
						variant="outlined"
						density="comfortable"
						:rules="[rules.required]"
					/>
				</v-col>
				<v-col cols="12" md="6" class="pb-0">
					<v-text-field
						v-model="form.code"
						label="Code"
						variant="outlined"
						density="comfortable"
						:rules="[rules.required, rules.code]"
					/>
				</v-col>
				<v-col cols="12" md="6" class="pb-0">
					<v-select
						v-model="form.type"
						:items="typeOptions"
						label="Type"
						variant="outlined"
						density="comfortable"
						:rules="[rules.required]"
					/>
				</v-col>
				<v-col cols="12" class="pb-0">
					<v-text-field
						v-if="form.type !== 'richtext'"
						v-model="form.value"
						label="Value"
						variant="outlined"
						density="comfortable"
						:rules="[rules.required]"
					/>
					<div v-else class="mt-1">
						<div class="text-body-2 text-medium-emphasis mb-1">Value</div>
						<RichText v-model="form.value" />
					</div>
				</v-col>
			</v-row>
		</v-form>
	</v-card-text>

	<v-card-actions class="pb-4">
		<div class="w-100 d-flex align-center justify-space-between px-4 pt-2">
			<v-btn variant="text" color="secondary" @click="emit('close')">Cancel</v-btn>
			<v-btn color="primary" variant="tonal" class="px-5" @click="onSubmit">
				<v-icon start>mdi-content-save-outline</v-icon>
				Save
			</v-btn>
		</div>
	</v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const formRef = ref();
const form = ref({
	label: '',
	code: '',
	type: '',
	value: '',
});

const rules = {
	required: (value: unknown) => (value ? true : 'Required'),
	code: (value: unknown) => {
		if (!value) return true;
		const text = String(value);
		if (/\s/.test(text)) return 'Use underscores instead of spaces';
		return /^[a-z0-9_]+$/i.test(text) ? true : 'Only letters, numbers, and underscores are allowed';
	},
};

const typeOptions = ['text', 'number', 'boolean', 'richtext'];

async function onSubmit() {
	const result = await formRef.value?.validate?.();
	if (result && result.valid === false) return;
	if (!result && formRef.value?.validate) {
		const ok = await formRef.value.validate();
		if (!ok) return;
	}

	const payload = {
		label: form.value.label,
		code: form.value.code,
		type: form.value.type,
		value: form.value.value,
	};
	emit('saved', payload);
	emit('close');
}
</script>
