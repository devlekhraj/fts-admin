import { http } from './http';

export function listSettings() {
	return http.get('/admin/settings');
}
