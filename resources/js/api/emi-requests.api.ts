import { http } from './http';

export type ListEmiRequestsParams = {
  page?: number;
  per_page?: number;
  search?: string;
  emi_type?: string;
  status?: number;
};

export type EmiRequestListItem = {
  id: number | string;
  application_code?: string | number | null;
  user?: {
    name?: string | null;
    avatar?: string | null;
    email?: string | null;
  } | null;
  product?: {
    id?: number | string;
    name?: string | null;
    thumb?: string | null;
    price?: number | string | null;
  } | null;
  time?: string | null;
  emi_per_month?: number | string | null;
  emi_type?: string | null;
  emi_mode?: string | null;
  status?: number | string | null;
  status_label?: string | null;
  created_at?: string | null;
  [key: string]: unknown;
};

export type EmiRequestListResponse = {
  data: EmiRequestListItem[];
  total?: number;
  current_page?: number;
  per_page?: number;
  meta?: {
    current_page?: number;
    per_page?: number;
    total?: number;
    last_page?: number;
    from?: number | null;
    to?: number | null;
  };
};

export type EmiRequestDetailResponse = {
  id: number | string;
  user?: {
    id?: number | string;
    name?: string | null;
    email?: string | null;
    mobile?: string | null;
    avatar?: string | null;
  } | null;
  product?: {
    id?: number | string;
    name?: string | null;
    price?: number | string | null;
    thumb?: string | null;
  } | null;
  product_price?: number | string | null;
  product_attributes?: unknown;
  emi_per_month?: number | string | null;
  status?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
  [key: string]: unknown;
};

export type EmiApplicationListItem = {
  id: number | string;
  application_id: string,
  bank_name?: string | null;
  status?: string | null;
  created_at?: string | null;
  file_url?: string | null;
  file_path?: string | null;
  [key: string]: unknown;
};

export type EmiApplicationListResponse = {
  data: EmiApplicationListItem[];
  [key: string]: unknown;
};

export type EmiApplicationGenerateResponse = {
  message?: string;
  path?: string;
  [key: string]: unknown;
};

export async function list(params?: ListEmiRequestsParams): Promise<EmiRequestListResponse> {
  const response = await http.get('/admin/emi-requests', { params });
  return response as EmiRequestListResponse;
}

export async function get(id: string): Promise<EmiRequestDetailResponse> {
  const response = await http.get(`/admin/emi-requests/${id}`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as EmiRequestDetailResponse;
  }
  return response as unknown as EmiRequestDetailResponse;
}

export function approve(id: string, payload?: Record<string, unknown>) {
  return http.post(`/admin/emi-requests/${id}/approve`, payload ?? {});
}

export async function generateApplication(
  id: string,
  payload?: FormData | Record<string, unknown>
): Promise<EmiApplicationGenerateResponse> {
  const response = await http.post(`/admin/emi-requests/${id}/application-pdf`, payload ?? {});
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as EmiApplicationGenerateResponse;
  }
  return response as unknown as EmiApplicationGenerateResponse;
}

export async function listApplications(id: string): Promise<EmiApplicationListResponse> {
  const response = await http.get(`/admin/emi-requests/${id}/application-list`);
  if (Array.isArray(response)) {
    return { data: response as EmiApplicationListItem[] };
  }
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && Array.isArray(wrapped.data)) {
    return { data: wrapped.data as EmiApplicationListItem[] };
  }
  return response as unknown as EmiApplicationListResponse;
}

export async function approveApplication(
  id: string | number,
  payload: Record<string, unknown>
): Promise<Record<string, unknown>> {
  const response = await http.post(`/admin/emi-applications/${id}/approve`, payload);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as Record<string, unknown>;
  }
  return response as unknown as Record<string, unknown>;
}
