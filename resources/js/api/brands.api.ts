import { http } from './http';

export function list() {
  return http.get('/brands');
}

export function create(payload: Record<string, unknown>) {
  return http.post('/brands', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/brands/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/brands/${id}`);
}
