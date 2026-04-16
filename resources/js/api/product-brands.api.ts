import { http } from './http';

export type ProductBrandLiteItem = {
  id: number | string;
  name: string;
  slug?: string | null;
  thumb?: string | null;
  [key: string]: unknown;
};

// Used for selectors; returns plain array.
// Fetches all brands via admin brands list endpoint.
export async function listProductBrandsLite(): Promise<ProductBrandLiteItem[]> {
  const response = await http.get('/admin/brands', { params: { per_page: -1 } });
  const wrapped = response as { data?: unknown };
  if (wrapped && Array.isArray(wrapped.data)) {
    return (wrapped.data as Array<Record<string, unknown>>)
      .map((brand) => ({
        id: (brand as any).id,
        name: String((brand as any).name ?? ''),
        slug: (brand as any).slug ?? null,
        thumb: (brand as any).thumb ?? null,
      }))
      .filter((entry) => entry.id !== undefined && entry.id !== null && entry.name.length > 0);
  }

  if (Array.isArray(response)) return response as ProductBrandLiteItem[];
  return [];
}
