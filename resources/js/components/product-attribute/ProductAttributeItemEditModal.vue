<template>
  <v-card-text>
    <div class="text-caption text-medium-emphasis mb-1">Attribute Name</div>
    <v-text-field
      v-model="nameModel"
      variant="outlined"
      density="comfortable"
      maxlength="255"
      counter="255"
      hide-details
      class="mb-4" />

    <div class="text-caption text-medium-emphasis mb-1">Type</div>
    <v-select
      v-model="typeModel"
      :items="typeOptions"
      item-title="label"
      item-value="value"
      variant="outlined"
      density="comfortable"
      hide-details
      class="mb-4" />

    <div class="d-flex align-center ga-6 mb-4">
      <v-switch
        v-model="useForVariantModel"
        color="primary"
        hide-details
        inset>
        <template #label>Use For Variant</template>
      </v-switch>
      <v-switch
        v-model="useInFilterModel"
        color="primary"
        hide-details
        inset>
        <template #label>Use In Filter</template>
      </v-switch>
    </div>
  </v-card-text>

  <v-divider></v-divider>
  <v-card-actions class="justify-end pa-4">
    <div>
      <v-btn color="primary" variant="flat" class="px-4" :loading="saving" :disabled="saving" @click="onSave">
        <v-icon start size="16">mdi-content-save-outline</v-icon>
        Update
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { updateAttributeItem } from '@/api/attribute-products.api';
import { closeModal } from '@/shared/modal';
import { useSnackbarStore } from '@/stores/snackbar.store';

type AttributeRow = {
  id: number | string;
  name?: string | null;
  type?: string | null;
  use_for_variant?: boolean;
  use_in_filter?: boolean;
};

const props = defineProps<{
  classId: number | string;
  row: AttributeRow;
}>();

const emit = defineEmits<{
  (e: 'saved', payload: {
    id: number | string;
    name: string;
    type: 'text' | 'option';
    use_for_variant: boolean;
    use_in_filter: boolean;
  }): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const typeOptions = [
  { label: 'Text', value: 'text' },
  { label: 'Option', value: 'option' },
] as const;

const nameModel = ref(String(props.row?.name ?? '').trim());
const typeModel = ref<'text' | 'option'>(isOptionType(props.row?.type) ? 'option' : 'text');
const useForVariantModel = ref(Boolean(props.row?.use_for_variant));
const useInFilterModel = ref(Boolean(props.row?.use_in_filter));

function isOptionType(type: string | null | undefined): boolean {
  return String(type ?? '').trim().toLowerCase() === 'option';
}

async function onSave() {
  const normalizedName = nameModel.value.trim();
  if (!normalizedName) {
    snackbar.show({ message: 'Attribute name is required.', color: 'error' });
    return;
  }

  saving.value = true;
  try {
    const payload = {
      name: normalizedName,
      type: typeModel.value,
      use_for_variant: useForVariantModel.value,
      use_in_filter: useInFilterModel.value,
    } as const;

    const response = await updateAttributeItem(props.classId, props.row.id, payload);
    const saved = response?.data;

    emit('saved', {
      id: props.row.id,
      name: String(saved?.name ?? payload.name),
      type: (saved?.type === 'option' ? 'option' : 'text'),
      use_for_variant: Boolean(saved?.use_for_variant ?? payload.use_for_variant),
      use_in_filter: Boolean(saved?.use_in_filter ?? payload.use_in_filter),
    });

    snackbar.show({ message: response?.message || 'Attribute item updated successfully.', color: 'success' });
    closeModal();
  } catch (error: any) {
    const message = error?.response?.data?.message || error?.message || 'Failed to update attribute item.';
    snackbar.show({ message, color: 'error' });
  } finally {
    saving.value = false;
  }
}
</script>
