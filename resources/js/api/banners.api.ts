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

export type BannerDetailResponse = {
  id: number | string;
  name?: string | null;
  slug?: string | null;
  status?: boolean;
  created_at?: string | null;
  updated_at?: string | null;
  total_images?: number | null;
  thumb?: string | null;
  files?: Array<{
    id: number | string;
    file_id?: number | string | null;
    url?: string | null;
    title?: string | null;
    alt_text?: string | null;
    meta?: Record<string, unknown>;
    file_size?: number | null;
    size?: number | null;
    height?: number | null;
    width?: number | null;
  }>;
  [key: string]: unknown;
};

export async function listBanners(params?: ListBannersParams): Promise<BannerListResponse> {
  const response = await http.get('/admin/banners', { params });
  return response as unknown as BannerListResponse;
}

export async function getBannerDetail(id: number | string): Promise<BannerDetailResponse> {
  const response = await http.get(`/admin/banners/${id}`);
  return response as unknown as BannerDetailResponse;
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
