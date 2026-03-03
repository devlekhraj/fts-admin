import { http } from './http';

export type ListPaymentMethodsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type PaymentMethodListItem = {
  id: number | string;
  name?: string | null;
  slug?: string | null;
  status: boolean;
  test_mode: boolean;
  is_international: boolean;
  logo_url?: string | null;
  created_at?: string | null;
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
  config?: Record<string, unknown>;
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
