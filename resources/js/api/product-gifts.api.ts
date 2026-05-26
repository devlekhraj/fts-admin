import { http } from './http';

export type ProductGiftsResponse = {
  success?: boolean;
  data: ProductGiftItem[];
  message?: string;
};

export type ProductGiftItem = {
  id: string | number;
  name?: string | null;
  slug?: string | null;
  thumb?: string | null;
  price?: number | null;
  status: boolean;
};

export async function getProductGifts(productId: string | number): Promise<ProductGiftItem[]> {
  const resp = (await http.get(`/admin/products/${productId}/gifts`)) as ProductGiftsResponse;
  return (resp as any)?.data ?? [];
}

export async function syncProductGifts(
  productId: string | number,
  giftIds: Array<string | number>,
): Promise<ProductGiftItem[]> {
  const resp = (await http.put(`/admin/products/${productId}/gifts`, { gift_ids: giftIds })) as ProductGiftsResponse;
  return (resp as any)?.data ?? [];
}

export async function storeProductGifts(
  productId: string | number,
  giftIds: Array<string | number>,
): Promise<ProductGiftItem[]> {
  const resp = (await http.post(`/admin/products/${productId}/gifts`, { gift_ids: giftIds })) as ProductGiftsResponse;
  return (resp as any)?.data ?? [];
}

export async function deleteProductGift(productId: string | number, giftId: string | number): Promise<void> {
  await http.delete(`/admin/products/${productId}/gifts/${giftId}`);
}
