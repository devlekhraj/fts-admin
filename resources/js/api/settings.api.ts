import { http } from './http';

export function listSettings() {
	return http.get('/admin/settings');
}

export function updateSettings(module: string, payload: any) {
	return http.put(`/admin/settings/${module}`, payload);
}
