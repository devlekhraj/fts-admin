import { http } from './http';

export type ListBannersParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type BannerListItem = {
  id: number | string;
  name?: string | null;
  slug?: string | null;
  status: boolean;
  created_at?: string | null;
  thumb?: string | null;
  total_images?: number | null;
  [key: string]: unknown;
};

export type BannerListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type BannerListResponse = {
  data: BannerListItem[];
  meta?: BannerListMeta;
  total?: number;
};

export async function listBanners(params?: ListBannersParams): Promise<BannerListResponse> {
  const response = await http.get('/admin/banners', { params });
  return response as unknown as BannerListResponse;
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/banners', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/banners/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/admin/banners/${id}`);
}
