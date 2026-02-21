import { http } from './http';

export function list() {
  return http.get('/admin/rbac/roles');
}

export function listPermissions() {
  return http.get('/admin/rbac/permissions');
}
