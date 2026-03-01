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

export async function listProductCategories(
  params?: ListProductCategoriesParams,
): Promise<ProductCategoryListResponse> {
  const response = await http.get('/admin/product-categories', { params });
  return response as unknown as ProductCategoryListResponse;
}

export async function getProductCategory(id: number | string): Promise<ProductCategoryListItem> {
  const response = await http.get(`/admin/product-categories/${id}`);
  return response as unknown as ProductCategoryListItem;
}
