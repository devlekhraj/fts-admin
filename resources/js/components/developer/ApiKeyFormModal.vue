<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>
    <v-form ref="formRef" @submit.prevent="onSubmit">
      <v-row>
        <v-col cols="12" md="12" class="pb-0">
          <v-text-field
            v-model="form.host"
            label="Host"
            variant="outlined"
            density="comfortable"
            :rules="[rules.maxLength]"
            :error-messages="getErrorMessages('host')"
            prepend-inner-icon="mdi-web" />
        </v-col>
        <v-col cols="12" md="12" class="pb-0">
          <v-select
            v-model="form.mode"
            label="Mode"
            variant="outlined"
            density="comfortable"
            :items="modeOptions"
            item-title="label"
            item-value="value"
            :rules="[rules.required]"
            :error-messages="getErrorMessages('mode')"
            prepend-inner-icon="mdi-toggle-switch" />
        </v-col>
        <v-col cols="12" md="12" class="pb-0">
          <v-textarea
            v-model="form.description"
            label="Description"
            variant="outlined"
            density="comfortable"
            auto-grow
            rows="2"
            :rules="[rules.maxLength]"
            :error-messages="getErrorMessages('description')"
            prepend-inner-icon="mdi-text-box-outline" />
        </v-col>
        <v-col cols="12" md="12" class="pb-0">
          <v-switch
            v-model="form.is_active"
            color="primary"
            inset
            label="Active"
            hide-details />
        </v-col>
        <v-col cols="12" md="12" class="pb-0" v-if="hasKeys">
          <v-text-field
            v-model="form.test_public_key"
            label="Test Public Key"
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-key"
            readonly />
        </v-col>
        <v-col cols="12" md="12" class="pb-0" v-if="hasKeys">
          <v-text-field
            v-model="form.live_public_key"
            label="Live Public Key"
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-key-star"
            readonly />
        </v-col>
      </v-row>
    </v-form>
  </v-card-text>

  <v-card-actions class="pb-4">
    <div class="w-100 d-flex align-center justify-space-between px-4 pt-2">
      <v-btn
        icon
        size="small"
        variant="tonal"
        title="Reset form"
        color="warning"
        :disabled="loading"
        @click="resetForm"
        aria-label="Reset form">
        <v-icon>mdi-refresh</v-icon>
      </v-btn>
      <v-btn
        color="primary"
        variant="tonal"
        class="px-5"
        :loading="loading"
        :disabled="loading"
        @click="onSubmit">
        <v-icon start>mdi-content-save-outline</v-icon>
        {{ isEdit ? 'Update' : 'Create' }}
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { createApiKey, updateApiKey, type ApiKeyItem, type ApiKeyPayload } from '@/api/developer.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  mode: 'create' | 'edit';
  item?: ApiKeyItem | null;
}>();

const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const formRef = ref();
const error = ref('');
const fieldErrors = ref<Record<string, string[]>>({});
const loading = ref(false);
const snackbar = useSnackbarStore();

const form = ref<ApiKeyPayload & Partial<ApiKeyItem>>({
  host: '',
  mode: 'test',
  description: '',
  is_active: true,
  test_public_key: '',
  live_public_key: '',
});

const rules = {
  required: (value: unknown) => (value ? true : 'Required'),
  maxLength: (value: string) =>
    !value || value.length <= 255 ? true : 'Must be 255 characters or fewer',
};

const modeOptions = [
  { label: 'Test', value: 'test' },
  { label: 'Live', value: 'live' },
];

const isEdit = computed(() => props.mode === 'edit');
const hasKeys = computed(() => Boolean(form.value.test_public_key || form.value.live_public_key));

watch(
  () => props.item,
  (item) => {
    if (!item) return;
    form.value = {
      host: item.host ?? '',
      mode: (item.mode as 'test' | 'live') ?? 'test',
      description: item.description ?? '',
      is_active: item.is_active ?? true,
      test_public_key: item.test_public_key ?? '',
      live_public_key: item.live_public_key ?? '',
    };
  },
  { immediate: true },
);

function getErrorMessages(field: string) {
  return fieldErrors.value[field] ?? [];
}

function clearFieldError(field: string) {
  if (!fieldErrors.value[field]) return;
  const next = { ...fieldErrors.value };
  delete next[field];
  fieldErrors.value = next;
}

function resetForm() {
  if (isEdit.value && props.item) {
    form.value = {
      host: props.item.host ?? '',
      mode: (props.item.mode as 'test' | 'live') ?? 'test',
      description: props.item.description ?? '',
      is_active: props.item.is_active ?? true,
      test_public_key: props.item.test_public_key ?? '',
      live_public_key: props.item.live_public_key ?? '',
    };
  } else {
    form.value = {
      host: '',
      mode: 'test',
      description: '',
      is_active: true,
      test_public_key: '',
      live_public_key: '',
    };
  }
  error.value = '';
  fieldErrors.value = {};
  formRef.value?.resetValidation?.();
}

async function onSubmit() {
  error.value = '';
  fieldErrors.value = {};

  const result = await formRef.value?.validate?.();
  if (result && result.valid === false) return;
  if (!result && formRef.value?.validate) {
    const ok = await formRef.value.validate();
    if (!ok) return;
  }

  loading.value = true;
  try {
    const payload: ApiKeyPayload = {
      host: form.value.host ?? '',
      mode: form.value.mode as 'test' | 'live',
      description: form.value.description ?? '',
      is_active: form.value.is_active ?? true,
    };

    const saved = isEdit.value && props.item?.id
      ? await updateApiKey(props.item.id, payload)
      : await createApiKey(payload);

    const message = isEdit.value ? 'API key updated successfully.' : 'API key created successfully.';
    snackbar.show({ message, color: 'success' });
    emit('saved', saved);
    emit('close');
  } catch (err: any) {
    const response = err?.response;
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
      return;
    }
    const message = getErrorMessage(err);
    error.value = message;
  } finally {
    loading.value = false;
  }
}
</script>
