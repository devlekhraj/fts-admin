import { http } from './http';

export type DashboardMetrics = {
  totalOrders: number;
  totalEmiRequests: number;
  totalProducts: number;
  totalCustomers: number;
};

export type DashboardLatestOrder = {
  id: number | string;
  order_number: string | null;
  status: string | null;
  total: number | string | null;
  customer: { id: number | string | null; name: string | null; avatar?: string | null };
  created_at?: string | null;
};

export type DashboardLatestEmiRequest = {
  id: number | string;
  product_name: string | null;
  customer_name: string | null;
  customer_avatar?: string | null;
  status: string | null;
  amount: number | string | null;
  created_at?: string | null;
};

export type DashboardLatestActivityLog = {
  id: number | string;
  action: string;
  label: string;
  description: string | null;
  entity_type?: string | null;
  entity_id?: number | string | null;
  actor_name?: string | null;
  created_at: string | null;
};

export type DashboardLatest = {
  orders: DashboardLatestOrder[];
  emi_requests: DashboardLatestEmiRequest[];
  activity_logs: DashboardLatestActivityLog[];
};

function unwrap<T>(response: unknown): T {
  const payload = response as { data?: unknown };
  if (payload && typeof payload === 'object' && 'data' in payload && payload.data) {
    return payload.data as T;
  }
  return response as T;
}

export async function getDashboardMetrics(): Promise<DashboardMetrics> {
  const response = await http.get<DashboardMetrics>('/admin/dashboard/metrics');
  return unwrap<DashboardMetrics>(response);
}

export async function getDashboardLatest(): Promise<DashboardLatest> {
  const response = await http.get<DashboardLatest>('/admin/dashboard/latest');
  return unwrap<DashboardLatest>(response);
}
