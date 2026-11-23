<template>
  <v-dialog :model-value="modelValue" max-width="860" scrollable persistent @update:model-value="onToggle">
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <span>{{ role ? 'Edit role' : 'Add role' }}</span>
        <v-btn icon="mdi-close" variant="text" density="comfortable" aria-label="Close" :disabled="saving" @click="close" />
      </v-card-title>
      <v-divider />

      <v-card-text>
        <v-text-field
          v-model="form.name"
          label="Role name *"
          maxlength="50"
          counter
          variant="outlined"
          density="comfortable"
          :error-messages="errors.name"
          @update:model-value="delete errors.name"
        />

        <div class="d-flex align-center justify-space-between mt-2 mb-2">
          <div class="text-subtitle-1 font-weight-medium">What this role can do</div>
          <div class="text-body-2 text-medium-emphasis">{{ form.permissions.length }} selected</div>
        </div>
        <div v-if="errors.permissions" class="text-error text-body-2 mb-2">{{ errors.permissions[0] }}</div>

        <v-row dense>
          <v-col v-for="group in groups" :key="group.key" cols="12" md="6">
            <v-card variant="outlined" class="h-100">
              <div class="d-flex align-center pa-2 pb-0">
                <v-checkbox
                  :model-value="groupState(group).all"
                  :indeterminate="groupState(group).some && !groupState(group).all"
                  :disabled="groupState(group).grantable === 0"
                  :label="group.label"
                  density="compact"
                  hide-details
                  class="group-checkbox"
                  @update:model-value="toggleGroup(group, Boolean($event))"
                />
                <v-spacer />
                <span class="text-caption text-medium-emphasis pr-2">{{ groupState(group).selected }}/{{ group.permissions.length }}</span>
              </div>
              <v-divider class="mt-1" />
              <div class="pa-2 pt-1">
                <v-tooltip
                  v-for="permission in group.permissions"
                  :key="permission.key"
                  :disabled="permission.grantable"
                  text="You do not hold this permission, so you cannot give it to a role."
                  location="start"
                >
                  <template #activator="{ props: tip }">
                    <div v-bind="tip">
                      <v-checkbox
                        v-model="form.permissions"
                        :value="permission.key"
                        :label="permission.label"
                        :disabled="!permission.grantable"
                        density="compact"
                        hide-details
                        color="primary"
                      />
                    </div>
                  </template>
                </v-tooltip>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider />
      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn variant="text" :disabled="saving" @click="close">Cancel</v-btn>
        <v-btn color="primary" variant="flat" :loading="saving" @click="submit">{{ role ? 'Save changes' : 'Create role' }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { createRole, updateRole, type PermissionGroup, type RoleItem } from '@/api/roles.api';
import { useSnackbar } from '@/composables/snackbar';

const props = defineProps<{ modelValue: boolean; role: RoleItem | null; groups: PermissionGroup[] }>();
const emit = defineEmits<{ (e: 'update:modelValue', open: boolean): void; (e: 'saved', role: RoleItem): void }>();

const { showSuccess, showError } = useSnackbar();
const form = reactive({ name: '', permissions: [] as string[] });
const errors = ref<Record<string, string[]>>({});
const saving = ref(false);

watch(
  () => props.modelValue,
  (open) => {
    if (!open) return;
    errors.value = {};
    form.name = props.role?.name ?? '';
    form.permissions = [...(props.role?.permissions ?? [])];
  },
  { immediate: true },
);

function groupState(group: PermissionGroup) {
  const keys = group.permissions.map((p) => p.key);
  const grantable = group.permissions.filter((p) => p.grantable).map((p) => p.key);
  const selected = keys.filter((key) => form.permissions.includes(key)).length;
  const grantableSelected = grantable.filter((key) => form.permissions.includes(key)).length;
  return {
    selected,
    grantable: grantable.length,
    all: grantable.length > 0 && grantableSelected === grantable.length,
    some: grantableSelected > 0,
  };
}

function toggleGroup(group: PermissionGroup, on: boolean) {
  const grantable = group.permissions.filter((p) => p.grantable).map((p) => p.key);
  const rest = form.permissions.filter((key) => !grantable.includes(key));
  form.permissions = on ? [...rest, ...grantable] : rest;
}

function close() {
  if (saving.value) return;
  emit('update:modelValue', false);
}

function onToggle(open: boolean) {
  if (!open) close();
}

async function submit() {
  if (saving.value) return;
  errors.value = {};
  const name = form.name.trim();
  if (name.length < 2) {
    errors.value = { name: ['Enter a role name (at least 2 letters)'] };
    return;
  }

  saving.value = true;
  try {
    const payload = { name, permissions: form.permissions };
    const response = props.role ? await updateRole(props.role.id, payload) : await createRole(payload);
    showSuccess(response.message || 'Role saved');
    emit('saved', response.data);
    emit('update:modelValue', false);
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })?.response?.data;
    errors.value = data?.errors ?? {};
    showError(data?.message ?? 'Could not save the role');
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.group-checkbox :deep(.v-label) {
  font-weight: 600;
  opacity: 1;
}
</style>
