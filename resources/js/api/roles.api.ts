import { http } from './http';

export function list() {
  return http.get('/admin/rbac/roles');
}
