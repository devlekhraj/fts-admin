import { http } from './http';

export type ListPagesParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type PageListItem = {
  id: number | string;
  title: string;
  slug: string;
  status: boolean;
  updated_at: string;
  [key: string]: unknown;
};

export type PageListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type PageListResponse = {
  data: PageListItem[];
  meta?: PageListMeta;
  total?: number;
};

export type PageDetailResponse = PageListItem & {
  content?: string | null;
  meta?: Record<string, any> | null;
  created_at?: string;
  updated_at?: string;
};

export async function listPages(params?: ListPagesParams): Promise<PageListResponse> {
  const response = await http.get('/admin/pages', { params });
  return response as unknown as PageListResponse;
}

export async function deletePage(id: number | string): Promise<void> {
  await http.delete(`/admin/pages/${id}`);
}

export async function getPageDetail(id: number | string): Promise<PageDetailResponse> {
  const response = await http.get(`/admin/pages/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as PageDetailResponse;
  }
  return response as unknown as PageDetailResponse;
}
