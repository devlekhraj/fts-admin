<template>
  <div class="d-flex align-center ga-2">
    <v-switch
      class="inline-boolean-switch"
      :model-value="currentValue"
      :loading="saving"
      :disabled="saving"
      color="primary"
      density="compact"
      inset
      hide-details
      @update:model-value="onToggle" />
    <!-- <v-chip size="small" label variant="tonal" :color="currentColor">
      {{ currentLabel }}
    </v-chip> -->
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { update as updateProduct } from '@/api/products.api';
import { useSnackbarStore } from '@/stores/snackbar.store';

type ToggleField = 'status' | 'emi_enabled';

const fieldMeta: Record<ToggleField, {
  enabledLabel: string;
  disabledLabel: string;
  enabledColor: string;
  disabledColor: string;
  successMessage: string;
  failureMessage: string;
}> = {
  status: {
    enabledLabel: 'Active',
    disabledLabel: 'Inactive',
    enabledColor: 'success',
    disabledColor: 'warning',
    successMessage: 'Product status updated successfully.',
    failureMessage: 'Failed to update product status',
  },
  emi_enabled: {
    enabledLabel: 'Enabled',
    disabledLabel: 'Disabled',
    enabledColor: 'success',
    disabledColor: 'grey',
    successMessage: 'Product EMI updated successfully.',
    failureMessage: 'Failed to update product EMI setting',
  },
};

const props = withDefaults(defineProps<{
  product: Record<string, unknown>;
  field: ToggleField;
}>(), {
});

const snackbar = useSnackbarStore();
const saving = ref(false);
const meta = computed(() => fieldMeta[props.field]);

const currentValue = computed(() => Boolean(props.product?.[props.field]));
const currentLabel = computed(() => (currentValue.value ? meta.value.enabledLabel : meta.value.disabledLabel));
const currentColor = computed(() => (currentValue.value ? meta.value.enabledColor : meta.value.disabledColor));

async function onToggle(value: unknown) {
  const nextValue = Boolean(value);
  if (nextValue === currentValue.value || saving.value) return;

  const productId = String(props.product?.id ?? '').trim();
  if (!productId) return;

  saving.value = true;

  try {
    await updateProduct(productId, {
      [props.field]: nextValue,
    });

    (props.product as Record<string, unknown>)[props.field] = nextValue;

    snackbar.show({
      message: meta.value.successMessage,
      color: 'success',
    });
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || meta.value.failureMessage,
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.inline-boolean-switch {
  transform: scale(0.82);
  transform-origin: center left;
  margin: 0;
}
</style>
