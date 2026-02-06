import { http } from './http';

export function list() {
  return http.get('/banners');
}

export function create(payload: Record<string, unknown>) {
  return http.post('/banners', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/banners/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/banners/${id}`);
}
