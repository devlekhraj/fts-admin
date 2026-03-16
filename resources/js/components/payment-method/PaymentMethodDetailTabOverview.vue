<template>
  <div class="pa-6 pt-10">
    <v-form ref="formRef" @submit.prevent="onUpdate">
      <v-row>
        <v-col cols="12" md="8" offset-md="2" lg="6" offset-lg="3">
          <div class="d-flex align-center justify-space-between mb-6">
            <div>
              <div class="text-h6 mb-1">Payment Method Overview</div>
              <div class="text-body-2 text-medium-emphasis">
                Basic information and status flags.
              </div>
            </div>
            <v-btn color="primary" variant="flat" :loading="loading" @click="onUpdate">
              <v-icon start size="16">mdi-content-save-outline</v-icon>
              Update
            </v-btn>
          </div>

          <v-row>
            <v-col cols="12">
              <AppFieldLabel label="Name" />
              <v-text-field
                v-model="form.name"
                variant="outlined"
                density="comfortable"
                :rules="[(v: string) => !!v || 'Name is required']"
                :error-messages="fieldErrors.name"
                @update:model-value="clearFieldError('name')"
                placeholder="Enter payment method name"
              />
            </v-col>
            <v-col cols="12">
              <AppFieldLabel label="Slug" />
              <v-text-field
                v-model="form.slug"
                variant="outlined"
                density="comfortable"
                :rules="[(v: string) => !!v || 'Slug is required']"
                :error-messages="fieldErrors.slug"
                @update:model-value="clearFieldError('slug')"
                placeholder="payment-method-slug"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <AppFieldLabel label="Status" />
              <v-select
                v-model="form.status"
                :items="statusItems"
                item-title="label"
                item-value="value"
                variant="outlined"
                density="comfortable"
                :error-messages="fieldErrors.status"
                @update:model-value="clearFieldError('status')"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <AppFieldLabel label="Test Mode" />
              <v-select
                v-model="form.test_mode"
                :items="testModeItems"
                item-title="label"
                item-value="value"
                variant="outlined"
                density="comfortable"
                :error-messages="fieldErrors.test_mode"
                @update:model-value="clearFieldError('test_mode')"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <AppFieldLabel label="International" />
              <v-select
                v-model="form.is_international"
                :items="internationalItems"
                item-title="label"
                item-value="value"
                variant="outlined"
                density="comfortable"
                :error-messages="fieldErrors.is_international"
                @update:model-value="clearFieldError('is_international')"
              />
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-form>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { PaymentMethodDetailResponse } from '@/api/payment-methods.api';
import { updatePaymentMethod } from '@/api/payment-methods.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: PaymentMethodDetailResponse | null;
}>();

const emit = defineEmits(['updated']);

const snackbar = useSnackbarStore();
const formRef = ref<any>(null);
const loading = ref(false);
const fieldErrors = ref<Record<string, string[]>>({});

const form = ref({
  name: '',
  slug: '',
  status: true,
  test_mode: false,
  is_international: false,
});

watch(
  () => props.item,
  (newItem) => {
    if (newItem) {
      form.value = {
        name: newItem.name ?? '',
        slug: newItem.slug ?? '',
        status: Boolean(newItem.status),
        test_mode: Boolean(newItem.test_mode),
        is_international: Boolean(newItem.is_international),
      };
    }
  },
  { immediate: true }
);

const statusItems = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false },
];

const testModeItems = [
  { label: 'Enabled', value: true },
  { label: 'Disabled', value: false },
];

const internationalItems = [
  { label: 'Yes', value: true },
  { label: 'No', value: false },
];

function clearFieldError(field: string) {
  if (fieldErrors.value[field]) {
    delete fieldErrors.value[field];
  }
}

async function onUpdate() {
  if (!props.item?.id) return;

  const { valid } = await formRef.value.validate();
  if (!valid) return;

  loading.value = true;
  fieldErrors.value = {};

  try {
    await updatePaymentMethod(props.item.id, form.value);
    snackbar.show({
      message: 'Payment method updated successfully',
      color: 'success',
    });
    emit('updated');
  } catch (error: any) {
    if (error.response?.status === 422) {
      fieldErrors.value = error.response.data.errors || {};
    }
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update payment method',
      color: 'error',
    });
  } finally {
    loading.value = false;
  }
}
</script>
