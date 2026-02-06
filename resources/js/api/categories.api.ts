import { http } from './http';

export function list() {
  return http.get('/categories');
}

export function create(payload: Record<string, unknown>) {
  return http.post('/categories', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/categories/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/categories/${id}`);
}
