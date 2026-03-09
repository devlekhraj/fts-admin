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

export async function listApiKeys(): Promise<ApiKeyItem[]> {
  const response = await http.get('/admin/developer/api-keys');

  // Handles both { data: [...] } and { data: { data: [...] } } response shapes.
  const primary = response?.data;
  if (Array.isArray(primary)) return primary as ApiKeyItem[];
  if (Array.isArray(primary?.data)) return primary.data as ApiKeyItem[];
  return [];
}
