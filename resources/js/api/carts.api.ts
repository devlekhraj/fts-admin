import { http } from './http';

export type CartListItem = {
  id: number | string;
  customer?: {
    name?: string | null;
    avatar?: string | null;
  } | null;
  is_proceed?: boolean | null;
  items_count?: number | null;
  updated_at?: string | null;
};

export type CartListResponse = {
  data: CartListItem[];
  meta?: {
    current_page?: number;
    per_page?: number;
    total?: number;
    last_page?: number;
    from?: number | null;
    to?: number | null;
  };
};

export type CartDetailItem = {
  id: number | string;
  description: string;
  price: number;
  quantity: number;
  line_total: number;
  product_attributes?: Record<string, unknown>;
};

export type CartDetail = {
  id: number | string;
  customer?: {
    name?: string | null;
    address?: string | null;
    avatar?: string | null;
  } | null;
  is_proceed?: boolean | null;
  items_count?: number | null;
  updated_at?: string | null;
  total?: number | null;
  items?: CartDetailItem[];
};

export type CartDetailResponse = {
  data: CartDetail;
};

export type ListCartsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export async function listCarts(params?: ListCartsParams): Promise<CartListResponse> {
  const response = await http.get('/admin/carts', { params });
  return response as unknown as CartListResponse;
}

export async function getCart(id: number | string): Promise<CartDetailResponse> {
  const response = await http.get(`/admin/carts/${id}`);
  return response as unknown as CartDetailResponse;
}
