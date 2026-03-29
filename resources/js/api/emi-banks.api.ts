import { http } from './http';

export type EmiBankListItem = {
  id: number | string;
  name?: string | null;
  code?: string | null;
  created_at?: string | null;
};

export type EmiBankPayload = {
  name?: string | null;
  code?: string | null;
  [key: string]: unknown;
};

export type EmiBankListResponse = {
  data: EmiBankListItem[];
  total: number;
  current_page: number;
  per_page: number;
};

export async function list(params?: Record<string, unknown>): Promise<EmiBankListResponse> {
  const response = await http.get('/admin/emi-banks', { params });
  return response as unknown as EmiBankListResponse;
}

export async function createEmiBank(payload: EmiBankPayload | FormData): Promise<Record<string, unknown>> {
  const response = await http.post('/admin/emi-banks', payload);
  return response as unknown as Record<string, unknown>;
}

export async function get(id: string | number): Promise<Record<string, unknown>> {
  const response = await http.get(`/admin/emi-banks/${id}`);
  return response as unknown as Record<string, unknown>;
}

export async function tenures(id: string | number): Promise<Record<string, unknown>> {
  const response = await http.get(`/admin/emi-banks/${id}/tenures`);
  return response as unknown as Record<string, unknown>;
}

export async function updateEmiBank(
  id: string | number,
  payload: EmiBankPayload | FormData,
): Promise<Record<string, unknown>> {
  const response = await http.put(`/admin/emi-banks/${id}`, payload);
  return response as unknown as Record<string, unknown>;
}

export async function deleteEmiBank(id: string | number): Promise<Record<string, unknown>> {
  const response = await http.delete(`/admin/emi-banks/${id}`);
  return response as unknown as Record<string, unknown>;
}
