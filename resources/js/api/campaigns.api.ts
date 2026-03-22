import { http } from './http';

export function list(params?: Record<string, unknown>) {
  return http.get('/campaigns', { params });
}

export function create(payload: Record<string, unknown>) {
  return http.post('/campaigns', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/campaigns/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/campaigns/${id}`);
}
