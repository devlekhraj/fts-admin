import { http } from './http';

export type ListBlogCategoriesParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type BlogCategoryListItem = {
  id: number | string;
  title?: string | null;
  slug?: string | null;
  thumb?: string | null;
  created_at?: string | null;
  status: boolean;
  [key: string]: unknown;
};

export type BlogCategoryListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type BlogCategoryListPaginatedResponse = {
  data: BlogCategoryListItem[];
  meta?: BlogCategoryListMeta;
  total?: number;
};

export type BlogCategoryListResponse = BlogCategoryListItem[] | BlogCategoryListPaginatedResponse;

export async function listBlogCategories(params?: ListBlogCategoriesParams): Promise<BlogCategoryListResponse> {
  const response = await http.get('/admin/blog-categories', { params });
  return response as unknown as BlogCategoryListResponse;
}

export function list(params?: ListBlogCategoriesParams) {
  return http.get('/admin/blog-categories', { params });
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/blog-categories', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/blog-categories/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/admin/blog-categories/${id}`);
}
