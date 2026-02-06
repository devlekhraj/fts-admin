import { http } from './http';

export function list() {
  return http.get('/blogs');
}

export function create(payload: Record<string, unknown>) {
  return http.post('/blogs', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/blogs/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/blogs/${id}`);
}
