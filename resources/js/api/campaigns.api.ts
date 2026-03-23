import { http } from './http';
import { Campaign, CampaignProductListItem, ProductListItem, ProductCategoryListItem } from '@/types/models';

export function list(params?: Record<string, unknown>) {
  return http.get('/admin/campaigns', { params });
}

export function create(payload: Record<string, unknown>) {
  return http.post('/admin/campaigns', payload);
}

export function update(id: string | number, payload: Record<string, unknown>) {
  return http.put(`/admin/campaigns/${id}/update`, payload);
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

export function getProductCategories() {
  return http.get<ProductCategoryListItem[]>('/admin/product-categorie-list');
}

export function getProductBrands() {
  return http.get('/admin/product-brand-list');
}

export function getProducts(params?: Record<string, unknown>) {
  return http.get<ProductListItem[]>('/admin/product-list', { params });
}

export function assignProducts(campaignId: string | number, payload: Record<string, unknown>) {
  return http.post(`/admin/campaigns/${campaignId}/assign-products`, payload);
}

export function updateDiscount(campaignId: string | number, payload: Record<string, unknown>) {
  return http.put(`/admin/campaigns/${campaignId}/update-discount`, payload);
}

export function updateCampaignProduct(id: string | number, payload: Record<string, unknown>) {
  return http.put(`/admin/campaign-products/${id}/update`, payload);
}

export function removeCampaignProduct(id: string | number) {
  return http.delete(`/admin/campaign-products/${id}/remove`);
}

export function addCampaignImage(campaignId: string | number, payload: FormData) {
  return http.post(`/admin/campaigns/${campaignId}/images`, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
}

