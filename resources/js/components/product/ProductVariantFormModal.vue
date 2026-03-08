<template>
    <v-card-text>
        <v-form ref="formRef" class="pt-2">
            <v-row class="mb-1">
                <v-col cols="12" md="6">
                    <div class="mb-4">
                        <AppFieldLabel label="Price" />
                        <v-text-field v-model="form.price" type="number" min="0" variant="outlined"
                            density="comfortable" :rules="[requiredRule]" :error-messages="getErrorMessages('price')"
                            hide-details="auto" @update:model-value="clearFieldError('price')" />
                    </div>
                    <div class="mb-4">
                        <AppFieldLabel label="Quantity" />
                        <v-text-field v-model="form.quantity" type="number" min="0" variant="outlined"
                            density="comfortable" :rules="[requiredRule]" :error-messages="getErrorMessages('quantity')"
                            hide-details="auto" @update:model-value="clearFieldError('quantity')" />
                    </div>
                </v-col>
                <v-col cols="12" md="6">
                </v-col>
            </v-row>

            <div class="pb-6">
                <div>
                    <AppFieldLabel label="Attributes" />
                </div>
                <div>
                    <div v-if="getErrorMessages('attributes').length" class="text-error text-caption mt-2">
                        {{ getErrorMessages('attributes')[0] }}
                    </div>
                </div>
                <v-table density="comfortable" class="rounded">
                    <tbody>
                        <tr v-for="attribute in props.variantAttributes ?? []" :key="attribute.name">
                            <td style="width: 30%;" class="font-weight-medium">{{ attribute.name }}</td>
                            <td>
                                <div class="py-3">
                                    <AppFieldSelect v-if="attribute.type === 'option'"
                                        :model-value="attributeValues[attribute.name] ?? ''"
                                        @update:model-value="setAttributeValue(attribute.name, $event)"
                                        :items="attribute.values" :hide-details="true" :clearable="true" />
                                    <AppFieldTextarea v-else :model-value="attributeValues[attribute.name] ?? ''"
                                        @update:model-value="setAttributeValue(attribute.name, $event)"
                                        placeholder="Enter value" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </v-table>

            </div>
        </v-form>
    </v-card-text>

    <v-divider />
    <v-card-actions class="justify-end pa-4">
        <v-btn color="primary" variant="flat" class="px-4" :loading="saving" :disabled="saving" @click="onSubmit">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            {{ isEditMode ? 'Update Variant' : 'Add Variant' }}
        </v-btn>
    </v-card-actions>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import AppFieldSelect from '@/components/shared/AppFieldSelect.vue';
import AppFieldTextarea from '@/components/shared/AppFieldTextarea.vue';
import { createVariant, type ProductVariantItem, updateVariant } from '@/api/products.api';
import { closeModal } from '@/shared/modal';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
    productId: string | number;
    mode?: 'create' | 'edit';
    variant?: ProductVariantItem | null;
    variantAttributes?: Array<{
        name: string;
        type: string;
        values: string[];
        assigned_value: string;
    }>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved', payload?: unknown): void;
}>();

const snackbar = useSnackbarStore();
const formRef = ref();
const saving = ref(false);
const fieldErrors = ref<Record<string, string[]>>({});
const isEditMode = props.mode === 'edit' && props.variant?.id !== undefined && props.variant?.id !== null;
const form = reactive({
    price: String(props.variant?.price ?? ''),
    quantity: String(props.variant?.quantity ?? ''),
});
const attributeValues = reactive<Record<string, string>>({});
const variantAttributeMap =
    props.variant?.attributes && typeof props.variant.attributes === 'object' && !Array.isArray(props.variant.attributes)
        ? (props.variant.attributes as Record<string, unknown>)
        : {};

(props.variantAttributes ?? []).forEach((attribute) => {
    const variantValue = variantAttributeMap[attribute.name];
    const fallbackValue = attribute.assigned_value;
    attributeValues[attribute.name] = String((variantValue ?? fallbackValue) ?? '').trim();
});

const requiredRule = (value: unknown) => (String(value ?? '').trim().length > 0 ? true : 'This field is required.');

function setAttributeValue(name: string, value: unknown) {
    attributeValues[name] = String(value ?? '').trim();
    clearFieldError('attributes');
}

function buildAttributesPayload(): Record<string, string> {
    return Object.entries(attributeValues).reduce<Record<string, string>>((acc, [key, value]) => {
        const normalizedKey = String(key ?? '').trim();
        const normalizedValue = String(value ?? '').trim();
        if (!normalizedKey || !normalizedValue) return acc;
        acc[normalizedKey] = normalizedValue;
        return acc;
    }, {});
}

async function onSubmit() {
    fieldErrors.value = {};
    const result = await formRef.value?.validate();
    if (!result?.valid) return;

    const attributes = buildAttributesPayload();

    saving.value = true;
    try {
        const payload = {
            price: Number(form.price),
            quantity: Number(form.quantity),
            attributes,
        };
        const response = isEditMode
            ? await updateVariant(props.productId, String(props.variant?.id), payload)
            : await createVariant(props.productId, payload);
        emit('saved');
        snackbar.show({ message: response?.message || (isEditMode ? 'Variant updated successfully.' : 'Variant created successfully.'), color: 'success' });
        closeModal();
    } catch (error: any) {
        const response = error?.response;
        const responseErrors = response?.data?.errors ?? null;
        if (response?.status === 422 && responseErrors && typeof responseErrors === 'object') {
            const next: Record<string, string[]> = {};
            for (const [key, messages] of Object.entries(responseErrors)) {
                if (Array.isArray(messages)) {
                    next[key] = messages.map((item) => String(item));
                } else if (messages != null) {
                    next[key] = [String(messages)];
                }
            }
            fieldErrors.value = next;
        }
        const message = error?.response?.data?.message || error?.message || (isEditMode ? 'Failed to update variant.' : 'Failed to create variant.');
        snackbar.show({ message, color: 'error' });
    } finally {
        saving.value = false;
    }
}

function getErrorMessages(field: string) {
    return fieldErrors.value[field] ?? [];
}

function clearFieldError(field: string) {
    if (!fieldErrors.value[field]) return;
    const next = { ...fieldErrors.value };
    delete next[field];
    fieldErrors.value = next;
}
</script>
