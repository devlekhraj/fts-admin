import { http } from './http';

export function list(params?: Record<string, unknown>) {
  return http.get('/admin/emi-requests', { params });
}

export function get(id: string) {
  return http.get(`/admin/emi-requests/${id}`);
}

export function approve(id: string, payload?: Record<string, unknown>) {
  return http.post(`/admin/emi-requests/${id}/approve`, payload ?? {});
}

export function generateApplication(id: string, payload?: FormData | Record<string, unknown>) {
  return http.post(`/admin/emi-requests/${id}/application-pdf`, payload ?? {});
}

export function listApplications(id: string) {
  return http.get(`/admin/emi-requests/${id}/application-list`);
}
