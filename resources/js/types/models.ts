export type Product = {
  id: string;
  name: string;
  price: number;
};

export type Category = {
  id: string;
  name: string;
};

export interface Campaign {
  id: number | string;
  title: string;
  slug: string;
  start_date: string;
  end_date: string;
  is_published: boolean | number;
  thumb?: { url: string; alt_text?: string } | null;
  files?: any[];
}
export interface ProductListItem {
  id: number | string;
  name: string;
  price: number;
  slug: string;
  thumb:string,
}
export interface ProductCategoryListItem {
  id: number | string;
  slug: string;
  thumb:string,
  title: string;
}

export interface CampaignProductListItem {
  id: number | string;
  product_id: number | string;
  name: string;
  thumb: {
    url: string | null;
    alt_text: string | null;
  };
  price: {
    original_price: number;
    discounted_price: number;
  };
  discount: {
    type: 'fixed' | 'percentage';
    value: number;
  };
}
