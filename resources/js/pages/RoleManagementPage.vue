<template>
  <div>
    <AppPageHeader title="Role Management" subtitle="Decide what each role can see and do." :show-back="false">
      <template #actions>
        <v-btn v-if="auth.can('roles-add')" color="primary" variant="flat" prepend-icon="mdi-plus" @click="openCreate">Add role</v-btn>
      </template>
    </AppPageHeader>

    <v-container fluid>
      <v-alert type="info" variant="tonal" density="comfortable" class="mb-4">
        Administrators can do everything. Every other role can do only what is ticked for it.
        A change applies to the people who hold the role from their next action.
      </v-alert>

      <div v-if="loading" class="text-medium-emphasis pa-4">Loading roles...</div>
      <v-alert v-else-if="loadError" type="error" variant="tonal" density="comfortable">{{ loadError }}</v-alert>

      <v-expansion-panels v-else v-model="expanded" multiple variant="accordion">
        <v-expansion-panel v-for="role in roles" :key="role.id" class="role-panel">
          <v-expansion-panel-title>
            <div class="d-flex flex-wrap align-center ga-2 w-100 pr-2">
              <v-icon :icon="role.is_super ? 'mdi-shield-crown-outline' : 'mdi-shield-account-outline'" :color="role.is_super ? 'warning' : 'primary'" />
              <span class="text-subtitle-1 font-weight-bold">{{ role.name }}</span>
              <v-chip v-if="role.is_super" size="x-small" color="warning" variant="tonal" label>Full access</v-chip>
              <v-chip v-if="role.is_own" size="x-small" color="primary" variant="tonal" label>Your role</v-chip>
              <span class="text-body-2 text-medium-emphasis">
                {{ role.is_super ? 'Everything' : `${role.permissions.length} ${role.permissions.length === 1 ? 'permission' : 'permissions'}` }}
                · {{ role.admins_count }} {{ role.admins_count === 1 ? 'admin' : 'admins' }}
              </span>
              <v-spacer />
              <div class="d-flex align-center ga-1" @click.stop>
                <v-tooltip :text="editReason(role)" location="top" :disabled="!editReason(role)">
                  <template #activator="{ props: tip }">
                    <span v-bind="tip">
                      <v-btn icon="mdi-pencil-outline" size="small" variant="tonal" color="primary" :disabled="!canEdit(role)" :aria-label="`Edit ${role.name}`" @click="openEdit(role)" />
                    </span>
                  </template>
                </v-tooltip>
                <v-tooltip :text="deleteReason(role)" location="top" :disabled="!deleteReason(role)">
                  <template #activator="{ props: tip }">
                    <span v-bind="tip">
                      <v-btn icon="mdi-delete-outline" size="small" variant="tonal" color="error" :disabled="!canDelete(role)" :aria-label="`Delete ${role.name}`" @click="confirmDelete(role)" />
                    </span>
                  </template>
                </v-tooltip>
              </div>
            </div>
          </v-expansion-panel-title>

          <v-expansion-panel-text>
            <div v-if="role.is_super" class="text-body-2 text-medium-emphasis">
              The Administrator role can use every part of the admin, including new parts added later. It cannot be changed or deleted,
              and there must always be at least one Administrator.
            </div>
            <div v-else-if="!role.permissions.length" class="text-body-2 text-medium-emphasis">
              This role has no permissions. People with it can sign in and see the dashboard only.
            </div>
            <template v-else>
              <div v-for="group in groupedFor(role)" :key="group.key" class="mb-3">
                <div class="text-caption text-medium-emphasis mb-1">{{ group.label }}</div>
                <div class="d-flex flex-wrap ga-1">
                  <v-chip v-for="permission in group.items" :key="permission.key" size="small" variant="tonal" color="primary" label>
                    {{ permission.label }}
                  </v-chip>
                </div>
              </div>
            </template>
          </v-expansion-panel-text>
        </v-expansion-panel>
      </v-expansion-panels>
    </v-container>

    <RoleFormDialog v-model="formOpen" :role="editing" :groups="groups" @saved="fetchAll" />

    <v-dialog v-model="deleteOpen" max-width="460" persistent>
      <v-card>
        <v-card-title>Delete role</v-card-title>
        <v-card-text>
          Delete the role <strong>{{ deleting?.name }}</strong>? This cannot be undone.
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" :disabled="deletingBusy" @click="deleteOpen = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" :loading="deletingBusy" @click="runDelete">Delete role</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import RoleFormDialog from '@/components/role/RoleFormDialog.vue';
import { deleteRole, list as listRoles, listPermissions, type PermissionGroup, type RoleItem } from '@/api/roles.api';
import { useAuthStore } from '@/stores/auth.store';
import { useSnackbar } from '@/composables/snackbar';

