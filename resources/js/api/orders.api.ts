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

export type OrderDetailResponse = OrderListItem & {
  currency?: string | null;
  payment_method?: {
    id?: number | string | null;
    name?: string | null;
    slug?: string | null;
  } | null;
  subtotal?: number | null;
  discount_total?: number | null;
  tax_total?: number | null;
  shipping_total?: number | null;
  notes?: string | null;
  meta?: Record<string, unknown> | null;
  paid_at?: string | null;
  cancelled_at?: string | null;
  updated_at?: string | null;
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
