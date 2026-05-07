import { http } from './http';

export type DashboardMetrics = {
  totalOrders: number;
  totalEmiRequests: number;
  totalProducts: number;
  totalCustomers: number;
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

