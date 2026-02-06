import { http } from './http';

export function list() {
  return http.get('/campaigns');
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
