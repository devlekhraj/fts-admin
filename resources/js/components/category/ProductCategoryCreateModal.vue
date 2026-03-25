<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>
    <v-form ref="formRef" @submit.prevent="onSubmit">
      <v-row>
        <v-col cols="12" md="12" class="pb-0">
          <v-text-field
            v-model="form.title"
            label="Title"
            variant="outlined"
            density="comfortable"
            :rules="[rules.required, rules.maxLength]"
            :error-messages="getErrorMessages('title')"
            prepend-inner-icon="mdi-text"
            @update:model-value="onTitleInput" />
        </v-col>
        <v-col cols="12" md="12" class="pb-0">
          <v-text-field
            v-model="form.slug"
            label="Slug"
            variant="outlined"
            density="comfortable"
            :rules="[rules.required, rules.noSpaces, rules.maxLength]"
            :error-messages="getErrorMessages('slug')"
            prepend-inner-icon="mdi-link-variant"
            @update:model-value="onSlugInput" />
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
        Create
      </v-btn>
    </div>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { createProductCategory } from '@/api/product-categories.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const formRef = ref();
const error = ref('');
const form = ref({
  title: '',
  slug: '',
});
const slugEdited = ref(false);
const fieldErrors = ref<Record<string, string[]>>({});
const loading = ref(false);
const snackbar = useSnackbarStore();

const rules = {
  required: (value: unknown) => (value ? true : 'Required'),
  noSpaces: (value: string) => (!value || !/\\s/.test(value) ? true : 'Slug must not contain spaces'),
  maxLength: (value: string) =>
    !value || value.length <= 255 ? true : 'Must be 255 characters or fewer',
};

function getErrorMessages(field: string) {
  return fieldErrors.value[field] ?? [];
}

function clearFieldError(field: string) {
  if (!fieldErrors.value[field]) return;
  const next = { ...fieldErrors.value };
  delete next[field];
  fieldErrors.value = next;
}

function toSlug(value: string) {
  return value
    .trim()
    .toLowerCase()
    .replace(/\\s+/g, '-')
    .replace(/[^a-z0-9-]/gi, '');
}

function onTitleInput(value: string) {
  clearFieldError('title');
  if (!slugEdited.value && typeof value === 'string') {
    form.value.slug = toSlug(value);
  }
}

function onSlugInput(value: string) {
  clearFieldError('slug');
  slugEdited.value = true;
  if (typeof value === 'string') {
    form.value.slug = toSlug(value);
  }
}

function resetForm() {
  form.value = {
    title: '',
    slug: '',
  };
  slugEdited.value = false;
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
    const payload = { ...form.value };
    const response = await createProductCategory(payload);
    const message = (response as any)?.data?.message || 'Product category created successfully.';
    const savedData = (response as any)?.data ?? payload;
    snackbar.show({ message, color: 'success' });
    emit('saved', savedData);
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
