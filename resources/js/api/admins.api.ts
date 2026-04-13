import { http } from './http';

export type AdminListItem = {
    id: number | string;
    avatar_url: string | null;
    name: string | null;
    email: string | null;
    username: string | null;
    role: string | null;
    role_id: number | string | null;
    created_at?: string | null;
};

export type AdminListResponse = {
    data: AdminListItem[];
    meta?: {
        current_page?: number;
        per_page?: number;
        total?: number;
        last_page?: number;
        from?: number | null;
        to?: number | null;
    };
};

export type ListAdminsParams = {
    page?: number;
    per_page?: number;
    search?: string;
    role_id?: string | number;
};

export function list(params?: ListAdminsParams) {
    return http.get<AdminListResponse, AdminListResponse>('/admin/admin-list', { params });
}

export type CreateAdminPayload = {
    name: string;
    email: string;
    username: string;
    role_id: string | number | null;
};

export function create(payload: CreateAdminPayload) {
    return http.post('/admin/admin-create', payload);
}

export type UpdateAdminBasicPayload = {
    id: string | number;
    name: string;
    username: string;
    role_id: string | number | null;
    // passwords are generated server-side when creating admins; basic update does not include password.
};

export function updateBasicInfo(payload: UpdateAdminBasicPayload) {
    return http.put(`/admin/admin-users/${payload.id}/basic-info`, {
        name: payload.name,
        username: payload.username,
        role_id: payload.role_id,
    });
}

export type UpdateAdminPasswordPayload = {
    id: string | number;
    password: string;
    confirm_password: string;
};

export function updatePassword(payload: UpdateAdminPasswordPayload) {
    return http.put(`/admin/admin-users/${payload.id}/password`, {
        password: payload.password,
        confirm_password: payload.confirm_password,
    });
}

export function deleteAdmin(id: string | number) {
    return http.delete(`/admin/admin-users/${id}/delete`);
}
