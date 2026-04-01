import { http } from './http';

export function list() {
  return http.get('/admin/products');
}

export type ListProductsParams = {
  page?: number;
  per_page?: number;
  search?: string;
  category_id?: number | string | null;
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
  alt_text?: string | null;
  meta?: {
    is_default: boolean;
  } | null;
  size_info?: string | null;
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

export type ProductDetailOverview = {
  name?: string | null;
  slug?: string | null;
  thumb?: string | null;
  status?: boolean;
  emi_enabled?: boolean;
  sku?: string | null;
};

export type ProductDetailBrand = {
  id?: number | string | null;
  name?: string | null;
  slug?: string | null;
  thumb?: string | null;
};

export type ProductDetailMeta = {
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
};

export type ProductDetailDescription = {
  description?: string | null;
  short_description?: string | null;
  highlights?: string | null;
  warranty_description?: string | null;
};

export type ProductDetailPreOrder = {
  availability?: boolean;
  price?: number | null;
};

export type ProductDetailPrice = {
  current_price?: number | null;
  compare_price?: number | null;
  quantity?: number | null;
};

export type ProductDetailAttributes = {
  attribute_class_id?: number | string | null;
  product_attributes?: Record<string, unknown> | null;
  [key: string]: unknown;
};

export type ProductDetailResponse = {
  overview?: ProductDetailOverview | null;
  brand?: ProductDetailBrand | null;
  meta?: ProductDetailMeta | null;
  description?: ProductDetailDescription | null;
  pre_order?: ProductDetailPreOrder | null;
  price?: ProductDetailPrice | null;
  images?: ProductFileItem[];
  variants?: ProductVariantItem[];
  attributes?: ProductDetailAttributes | null;
  schema_jsonld?: string | null;
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
  alt_text?: string | null;
  meta?: {
    is_default: boolean;
  } | null;
  size_info?: string | null;
};

export type ProductBrandDetailResponse = ProductBrandListItem & {
  description?: string | null;
  meta_title?: string | null;
  meta_keywords?: string | null;
  meta_description?: string | null;
  updated_at?: string | null;
  default_file?: Record<string, unknown> | null;
  files?: ProductBrandFileItem[];
  [key: string]: unknown;
};

export type UpdateBrandImagePayload = {
  alt_text: string;
  is_default?: boolean;
};

export type UpdateProductImagePayload = {
  alt_text: string;
  is_default?: boolean;
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

export function createBrand(payload: Record<string, unknown>) {
  return http.post('/admin/brands', payload);
}

export function deleteBrand(id: string) {
  return http.delete(`/admin/brands/${id}`);
}

export function updateBrandImage(
  brandId: number | string,
  fileUsageId: number | string,
  payload: UpdateBrandImagePayload,
) {
  return http.put(`/admin/brands/${brandId}/images/${fileUsageId}`, payload);
}

export function deleteBrandImage(brandId: number | string, fileUsageId: number | string) {
  return http.delete(`/admin/brands/${brandId}/images/${fileUsageId}`);
}

export function updateProductImage(
  productId: number | string,
  fileUsageId: number | string,
  payload: UpdateProductImagePayload,
) {
  return http.put(`/admin/products/${productId}/images/${fileUsageId}`, payload);
}

export function deleteProductImage(productId: number | string, fileUsageId: number | string) {
  return http.delete(`/admin/products/${productId}/images/${fileUsageId}`);
}

export function updateVariantImage(
  variantId: number | string,
  fileUsageId: number | string,
  payload: UpdateProductImagePayload,
) {
  return http.put(`/admin/product-variants/${variantId}/images/${fileUsageId}`, payload);
}

export function deleteVariantImage(
  variantId: number | string, 
  fileUsageId: number | string
) {
  return http.delete(`/admin/product-variants/${variantId}/images/${fileUsageId}`);
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/products', payload);
}

export function update(id: string, payload: Record<string, unknown>) {
  return http.put(`/admin/products/${id}`, payload);
}

export type CreateProductVariantResponse = {
  message?: string;
  data?: ProductVariantItem;
  success?: boolean;
};

export async function createVariant(
  productId: string | number,
  payload: Record<string, unknown>,
): Promise<CreateProductVariantResponse> {
  const response = await http.post(`/admin/products/${productId}/variants`, payload);
  return response as unknown as CreateProductVariantResponse;
}

export async function updateVariant(
  productId: string | number,
  itemId: string | number,
  payload: Record<string, unknown>,
): Promise<CreateProductVariantResponse> {
  const response = await http.put(`/admin/products/${productId}/variants/${itemId}`, payload);
  return response as unknown as CreateProductVariantResponse;
}

export function remove(id: string) {
  return http.delete(`/admin/products/${id}`);
}
