<template>
	<AppPageHeader title="Attribute Detail" subtitle="View product attribute information">
		<template #actions>
			<v-btn variant="tonal" color="primary" @click="goBack">
				<v-icon start>mdi-arrow-left</v-icon>
				Back
			</v-btn>
		</template>
	</AppPageHeader>

	<v-card class="pa-6">
		<div class="text-h6">{{ detail?.name || `Attribute #${attributeId || '-'}` }}</div>
		<div class="d-flex align-center ga-2 mt-2">
			<v-chip size="small" variant="tonal" color="primary" label>
				{{ Number(detail?.attributes_count ?? 0) }} attributes
			</v-chip>
			<span class="text-body-2 text-medium-emphasis">
				Created: {{ formatLongDate(detail?.created_at) ?? '-' }}
			</span>
		</div>

		<div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">
			Loading attribute detail...
		</div>

		<v-table v-else-if="attributeRows.length" class="mt-4" density="comfortable">
			<thead>
				<tr>
					<th style="min-width: 200px;">Name</th>
					<th style="min-width: 150px;">Type</th>
					<th style="min-width: 150px;">For Variant</th>
					<th style="min-width: 150px;">In Filter</th>
					<th style="min-width: 180px;">Values</th>
					<th style="min-width: 100px;">Action</th>
				</tr>
			</thead>
			<tbody>
				<template v-for="row in attributeRows" :key="String(row.id)">
					<tr>
						<td><span style="font-size: 0.8rem;">{{ row.name || '-' }}</span></td>
						<td>
							<v-chip size="small" label variant="tonal" :color="typeMeta(row.type).color">
								{{ typeMeta(row.type).label }}
							</v-chip>
						</td>
						<td>
							<v-chip v-if="row.use_for_variant" size="small" label variant="tonal"
								color="success">Yes</v-chip>
							<v-chip v-else size="small" label variant="tonal" color="error">No</v-chip>
						</td>
						<td>
							<v-chip v-if="row.use_in_filter" size="small" label variant="tonal"
								color="success">Yes</v-chip>
							<v-chip v-else size="small" label variant="tonal" color="error">No</v-chip>
						</td>
						<td>
							<v-btn size="small" variant="tonal" color="primary" @click="toggleExpanded(row)">
								<v-icon size="16">{{ isExpanded(row) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
								{{ valuesCountForRow(row) }} values
							</v-btn>
						</td>
						<td>
							<v-btn class="mr-3" size="x-small" variant="tonal" color="primary" icon
								@click="toggleExpanded(row)">
								<v-icon size="16">{{ isExpanded(row) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
							</v-btn>
							<v-btn class="mr-3" size="x-small" variant="tonal" color="primary" icon
								@click="openEditModal(row)">
								<v-icon size="16">mdi-square-edit-outline</v-icon>
							</v-btn>
							<v-btn size="x-small" variant="tonal" color="error" icon>
								<v-icon size="16">mdi-delete-outline</v-icon>
							</v-btn>
						</td>
					</tr>
					<tr v-if="isExpanded(row)">
						<td colspan="6" class="py-6 pb-14">
							<v-container>
								<v-row>
									<v-col cols="12" md="8" offset-md="2">
										<div>
											<div class="text-caption text-medium-emphasis mb-2">Values</div>
											<template v-if="isOptionType(row.type)">
												<div class="d-flex align-center ga-2">
													<v-text-field :model-value="getOptionInput(row)"
														@update:model-value="setOptionInput(row, $event)"
														variant="outlined" density="comfortable" hide-details
														placeholder="Add value" @keyup.enter="addOptionValue(row)" />
													<v-btn color="primary" icon variant="tonal"
														@click="addOptionValue(row)">
														<v-icon>mdi-plus</v-icon>
													</v-btn>
												</div>
												<div class="d-flex flex-wrap ga-2 mt-4">
													<v-chip v-for="(value, index) in getOptionValues(row)"
														:key="`${row.id}-${value}-${index}`" size="small" label closable
														color="primary" variant="tonal"
														@click:close="removeOptionValue(row, index)">
														{{ value }}
													</v-chip>
													<span v-if="!getOptionValues(row).length"
														class="text-body-2 text-medium-emphasis">-</span>
												</div>
											</template>
											<template v-else-if="isTextType(row.type)">
												<v-textarea :model-value="getTextValue(row)"
													@update:model-value="setTextValue(row, $event)" rows="2"
													variant="outlined" density="comfortable" auto-grow maxlength="100"
													counter="100" hide-details />
											</template>
											<template v-else>
												<div class="text-body-2">{{ formatValues(row.values) }}</div>
											</template>
											<div class="d-flex justify-end mt-4">
												<v-btn color="primary" variant="flat" :loading="isSavingRow(row)"
													:disabled="isSavingRow(row)" @click="saveRowValues(row)">
													<v-icon start size="16">mdi-content-save-outline</v-icon>
													Save Values
												</v-btn>
											</div>
										</div>
									</v-col>
								</v-row>
							</v-container>
						</td>
					</tr>
				</template>
			</tbody>
		</v-table>
		<div v-else class="text-body-2 text-medium-emphasis mt-4">No attribute items found.</div>
	</v-card>

</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import ProductAttributeItemEditModal from '@/components/product-attribute/ProductAttributeItemEditModal.vue';
import {
	getAttributeProductDetail,
	updateAttributeValues,
	type AttributeDetailItem,
	type AttributeProductDetailResponse,
} from '@/api/attribute-products.api';
import { openModal } from '@/shared/modal';
import { formatLongDate } from '@/shared/utils';
import { useSnackbarStore } from '@/stores/snackbar.store';

const route = useRoute();
const router = useRouter();
const snackbar = useSnackbarStore();

const attributeId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const detail = ref<AttributeProductDetailResponse | null>(null);
const expandedRowIds = ref<Set<string>>(new Set());
const savingRowIds = ref<Set<string>>(new Set());
const optionValuesByRow = ref<Record<string, string[]>>({});
const optionInputByRow = ref<Record<string, string>>({});
const textValueByRow = ref<Record<string, string>>({});
const attributeRows = computed<AttributeDetailItem[]>(() =>
	Array.isArray(detail.value?.attributes) ? detail.value.attributes : [],
);

async function fetchDetail() {
	if (!attributeId.value) return;
	loading.value = true;
	try {
		detail.value = await getAttributeProductDetail(attributeId.value);
	} finally {
		loading.value = false;
	}
}

function goBack() {
	router.push({ name: 'admin.product.attributes' });
}

function isExpanded(row: AttributeDetailItem): boolean {
	return expandedRowIds.value.has(String(row.id));
}

function toggleExpanded(row: AttributeDetailItem) {
	const id = String(row.id);
	const next = new Set(expandedRowIds.value);
	if (next.has(id)) {
		next.delete(id);
	} else {
		ensureRowEditorState(row);
		next.add(id);
	}
	expandedRowIds.value = next;
}

function openEditModal(row: AttributeDetailItem) {
	ensureRowEditorState(row);
	openModal(
		ProductAttributeItemEditModal,
		{
			classId: attributeId.value,
			row: {
				id: row.id,
				name: row.name,
				type: row.type,
				use_for_variant: row.use_for_variant,
				use_in_filter: row.use_in_filter,
				values: getRowValuesForSave(row),
			},
		},
		{
			title: 'Edit Attribute Item',
			size: 'lg',
			onSaved: (payload?: unknown) => {
				const data = payload as {
					id?: number | string;
					name?: string;
					type?: string;
					use_for_variant?: boolean;
					use_in_filter?: boolean;
					values?: unknown;
				} | undefined;
				const rowId = String(data?.id ?? row.id);
				if (Array.isArray(data?.values)) {
					const savedValues = normalizeValuesArray(data?.values);
					updateLocalRowValues(rowId, savedValues);
					optionValuesByRow.value[rowId] = [...savedValues];
					textValueByRow.value[rowId] = savedValues[0] ?? '';
				}
				const target = detail.value?.attributes?.find((item) => String(item.id) === rowId);
				if (target) {
					if (typeof data?.name === 'string') target.name = data.name;
					if (typeof data?.type === 'string') target.type = data.type;
					if (typeof data?.use_for_variant === 'boolean') target.use_for_variant = data.use_for_variant;
					if (typeof data?.use_in_filter === 'boolean') target.use_in_filter = data.use_in_filter;
				}
			},
		},
	);
}

function ensureRowEditorState(row: AttributeDetailItem) {
	const id = String(row.id);
	if (optionValuesByRow.value[id] === undefined) {
		optionValuesByRow.value[id] = normalizeValuesArray(row.values);
	}
	if (optionInputByRow.value[id] === undefined) {
		optionInputByRow.value[id] = '';
	}
	if (textValueByRow.value[id] === undefined) {
		textValueByRow.value[id] = normalizeValuesArray(row.values).join(', ');
	}
}

function typeMeta(type: string | null | undefined): { label: string; icon: string; color: string } {
	const key = String(type ?? '').trim().toLowerCase();
	if (key === 'text') {
		return { label: 'Text', icon: 'mdi-form-textbox', color: 'primary' };
	}
	if (key === 'option') {
		return { label: 'Option', icon: 'mdi-format-list-bulleted', color: 'indigo' };
	}

	return {
		label: key ? key.charAt(0).toUpperCase() + key.slice(1) : '-',
		icon: 'mdi-shape-outline',
		color: 'grey',
	};
}

function formatValues(values: unknown): string {
	if (!Array.isArray(values)) {
		return '-';
	}

	const normalized = values
		.map((value) => String(value ?? '').trim())
		.filter((value) => value.length > 0);

	return normalized.length ? normalized.join(', ') : '-';
}

function valuesCountForRow(row: AttributeDetailItem): number {
	return getRowValuesForSave(row).length;
}

function isOptionType(type: string | null | undefined): boolean {
	return String(type ?? '').trim().toLowerCase() === 'option';
}

function isTextType(type: string | null | undefined): boolean {
	return String(type ?? '').trim().toLowerCase() === 'text';
}

function normalizeValuesArray(values: unknown): string[] {
	if (!Array.isArray(values)) return [];
	return values
		.map((value) => String(value ?? '').trim())
		.filter((value) => value.length > 0);
}

function getOptionValues(row: AttributeDetailItem): string[] {
	const id = String(row.id);
	ensureRowEditorState(row);
	return optionValuesByRow.value[id] ?? [];
}

function getOptionInput(row: AttributeDetailItem): string {
	const id = String(row.id);
	ensureRowEditorState(row);
	return optionInputByRow.value[id] ?? '';
}

function setOptionInput(row: AttributeDetailItem, value: unknown) {
	const id = String(row.id);
	optionInputByRow.value[id] = String(value ?? '');
}

function addOptionValue(row: AttributeDetailItem) {
	const id = String(row.id);
	const nextValue = (optionInputByRow.value[id] ?? '').trim();
	if (nextValue === '') return;
	const existing = optionValuesByRow.value[id] ?? [];
	if (!existing.includes(nextValue)) {
		optionValuesByRow.value[id] = [...existing, nextValue];
	}
	optionInputByRow.value[id] = '';
}

function removeOptionValue(row: AttributeDetailItem, index: number) {
	const id = String(row.id);
	const existing = optionValuesByRow.value[id] ?? [];
	if (index < 0 || index >= existing.length) return;
	const next = [...existing];
	next.splice(index, 1);
	optionValuesByRow.value[id] = next;
}

function getTextValue(row: AttributeDetailItem): string {
	const id = String(row.id);
	ensureRowEditorState(row);
	return textValueByRow.value[id] ?? '';
}

function setTextValue(row: AttributeDetailItem, value: unknown) {
	const id = String(row.id);
	textValueByRow.value[id] = String(value ?? '').slice(0, 100);
}

function isSavingRow(row: AttributeDetailItem): boolean {
	return savingRowIds.value.has(String(row.id));
}

function getRowValuesForSave(row: AttributeDetailItem): string[] {
	const id = String(row.id);
	if (isOptionType(row.type)) {
		return [...(optionValuesByRow.value[id] ?? normalizeValuesArray(row.values))];
	}
	if (isTextType(row.type)) {
		const value = (textValueByRow.value[id] ?? normalizeValuesArray(row.values).join(', ')).trim();
		return value === '' ? [] : [value.slice(0, 100)];
	}
	return normalizeValuesArray(row.values);
}

function updateLocalRowValues(rowId: string, values: string[]) {
	if (!detail.value?.attributes) return;
	const target = detail.value.attributes.find((row) => String(row.id) === rowId);
	if (!target) return;
	target.values = values;
}

async function saveRowValues(row: AttributeDetailItem): Promise<boolean> {
	if (!attributeId.value) return false;

	const rowId = String(row.id);
	const values = getRowValuesForSave(row);

	const nextSaving = new Set(savingRowIds.value);
	nextSaving.add(rowId);
	savingRowIds.value = nextSaving;

	try {
		const response = await updateAttributeValues(attributeId.value, row.id, { values });
		const savedValues = Array.isArray(response?.data?.values) ? response.data.values : values;
		updateLocalRowValues(rowId, savedValues);
		optionValuesByRow.value[rowId] = [...savedValues];
		textValueByRow.value[rowId] = savedValues[0] ?? '';
		snackbar.show({ message: response?.message || 'Values updated successfully.', color: 'success' });
		return true;
	} catch (error: any) {
		const message =
			error?.response?.data?.message ||
			error?.message ||
			'Failed to update values.';
		snackbar.show({ message, color: 'error' });
		return false;
	} finally {
		const done = new Set(savingRowIds.value);
		done.delete(rowId);
		savingRowIds.value = done;
	}
}

onMounted(fetchDetail);
</script>
