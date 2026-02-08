import { http } from './http';

export function list(params?: Record<string, unknown>) {
  return http.get('/admin/emi-banks', { params });
}

export function get(id: string) {
  return http.get(`/admin/emi-banks/${id}`);
}

export function tenures(id: string) {
  return http.get(`/admin/emi-banks/${id}/tenures`);
}
