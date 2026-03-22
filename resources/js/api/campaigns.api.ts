import { http } from './http';
import { Campaign, CampaignProductListItem } from '@/types/models';

export function list(params?: Record<string, unknown>) {
  return http.get('/admin/campaigns', { params });
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/campaigns', payload);
}

export function update(id: string | number, payload: Record<string, unknown>) {
  return http.put(`/admin/campaigns/${id}`, payload);
}

export function show(id: string | number) {
  return http.get<Campaign>(`/admin/campaigns/${id}`);
}

export function campaignProducts(id: string | number, params?: Record<string, unknown>) {
  return http.get<CampaignProductListItem[]>(`/admin/campaigns/${id}/products`, { params });
}

export function remove(id: string) {
  return http.delete(`/admin/campaigns/${id}/delete`);
}