const auth = useAuthStore();
const { showSuccess, showError } = useSnackbar();

const roles = ref<RoleItem[]>([]);
const groups = ref<PermissionGroup[]>([]);
const loading = ref(true);
const loadError = ref('');
const expanded = ref<number[]>([]);

const formOpen = ref(false);
const editing = ref<RoleItem | null>(null);
const deleteOpen = ref(false);
const deleting = ref<RoleItem | null>(null);
const deletingBusy = ref(false);

const labels = computed(() => {
  const map = new Map<string, { label: string; group: PermissionGroup }>();
  for (const group of groups.value) {
    for (const permission of group.permissions) map.set(permission.key, { label: permission.label, group });
  }
  return map;
});

/** A role's permissions arranged by the same groups the role form uses. */
function groupedFor(role: RoleItem) {
  const out: Array<{ key: string; label: string; items: Array<{ key: string; label: string }> }> = [];
  for (const group of groups.value) {
    const items = group.permissions.filter((p) => role.permissions.includes(p.key)).map((p) => ({ key: p.key, label: p.label }));
    if (items.length) out.push({ key: group.key, label: group.label, items });
  }
  // permissions saved earlier that the list no longer knows are still shown, never hidden
  const unknown = role.permissions.filter((key) => !labels.value.has(key));
  if (unknown.length) out.push({ key: 'unknown', label: 'Other', items: unknown.map((key) => ({ key, label: key })) });
  return out;
}

function canEdit(role: RoleItem) {
  return auth.can('roles-edit') && role.editable;
}

function canDelete(role: RoleItem) {
  return auth.can('roles-delete') && role.deletable;
}

function editReason(role: RoleItem): string {
  if (canEdit(role)) return '';
  if (role.is_super) return 'The Administrator role cannot be changed.';
  if (role.is_own) return 'You cannot change the role you hold. Ask another administrator.';
  if (!auth.can('roles-edit')) return 'You do not have permission to edit roles.';
  return 'This role has more access than yours.';
}

function deleteReason(role: RoleItem): string {
  if (canDelete(role)) return '';
  if (role.is_super) return 'The Administrator role cannot be deleted.';
  if (role.is_own) return 'You cannot delete the role you hold.';
  if (!auth.can('roles-delete')) return 'You do not have permission to delete roles.';
  if (role.admins_count > 0) return `${role.admins_count} ${role.admins_count === 1 ? 'admin has' : 'admins have'} this role. Give them another role first.`;
  return 'This role has more access than yours.';
}

async function fetchAll() {
  loading.value = roles.value.length === 0;
  loadError.value = '';
  try {
    const [rolesResponse, permissionsResponse] = await Promise.all([listRoles(), listPermissions()]);
    const roleList = (rolesResponse as { data?: RoleItem[] }).data ?? [];
    const groupList = (permissionsResponse as { data?: PermissionGroup[] }).data ?? [];
    roles.value = Array.isArray(roleList) ? roleList : [];
    groups.value = Array.isArray(groupList) ? groupList : [];
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string } } })?.response?.data;
    loadError.value = data?.message ?? 'Could not load the roles.';
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editing.value = null;
  formOpen.value = true;
}

function openEdit(role: RoleItem) {
  editing.value = role;
  formOpen.value = true;
}

function confirmDelete(role: RoleItem) {
  deleting.value = role;
  deleteOpen.value = true;
}

async function runDelete() {
  if (!deleting.value || deletingBusy.value) return;
  deletingBusy.value = true;
  try {
    const response = await deleteRole(deleting.value.id);
    showSuccess(response.message || 'Role deleted');
    deleteOpen.value = false;
    await fetchAll();
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string } } })?.response?.data;
    showError(data?.message ?? 'Could not delete the role');
    deleteOpen.value = false;
    await fetchAll();
  } finally {
    deletingBusy.value = false;
  }
}

onMounted(fetchAll);
</script>
