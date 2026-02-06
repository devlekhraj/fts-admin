import { http } from './http';

export function list() {
  return http.get('/products');
}

export function create(payload: Record<string, unknown>) {
  return http.post('/products', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/products/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/products/${id}`);
}
