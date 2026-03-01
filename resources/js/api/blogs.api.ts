import { http } from './http';

export type ListBlogsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type BlogListItem = {
  id: number | string;
  title?: string | null;
  slug?: string | null;
  status: boolean;
  published_at?: string | null;
  category_name?: string | null;
  thumb?: string | null;
  [key: string]: unknown;
};

export type BlogListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type BlogListResponse = {
  data: BlogListItem[];
  meta?: BlogListMeta;
  total?: number;
};

export async function listBlogs(params?: ListBlogsParams): Promise<BlogListResponse> {
  const response = await http.get('/admin/blogs', { params });
  return response as unknown as BlogListResponse;
}

export function list(params?: ListBlogsParams) {
  return http.get('/admin/blogs', { params });
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/blogs', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/blogs/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/admin/blogs/${id}`);
}
