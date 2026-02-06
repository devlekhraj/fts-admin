import { http } from './http';

export function list() {
  return http.get('/blog-categories');
}

export function create(payload: Record<string, unknown>) {
  return http.post('/blog-categories', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/blog-categories/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/blog-categories/${id}`);
}
