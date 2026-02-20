import { http } from './http';

export type ListAdminsParams = {
  page?: number;
  per_page?: number;
  search?: string;
  role_id?: string | number;
};

export function list(params?: ListAdminsParams) {
  return http.get('/admin/admin-list', { params });
}

export type CreateAdminPayload = {
  name: string;
  email: string;
  username: string;
  role_id: string | number | null;
  password: string;
  confirm_password: string;
};

export function create(payload: CreateAdminPayload) {
  return http.post('/admin/admin-create', payload);
}
