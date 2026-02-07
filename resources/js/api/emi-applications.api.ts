import { http } from './http';

export function list(params?: Record<string, unknown>) {
  return http.get('/admin/emi-applications', { params });
}

export function get(id: string) {
  return http.get(`/admin/emi-applications/${id}`);
}

export function approve(id: string, payload?: Record<string, unknown>) {
  return http.post(`/admin/emi-applications/${id}/approve`, payload ?? {});
}
