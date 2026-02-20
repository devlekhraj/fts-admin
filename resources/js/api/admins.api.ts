import { http } from './http';

export type ListAdminsParams = {
    page?: number;
    per_page?: number;
    search?: string;
    role_id?: string | number;
};

export function list(params?: ListAdminsParams) {
    return http.get('/admin/admin-list', { params });
}

export type CreateAdminPayload = {
    name: string;
    email: string;
    username: string;
    role_id: string | number | null;
    password: string;
    confirm_password: string;
};

export function create(payload: CreateAdminPayload) {
    return http.post('/admin/admin-create', payload);
}

export type UpdateAdminBasicPayload = {
    id: string | number;
    name: string;
    username: string;
    role_id: string | number | null;
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
