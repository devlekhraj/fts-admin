import { http } from './http';

export function list() {
  return http.get('/admin/products');
}

export type ListProductsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type ProductListItem = {
  id: number | string;
  name?: string | null;
  slug?: string | null;
  status: boolean;
  emi_enabled: boolean;
  thumb?: string | null;
  [key: string]: unknown;
};

export type ProductListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type ProductListResponse = {
  data: ProductListItem[];
  meta?: ProductListMeta;
  total?: number;
};

export async function listProducts(params?: ListProductsParams): Promise<ProductListResponse> {
  const response = await http.get('/admin/products', { params });
  return response as unknown as ProductListResponse;
}

export type ListBrandsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type ProductBrandListItem = {
  name?: string | null;
  logo?: string | null;
  slug?: string | null;
  status: boolean;
  total_products?: number | null;
  created_at?: string | null;
  [key: string]: unknown;
};

export type ProductBrandListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type ProductBrandListResponse = {
  data: ProductBrandListItem[];
  meta?: ProductBrandListMeta;
  total?: number;
};

export async function listBrands(params?: ListBrandsParams): Promise<ProductBrandListResponse> {
  const response = await http.get('/admin/brands', { params });
  return response as unknown as ProductBrandListResponse;
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/products', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/products/${id}`, payload);
}

export function remove(id: string) {
  return http.delete(`/admin/products/${id}`);
}
