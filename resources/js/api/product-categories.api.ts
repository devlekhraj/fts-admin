import { http } from './http';

export type ListProductCategoriesParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type ProductCategoryListItem = {
  id: number;
  title?: string | null;
  slug?: string | null;
  thumb?: string | null;
  status?: boolean | null;
  created_at?: string | null;
  [key: string]: unknown;
};

export type ProductCategoryListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type ProductCategoryListResponse = {
  data: ProductCategoryListItem[];
  meta?: ProductCategoryListMeta;
  total?: number;
};

export type ProductCategoryFileItem = {
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

export type ProductCategoryDetailResponse = ProductCategoryListItem & {
  short_desc?: string | null;
  content?: string | null;
  description?: string | null;
  meta_title?: string | null;
  meta_keywords?: string | null;
  meta_description?: string | null;
  updated_at?: string | null;
  default_file?: Record<string, unknown> | null;
  files?: ProductCategoryFileItem[];
  [key: string]: unknown;
};

export async function listProductCategories(
  params?: ListProductCategoriesParams,
): Promise<ProductCategoryListResponse> {
  const response = await http.get('/admin/product-categories', { params });
  return response as unknown as ProductCategoryListResponse;
}

export async function getProductCategory(id: number | string): Promise<ProductCategoryDetailResponse> {
  const response = await http.get(`/admin/product-categories/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as ProductCategoryDetailResponse;
  }
  return response as unknown as ProductCategoryDetailResponse;
}

export function updateProductCategory(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/product-categories/${id}`, payload);
}
