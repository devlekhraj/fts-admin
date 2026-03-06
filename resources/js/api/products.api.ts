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
  variants_count?: number | null;
  created_at?: string | null;
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

export type ProductFileItem = {
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

export type ProductVariantItem = {
  id: number | string;
  product_id?: number | string | null;
  quantity?: number | null;
  price?: number | null;
  attributes?: Record<string, unknown> | null;
  images?: ProductFileItem[];
  created_at?: string | null;
  updated_at?: string | null;
};

export type ProductDetailResponse = ProductListItem & {
  short_desc?: string | null;
  content?: string | null;
  description?: string | null;
  meta_title?: string | null;
  meta_keywords?: string | null;
  meta_description?: string | null;
  updated_at?: string | null;
  default_file?: Record<string, unknown> | null;
  files?: ProductFileItem[];
  variants?: ProductVariantItem[];
  [key: string]: unknown;
};

export async function listProducts(params?: ListProductsParams): Promise<ProductListResponse> {
  const response = await http.get('/admin/products', { params });
  return response as unknown as ProductListResponse;
}

export async function getProductDetail(id: number | string): Promise<ProductDetailResponse> {
  const response = await http.get(`/admin/products/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as ProductDetailResponse;
  }
  return response as unknown as ProductDetailResponse;
}

export type ListBrandsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type ProductBrandListItem = {
  id: number | string;
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

export type ProductBrandFileItem = {
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

export type ProductBrandDetailResponse = ProductBrandListItem & {
  short_desc?: string | null;
  content?: string | null;
  description?: string | null;
  meta_title?: string | null;
  meta_keywords?: string | null;
  meta_description?: string | null;
  updated_at?: string | null;
  default_file?: Record<string, unknown> | null;
  files?: ProductBrandFileItem[];
  [key: string]: unknown;
};

export async function listBrands(params?: ListBrandsParams): Promise<ProductBrandListResponse> {
  const response = await http.get('/admin/brands', { params });
  return response as unknown as ProductBrandListResponse;
}

export async function getBrandDetail(id: number | string): Promise<ProductBrandDetailResponse> {
  const response = await http.get(`/admin/brands/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as ProductBrandDetailResponse;
  }
  return response as unknown as ProductBrandDetailResponse;
}

export function updateBrand(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/brands/${id}`, payload);
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
