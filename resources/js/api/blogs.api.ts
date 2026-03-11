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

export type BlogFileItem = {
  id: number | string;
  url?: string | null;
  alt_text?: string | null;
  meta?: {
    is_default: boolean;
  } | null;
  size_info?: string | null;
};

export type BlogDetailResponse = BlogListItem & {
  short_desc?: string | null;
  content?: string | null;
  meta_title?: string | null;
  meta_keywords?: string | null;
  meta_description?: string | null;
  updated_at?: string | null;
  category_id?: number | string | null;
  default_file?: Record<string, unknown> | null;
  files?: BlogFileItem[];
  [key: string]: unknown;
};

export type UpdateBlogImagePayload = {
  alt_text: string;
  is_default?: boolean;
};

export async function listBlogs(params?: ListBlogsParams): Promise<BlogListResponse> {
  const response = await http.get('/admin/blogs', { params });
  return response as unknown as BlogListResponse;
}

export async function getBlogDetail(id: number | string): Promise<BlogDetailResponse> {
  const response = await http.get(`/admin/blogs/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as BlogDetailResponse;
  }
  return response as unknown as BlogDetailResponse;
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

export function updateBlogImage(
  blogId: number | string,
  fileUsageId: number | string,
  payload: UpdateBlogImagePayload,
) {
  return http.put(`/admin/blogs/${blogId}/images/${fileUsageId}`, payload);
}

export function deleteBlogImage(blogId: number | string, fileUsageId: number | string) {
  return http.delete(`/admin/blogs/${blogId}/images/${fileUsageId}`);
}
