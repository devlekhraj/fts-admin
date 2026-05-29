import { http } from './http';

export type ListFaqsParams = {
  page?: number;
  per_page?: number;
  search?: string;
  type?: string;
  type_id?: number | string;
};

export type FaqListItem = {
  id: number | string;
  type?: string | null;
  type_id?: number | string | null;
  type_name?: string | null;
  question: string;
  answer: string;
  created_at?: string | null;
  updated_at?: string | null;
  [key: string]: unknown;
};

export type FaqListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type FaqListResponse = {
  data: FaqListItem[];
  meta?: FaqListMeta;
  total?: number;
};

export type SaveFaqPayload = {
  id?: number | string | null;
  type: string;
  type_id?: number | string | null;
  question: string;
  answer: string;
};

export type SaveFaqResponse = {
  message?: string;
  data: FaqListItem;
  success?: boolean;
};

export type DeleteFaqResponse = {
  message?: string;
  success?: boolean;
};

export async function listFaqs(params?: ListFaqsParams): Promise<FaqListResponse> {
  const response = await http.get('/admin/faqs', { params });
  return response as unknown as FaqListResponse;
}

export async function saveFaq(payload: SaveFaqPayload): Promise<SaveFaqResponse> {
  const response = await http.post('/admin/faqs/save', payload);
  return response as unknown as SaveFaqResponse;
}

export async function deleteFaq(id: number | string): Promise<DeleteFaqResponse> {
  const response = await http.delete(`/admin/faqs/${id}`);
  return response as unknown as DeleteFaqResponse;
}

export async function listProductFaqs(
  productId: number | string,
  params?: ListFaqsParams,
): Promise<FaqListResponse> {
  const response = await http.get(`/admin/products/${productId}/faqs`, { params });
  return response as unknown as FaqListResponse;
}

export async function listBrandFaqs(
  brandId: number | string,
  params?: ListFaqsParams,
): Promise<FaqListResponse> {
  const response = await http.get(`/admin/brands/${brandId}/faqs`, { params });
  return response as unknown as FaqListResponse;
}

export async function listProductCategoryFaqs(
  categoryId: number | string,
  params?: ListFaqsParams,
): Promise<FaqListResponse> {
  const response = await http.get(`/admin/product-categories/${categoryId}/faqs`, { params });
  return response as unknown as FaqListResponse;
}
