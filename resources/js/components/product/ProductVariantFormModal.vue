<template>
    <v-card-text class="pa-0">
        <v-form ref="formRef" class="pa-6">
            <v-row>
                <v-col cols="12" md="6">
                    <div class="mb-0">
                        <AppFieldLabel label="Price" />
                        <v-text-field
                            v-model="form.price"
                            type="number"
                            min="0"
                            variant="outlined"
                            density="comfortable"
                            :rules="[v => !!v || 'Price is required']"
                            :error-messages="getErrorMessages('price')"
                            hide-details="auto"
                            @update:model-value="clearFieldError('price')"
                        />
                    </div>
                </v-col>
                <v-col cols="12" md="6">
                    <div class="mb-0">
                        <AppFieldLabel label="Quantity" />
                        <v-text-field
                            v-model="form.quantity"
                            type="number"
                            min="0"
                            variant="outlined"
                            density="comfortable"
                            :rules="[v => !!v || 'Quantity is required']"
                            :error-messages="getErrorMessages('quantity')"
                            hide-details="auto"
                            @update:model-value="clearFieldError('quantity')"
                        />
                    </div>
                </v-col>
            </v-row>

            <div class="mt-6">
                <div class="mb-4">
                    <AppFieldLabel label="Variant Attributes" />
                    <div v-if="getErrorMessages('attributes').length" class="text-error text-caption mt-1">
                        {{ getErrorMessages('attributes')[0] }}
                    </div>
                </div>
                
                <v-table density="comfortable" class="border rounded">
                    <tbody>
                        <tr v-for="attribute in props.variantAttributes ?? []" :key="attribute.name">
                            <td style="width: 35%;" class="font-weight-medium text-body-2">
                                {{ attribute.name }}
                            </td>
                            <td>
                                <div class="py-2">
                                    <AppFieldSelect
                                        v-if="attribute.type === 'option' || attribute.type === 'select'"
                                        :model-value="attributeValues[attribute.name] ?? ''"
                                        @update:model-value="setAttributeValue(attribute.name, $event)"
                                        :items="attribute.values"
                                        :hide-details="true"
                                        :clearable="true"
                                        placeholder="Select option"
                                    />
                                    <AppFieldTextarea
                                        v-else
                                        :model-value="attributeValues[attribute.name] ?? ''"
                                        @update:model-value="setAttributeValue(attribute.name, $event)"
                                        placeholder="Enter value"
                                        :rows="1"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!(props.variantAttributes?.length)">
                            <td colspan="2" class="text-center text-medium-emphasis py-4">
                                No attributes available for this product's class.
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </div>
        </v-form>
    </v-card-text>

    <v-divider />
    <v-card-actions class="pa-4 ga-3">
        <v-spacer />
        <v-btn
            variant="tonal"
            color="secondary"
            :disabled="saving"
            @click="closeModal"
        >
            Cancel
        </v-btn>
        <v-btn
            color="primary"
            variant="flat"
            class="px-6"
            :loading="saving"
            @click="onSubmit"
        >
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            {{ isEditMode ? 'Update Variant' : 'Create Variant' }}
        </v-btn>
    </v-card-actions>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue';
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
const isEditMode = props.mode === 'edit' && props.variant?.id !== undefined;

const form = reactive({
    price: String(props.variant?.price ?? ''),
    quantity: String(props.variant?.quantity ?? ''),
});

const attributeValues = reactive<Record<string, string>>({});

// Initialize attribute values
onMounted(() => {
    const existingAttributes = (props.variant?.attributes && typeof props.variant.attributes === 'object')
        ? (props.variant.attributes as Record<string, any>)
        : {};

    (props.variantAttributes ?? []).forEach((attr) => {
        // If editing, use existing variant attribute value, else fallback to class assigned value
        const val = existingAttributes[attr.name] ?? attr.assigned_value ?? '';
        attributeValues[attr.name] = String(val).trim();
    });
});

function setAttributeValue(name: string, value: any) {
    attributeValues[name] = String(value ?? '').trim();
    clearFieldError('attributes');
}

function buildAttributesPayload(): Record<string, string> {
    const payload: Record<string, string> = {};
    Object.entries(attributeValues).forEach(([key, value]) => {
        if (value.trim()) {
            payload[key] = value.trim();
        }
    });
    return payload;
}

async function onSubmit() {
    fieldErrors.value = {};
    const { valid } = await formRef.value?.validate();
    if (!valid) return;

    saving.value = true;
    try {
        const payload = {
            price: Number(form.price),
            quantity: Number(form.quantity),
            attributes: buildAttributesPayload(),
        };

        const response = isEditMode
            ? await updateVariant(props.productId, String(props.variant?.id), payload)
            : await createVariant(props.productId, payload);

        snackbar.show({
            message: response?.message || (isEditMode ? 'Variant updated successfully' : 'Variant created successfully'),
            color: 'success'
        });

        emit('saved');
        closeModal();
    } catch (error: any) {
        const response = error?.response;
        const responseErrors = response?.data?.errors;
        
        if (response?.status === 422 && responseErrors) {
            fieldErrors.value = responseErrors;
        }

        snackbar.show({
            message: response?.data?.message || 'Something went wrong',
            color: 'error'
        });
    } finally {
        saving.value = false;
    }
}

function getErrorMessages(field: string): string[] {
    return fieldErrors.value[field] ?? [];
}

function clearFieldError(field: string) {
    if (fieldErrors.value[field]) {
        delete fieldErrors.value[field];
    }
}
</script>
