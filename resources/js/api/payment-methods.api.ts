import { http } from './http';

export type ListPaymentMethodsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type PaymentMethodAsset = {
  id: number | string;
  file_id: number | string;
  url: string;
  title?: string | null;
  alt_text?: string | null;
  status: boolean;
  meta?: Record<string, any>;
  size_info?: string | null;
};

export type PaymentMethodListItem = {
  id: number | string;
  name: string;
  slug: string;
  status: boolean;
  test_mode: boolean;
  is_international: boolean;
  logo_url?: string | null;
  thumb?: string | null;
  image_counts?: number;
  created_at: string;
  [key: string]: unknown;
};

export type PaymentMethodListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type PaymentMethodListResponse = {
  data: PaymentMethodListItem[];
  meta?: PaymentMethodListMeta;
  total?: number;
};

export type PaymentMethodDetailResponse = PaymentMethodListItem & {
  config: Record<string, any>;
  images: PaymentMethodAsset[];
  updated_at?: string | null;
};

export async function listPaymentMethods(params?: ListPaymentMethodsParams): Promise<PaymentMethodListResponse> {
  const response = await http.get('/admin/payment-methods', { params });
  return response as unknown as PaymentMethodListResponse;
}

export async function getPaymentMethodDetail(id: number | string): Promise<PaymentMethodDetailResponse> {
  const response = await http.get(`/admin/payment-methods/${id}/details`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as PaymentMethodDetailResponse;
  }
  return response as unknown as PaymentMethodDetailResponse;
}

export async function updatePaymentMethod(id: number | string, data: Partial<PaymentMethodDetailResponse>): Promise<PaymentMethodDetailResponse> {
  const response = await http.put(`/admin/payment-methods/${id}`, data);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as PaymentMethodDetailResponse;
  }
  return response as unknown as PaymentMethodDetailResponse;
}

export async function updatePaymentMethodImage(id: number | string, fileUsageId: number | string, data: { alt_text: string, is_default: boolean }): Promise<void> {
  await http.put(`/admin/payment-methods/${id}/images/${fileUsageId}`, data);
}

export async function deletePaymentMethodImage(id: number | string, fileUsageId: number | string): Promise<void> {
  await http.delete(`/admin/payment-methods/${id}/images/${fileUsageId}`);
}
