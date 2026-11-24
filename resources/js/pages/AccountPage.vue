<template>
  <v-container fluid class="pa-0">
    <AppPageHeader title="My account" subtitle="Your name and password. Your role is set by an administrator." />

    <v-row>
      <v-col cols="12" md="6">
        <v-card variant="outlined" class="pa-6">
          <div class="d-flex align-center ga-4 mb-5">
            <v-avatar size="56" color="primary" variant="tonal">
              <v-img v-if="auth.admin?.avatar_url" :src="String(auth.admin.avatar_url)" cover />
              <v-icon v-else size="32">mdi-account-circle</v-icon>
            </v-avatar>
            <div class="min-w-0">
              <div class="text-subtitle-1 font-weight-medium text-truncate">{{ auth.admin?.name }}</div>
              <div class="text-body-2 text-medium-emphasis text-truncate">{{ auth.admin?.email }}</div>
              <v-chip size="small" label class="mt-1" :color="auth.isSuper ? 'primary' : undefined" variant="tonal">
                {{ roleName }}
              </v-chip>
            </div>
          </div>

          <v-form @submit.prevent="saveName">
            <v-text-field
              v-model="name"
              label="Name"
              variant="outlined"
              density="comfortable"
              maxlength="255"
              :error-messages="nameError"
              @update:model-value="nameError = ''"
            />
            <div class="d-flex justify-end">
              <v-btn type="submit" color="primary" variant="flat" :loading="savingName" :disabled="!nameChanged">
                Save name
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card variant="outlined" class="pa-6">
          <div class="text-subtitle-1 font-weight-medium mb-1">Change password</div>
          <div class="text-body-2 text-medium-emphasis mb-4">Use at least 8 characters. You stay signed in on this device.</div>

          <v-form @submit.prevent="savePassword">
            <v-text-field
              v-model="current"
              label="Current password"
              type="password"
              autocomplete="current-password"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.current_password"
              @update:model-value="errors.current_password = ''"
            />
            <v-text-field
              v-model="next"
              label="New password"
              type="password"
              autocomplete="new-password"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.password"
              @update:model-value="errors.password = ''"
            />
            <v-text-field
              v-model="confirm"
              label="Confirm new password"
              type="password"
              autocomplete="new-password"
              variant="outlined"
              density="comfortable"
              :error-messages="errors.confirm_password"
              @update:model-value="errors.confirm_password = ''"
            />
            <div class="d-flex justify-end">
              <v-btn type="submit" color="primary" variant="flat" :loading="savingPassword" :disabled="!current || !next || !confirm">
                Change password
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { useAuthStore } from '@/stores/auth.store';
import { useSnackbar } from '@/composables/snackbar';
import { changeOwnPassword, updateOwnName } from '@/api/account.api';
import { getErrorMessage } from '@/shared/errors';

const auth = useAuthStore();
const { showSuccess, showError } = useSnackbar();

const roleName = computed(() => (typeof auth.admin?.role === 'string' && auth.admin.role ? auth.admin.role : 'No role'));

const name = ref(String(auth.admin?.name ?? ''));
const nameError = ref('');
const savingName = ref(false);
const nameChanged = computed(() => name.value.trim().length >= 2 && name.value.trim() !== String(auth.admin?.name ?? ''));
watch(() => auth.admin?.name, (value) => { name.value = String(value ?? ''); });

const current = ref('');
const next = ref('');
const confirm = ref('');
const savingPassword = ref(false);
const errors = reactive({ current_password: '', password: '', confirm_password: '' });

type Fields = Record<string, string[] | string | undefined>;

function fieldErrors(error: unknown): Fields | null {
  const data = (error as { response?: { data?: { errors?: Fields } } })?.response?.data;
  return data?.errors ?? null;
}

function first(value: string[] | string | undefined): string {
  return Array.isArray(value) ? String(value[0] ?? '') : String(value ?? '');
}

async function saveName() {
  savingName.value = true;
  nameError.value = '';
  try {
    await updateOwnName(name.value.trim());
    await auth.fetchProfile();
    showSuccess('Your name was updated.');
  } catch (error) {
    nameError.value = first(fieldErrors(error)?.name) || getErrorMessage(error);
  } finally {
    savingName.value = false;
  }
}

async function savePassword() {
  if (next.value !== confirm.value) {
    errors.confirm_password = 'The new password and its confirmation do not match.';
    return;
  }
  savingPassword.value = true;
  errors.current_password = errors.password = errors.confirm_password = '';
  try {
    await changeOwnPassword(current.value, next.value, confirm.value);
    current.value = next.value = confirm.value = '';
    showSuccess('Your password was changed.');
  } catch (error) {
    const fields = fieldErrors(error);
    if (fields) {
      errors.current_password = first(fields.current_password);
      errors.password = first(fields.password);
      errors.confirm_password = first(fields.confirm_password);
    }
    if (!errors.current_password && !errors.password && !errors.confirm_password) {
      showError(getErrorMessage(error));
    }
  } finally {
    savingPassword.value = false;
  }
}
</script>
