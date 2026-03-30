import { http } from './http';

export type ListOrdersParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export type OrdersListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type OrderListItem = {
  id: number | string;
  order_number?: string | null;
  status?: string | null;
  total?: number | null;
  customer?: {
    id?: number | string | null;
    name?: string | null;
    email?: string | null;
    avatar?: string | null;
    avatar_url?: string | null;
    mobile?: string | null;
  } | null;
  items_count?: number | null;
  created_at?: string | null;
  [key: string]: unknown;
};

export type OrdersListResponse = {
  data: OrderListItem[];
  meta?: OrdersListMeta;
  total?: number;
};

export type OrderDetailResponse = {
  summary?: {
    id?: number | string | null;
    order_no?: string | null;
    invoice_no?: string | null;
    order_date?: string | null;
    status?: string | null;
    warranty_token?: string | null;
  } | null;
  customer?: {
    id?: number | string | null;
    name?: string | null;
    email?: string | null;
    mobile?: string | null;
    avatar_url?: string | null;
  } | null;
  receipent?: {
    id?: number | string | null;
    name?: string | null;
    phone?: string | null;
    sender_photo?: string | null;
    receiver_photo?: string | null;
  } | null;
  shipping_address?: {
    id?: number | string | null;
    label?: string | null;
    district?: string | null;
    city?: string | null;
    landmark?: string | null;
    province?: string | null;
    geo?: { lat?: number | null; lng?: number | null } | null;
  } | null;
  order_items?: Array<{
    id?: number | string | null;
    product_name?: string | null;
    price?: number | null;
    quantity?: number | null;
    sku?: string | null;
    product_thumb?: string | null;
  }> | null;
  total_summary?: {
    payment_type?: string | null;
    shipping_cost?: string | number | null;
    discount_total?: string | number | null;
    sub_total?: string | number | null;
    total?: string | number | null;
  } | null;
};

export async function listOrders(params?: ListOrdersParams): Promise<OrdersListResponse> {
  const response = await http.get('/admin/orders/list', { params });
  return response as OrdersListResponse;
}

export async function getOrderDetails(id: number | string): Promise<OrderDetailResponse> {
  const response = await http.get(`/admin/orders/${id}/details`);
  const wrapped = response as { data?: unknown };
  if (wrapped && typeof wrapped === 'object' && 'data' in wrapped && wrapped.data) {
    return wrapped.data as OrderDetailResponse;
  }
  return response as unknown as OrderDetailResponse;
}

export async function generateWarrantySerial(id: number | string): Promise<{ warranty_token: string }> {
  const response = await http.post(`/admin/orders/${id}/warranty`);
  const wrapped = response as { warranty_token?: string };
  if (wrapped && typeof wrapped === 'object' && 'warranty_token' in wrapped) {
    return { warranty_token: wrapped.warranty_token as string };
  }
  return response as unknown as { warranty_token: string };
}

export async function updateOrderStatus(id: number | string, status: number): Promise<{ status: string; status_code: number }> {
  const response = await http.post(`/admin/orders/${id}/status`, { status });
  const wrapped = response as { status?: string; status_code?: number };
  if (wrapped && typeof wrapped === 'object') {
    return {
      status: String(wrapped.status ?? ''),
      status_code: Number(wrapped.status_code ?? status),
    };
  }
  return response as unknown as { status: string; status_code: number };
}
