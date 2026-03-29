<template>
  <v-form ref="formRef" @submit.prevent="onSubmit">
    <v-card-text class="pt-4 pb-0">
      <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
        {{ error }}
      </v-alert>

      <v-row class="align-start">
        <v-col cols="12" md="4" class="d-flex justify-center">
          <div class="logo-preview" @click="triggerFilePick">
            <v-avatar size="128" rounded="lg" color="grey-lighten-3" class="logo-avatar">
              <v-img v-if="logoPreview" :src="logoPreview" cover />
              <v-icon v-else size="36" color="grey-darken-1">mdi-image-plus</v-icon>
            </v-avatar>
            <div class="text-caption text-medium-emphasis mt-1 text-center">Upload logo</div>
          </div>
          <input ref="fileInputRef" type="file" accept="image/*" class="d-none" @change="onFileChange" />
        </v-col>
        <v-col cols="12" md="8">
          <v-text-field
            v-model="form.name"
            label="Bank Name"
            placeholder="Nabil Bank"
            variant="outlined"
            density="comfortable"
            :rules="[requiredRule]"
            class="mb-3"
          />
          <v-text-field
            v-model="form.code"
            label="Code"
            placeholder="NABIL"
            variant="outlined"
            density="comfortable"
            :rules="[requiredRule]"
            class="mb-3 text-uppercase"
          />
        </v-col>
      </v-row>
    </v-card-text>
    <v-card-actions class="pb-4 px-4">
      <v-spacer />
      <v-btn variant="text" @click="emit('close')">Cancel</v-btn>
      <v-btn color="primary" :loading="submitting" type="submit">{{ submitLabel }}</v-btn>
    </v-card-actions>
  </v-form>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { createEmiBank, updateEmiBank } from '@/api/emi-banks.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type Mode = 'create' | 'edit';
type EmiBank = {
  id?: number | string;
  name?: string | null;
  code?: string | null;
  logo_url?: string | null;
};

const props = defineProps<{ mode: Mode; bank?: EmiBank }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'saved', payload?: unknown): void }>();

const snackbar = useSnackbarStore();
const submitting = ref(false);
const error = ref('');
const formRef = ref();
const fileInputRef = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(props.bank?.logo_url ?? null);

const form = reactive({
  name: props.bank?.name ?? '',
  code: props.bank?.code ?? '',
  logo: null as File | null,
});

const requiredRule = (value: string) => (String(value ?? '').trim() ? true : 'Required');
const submitLabel = computed(() => (props.mode === 'edit' ? 'Save Changes' : 'Create'));

function triggerFilePick() {
  fileInputRef.value?.click();
}

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement | null;
  const file = target?.files?.[0] ?? null;
  form.logo = file;
}

watch(
  () => form.logo,
  (file) => {
    if (logoPreview.value && logoPreview.value.startsWith('blob:')) {
      URL.revokeObjectURL(logoPreview.value);
      logoPreview.value = null;
    }
    if (!file) {
      logoPreview.value = props.bank?.logo_url ?? null;
      return;
    }
    logoPreview.value = URL.createObjectURL(file);
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  if (logoPreview.value && logoPreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(logoPreview.value);
  }
});

async function onSubmit() {
  error.value = '';
  const isValid = await formRef.value?.validate();
  if (isValid?.valid === false) return;

  submitting.value = true;
  try {
    const payload = new FormData();
    payload.append('name', form.name);
    payload.append('code', form.code);
    if (form.logo) payload.append('logo', form.logo);

    let response: unknown;
    if (props.mode === 'edit') {
      if (!props.bank?.id) throw new Error('Bank id is required.');
      response = await updateEmiBank(props.bank.id, payload);
    } else {
      response = await createEmiBank(payload);
    }

    const id = (response as any)?.id ?? (response as any)?.data?.id;
    const message =
      props.mode === 'edit' ? 'EMI bank updated successfully.' : 'EMI bank created successfully.';
    snackbar.show({ message, color: 'success' });
    emit('saved', { id });
    emit('close');
  } catch (err) {
    const response = (err as any)?.response;
    const responseErrors = response?.data?.errors ?? null;
    if (response?.status === 422 && responseErrors && typeof responseErrors === 'object') {
      const firstError = Object.values(responseErrors)[0];
      const message = Array.isArray(firstError) ? firstError[0] : String(firstError ?? 'Validation failed.');
      error.value = message;
      snackbar.show({ message, color: 'error' });
    } else {
      const message = getErrorMessage(err);
      error.value = message;
      snackbar.show({ message, color: 'error' });
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.logo-preview {
  cursor: pointer;
  display: inline-flex;
  flex-direction: column;
  align-items: center;
}

.logo-avatar {
  width: 128px;
  height: 128px;
  border: 1px dashed rgb(var(--v-theme-outline-variant));
  background: #f6f6f6;
}
</style>
