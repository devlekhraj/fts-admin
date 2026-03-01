import { http } from './http';

export type ListCustomersParams = {
    page?: number;
    per_page?: number;
    search?: string;
};

export type CustomersListMeta = {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
};

export type CustomersListItem = {
    id: number | string;
    name?: string | null;
    email?: string | null;
    mobile?: string | null;
    contact_number?: string | null;
    avatar_url?: string | null;
    email_verified_at?: string | null;
    created_at?: string | null;
    [key: string]: unknown;
};

export type CustomersListResponse = {
    data: CustomersListItem[];
    meta?: CustomersListMeta;
    total?: number;
};

export async function list(params?: ListCustomersParams): Promise<CustomersListResponse> {
    const response = await http.get('/admin/customer-list', { params });
    return response as CustomersListResponse;
}
