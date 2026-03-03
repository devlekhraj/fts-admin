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

export type BlogCategoryFileItem = {
  id: number | string;
  url?: string | null;
  title?: string | null;
  alt_text?: string | null;
  meta?: Record<string, unknown> | null;
  file_size?: number | null;
  size?: number | null;
  height?: number | null;
  width?: number | null;
};

export type BlogCategoryDetailResponse = BlogCategoryListItem & {
  short_desc?: string | null;
  content?: string | null;
  meta_title?: string | null;
  meta_keywords?: string | null;
  meta_description?: string | null;
  deleted_at?: string | null;
  updated_at?: string | null;
  default_file?: Record<string, unknown> | null;
  files?: BlogCategoryFileItem[];
};

export async function listBlogCategories(params?: ListBlogCategoriesParams): Promise<BlogCategoryListResponse> {
  const response = await http.get('/admin/blog-categories', { params });
  return response as unknown as BlogCategoryListResponse;
}

export async function getBlogCategoryDetail(id: number | string): Promise<BlogCategoryDetailResponse> {
  const response = await http.get(`/admin/blog-categories/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as BlogCategoryDetailResponse;
  }
  return response as unknown as BlogCategoryDetailResponse;
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
