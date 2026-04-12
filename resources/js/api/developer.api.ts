import { http } from './http';

export type ApiKeyItem = {
  id: number | string;
  vendor_id?: number | string | null;
  host?: string | null;
  test_public_key?: string | null;
  test_secret_key?: string | null;
  live_public_key?: string | null;
  live_secret_key?: string | null;
  mode?: string | null;
  description?: string | null;
  is_active?: boolean;
  created_at?: string | null;
  updated_at?: string | null;
};

export type ApiKeyPayload = {
  host?: string | null;
  mode: 'test' | 'live';
  description?: string | null;
  is_active?: boolean;
};

export async function listApiKeys(): Promise<ApiKeyItem[]> {
  const response = await http.get('/admin/developer/api-keys');

  // Handles both { data: [...] } and { data: { data: [...] } } response shapes.
  const primary = response?.data;
  if (Array.isArray(primary)) return primary as ApiKeyItem[];
  if (Array.isArray(primary?.data)) return primary.data as ApiKeyItem[];
  return [];
}

export async function createApiKey(payload: ApiKeyPayload): Promise<ApiKeyItem> {
  const response = await http.post('/admin/developer/api-keys', payload);
  const data = (response as any)?.data?.data ?? (response as any)?.data ?? response;
  return data as ApiKeyItem;
}

export async function updateApiKey(id: number | string, payload: ApiKeyPayload): Promise<ApiKeyItem> {
  const response = await http.put(`/admin/developer/api-keys/${id}`, payload);
  const data = (response as any)?.data?.data ?? (response as any)?.data ?? response;
  return data as ApiKeyItem;
}

export async function deleteApiKey(id: number | string): Promise<void> {
  await http.delete(`/admin/developer/api-keys/${id}`);
}
