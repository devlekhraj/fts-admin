import { http } from './http';

export type AttributeProductItem = {
  id: number | string;
  name?: string | null;
  attributes_count?: number | null;
  created_at?: string | null;
};

export type AttributeProductListResponse = {
  data: AttributeProductItem[];
  success?: boolean;
};

export type AttributeDetailItem = {
  id: number | string;
  name?: string | null;
  type?: string | null;
  values?: unknown;
  use_for_variant?: boolean;
  use_in_filter?: boolean;
  created_at?: string | null;
};

export type AttributeProductDetailResponse = AttributeProductItem & {
  attributes?: AttributeDetailItem[];
};

export type UpdateAttributeValuesPayload = {
  values: string[];
};

export type UpdateAttributeValuesResponse = {
  message?: string;
  data?: {
    id: number | string;
    values: string[];
  };
};

export type UpdateAttributeItemPayload = {
  name: string;
  type: 'text' | 'option';
  use_for_variant: boolean;
  use_in_filter: boolean;
  values?: string[] | null;
};

export type UpdateAttributeItemResponse = {
  message?: string;
  data?: {
    id: number | string;
    name: string;
    type: string;
    use_for_variant: boolean;
    use_in_filter: boolean;
    values: string[];
  };
};

export async function listAttributeProducts(payload: Record<string, unknown> = {}): Promise<AttributeProductListResponse> {
  const response = await http.get('/admin/attribute-list', { params: payload });
  return response as unknown as AttributeProductListResponse;
}

export async function getAttributeProductDetail(id: number | string): Promise<AttributeProductDetailResponse> {
  const response = await http.get(`/admin/attributes/${id}/detail`);
  return response as unknown as AttributeProductDetailResponse;
}

export async function updateAttributeValues(
  classId: number | string,
  attributeId: number | string,
  payload: UpdateAttributeValuesPayload,
): Promise<UpdateAttributeValuesResponse> {
  const response = await http.patch(`/admin/attributes/${classId}/items/${attributeId}/values`, payload);
  return response as unknown as UpdateAttributeValuesResponse;
}

export async function updateAttributeItem(
  classId: number | string,
  attributeId: number | string,
  payload: UpdateAttributeItemPayload,
): Promise<UpdateAttributeItemResponse> {
  const response = await http.patch(`/admin/attributes/${classId}/items/${attributeId}`, payload);
  return response as unknown as UpdateAttributeItemResponse;
}
