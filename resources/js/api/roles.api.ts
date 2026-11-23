import { http } from './http';

export type RoleItem = {
  id: number;
  name: string;
  permissions: string[];
  /** The Administrator role: full access, cannot be changed or deleted. */
  is_super: boolean;
  admins_count: number;
  /** The role the signed-in admin holds. */
  is_own: boolean;
  /** What the signed-in admin may do with this role (the server checks again). */
  assignable: boolean;
  editable: boolean;
  deletable: boolean;
};

export type PermissionGroup = {
  key: string;
  label: string;
  permissions: Array<{ key: string; label: string; grantable: boolean }>;
};

export type RolePayload = { name: string; permissions: string[] };

export function list() {
  return http.get('/admin/rbac/roles');
}

export function listPermissions() {
  return http.get('/admin/rbac/permissions');
}

export async function createRole(payload: RolePayload) {
  return (await http.post('/admin/rbac/roles', payload)) as unknown as { success: boolean; message: string; data: RoleItem };
}

export async function updateRole(id: number, payload: RolePayload) {
  return (await http.put(`/admin/rbac/roles/${id}`, payload)) as unknown as { success: boolean; message: string; data: RoleItem };
}

export async function deleteRole(id: number) {
  return (await http.delete(`/admin/rbac/roles/${id}`)) as unknown as { success: boolean; message: string };
}
