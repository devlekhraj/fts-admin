import { http } from './http';

export type WishlistListItem = {
  id: number | string;
  user_id: number | string;
  product_id: number | string;
  customer?: {
    name?: string | null;
    avatar?: string | null;
  } | null;
  product?: {
    id?: number | string | null;
    name?: string | null;
    sku?: string | null;
    thumb?: string | null;
  } | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type WishlistListResponse = {
  data: WishlistListItem[];
  meta?: {
    current_page?: number;
    per_page?: number;
    total?: number;
    last_page?: number;
    from?: number | null;
    to?: number | null;
  };
};

export type ListWishlistsParams = {
  page?: number;
  per_page?: number;
  search?: string;
};

export async function listWishlists(params?: ListWishlistsParams): Promise<WishlistListResponse> {
  const response = await http.get('/admin/wishlists', { params });
  return response as unknown as WishlistListResponse;
}

