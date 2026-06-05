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

export function saveBrandBanner(id: number | string, payload: FormData) {
  return http.post(`/admin/brands/${id}/banner`, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
}

export function updateBrandBanner(id: number | string, fileUsageId: number | string, payload: FormData) {
  const formData = new FormData();
  payload.forEach((value, key) => {
    formData.append(key, value);
  });
  formData.append('_method', 'PUT');

  return http.post(`/admin/brands/${id}/banner/${fileUsageId}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
}

export function deleteBrandBanner(id: number | string, fileUsageId: number | string) {
  return http.delete(`/admin/brands/${id}/banner/${fileUsageId}`);
}
