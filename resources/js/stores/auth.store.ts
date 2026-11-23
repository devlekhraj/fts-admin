import { defineStore } from 'pinia';
import * as authApi from '@/api/auth.api';

export interface AdminProfile {
	id?: string | number;
	name?: string;
	email?: string;
	role?: string | null;
	role_id?: number | string | null;
	/** The Administrator role can do everything. */
	is_super?: boolean;
	/** What the role allows. The server checks every request again; this only decides what the screens show. */
	permissions?: string[];
	[key: string]: unknown;
}

export const useAuthStore = defineStore('auth', {
	state: () => ({
		token: null as string | null,
		admin: null as AdminProfile | null,
	}),
	getters: {
		isAuthenticated: (state) => Boolean(state.token),
		isSuper: (state) => Boolean(state.admin?.is_super),
	},
	actions: {
		initFromStorage() {
			this.token = localStorage.getItem('admin_token');
		},
		async signIn(email: string, password: string) {
			const response = await authApi.login(email, password);
			this.token = response.access_token;
			
			localStorage.setItem('admin_token', response.access_token);
			this.admin = response.admin ?? await authApi.profile();
			return this.admin;
		},
		/** True when the signed-in admin holds any one of these permissions (an Administrator holds all). */
		can(...permissions: string[]) {
			const admin = this.admin;
			if (!admin) return false;
			if (admin.is_super) return true;
			const mine = Array.isArray(admin.permissions) ? admin.permissions : [];
			return permissions.some((permission) => mine.includes(permission));
		},
		async fetchProfile() {
			const admin = await authApi.profile();
			this.admin = admin;
			return admin;
		},
		async logout() {
			try {
				await authApi.logout();
			} catch {
				// ignore
			}
			this.token = null;
			this.admin = null;
			localStorage.removeItem('admin_token');
		},
		async signOut() {
			await this.logout();
			return { name: 'admin.login' } as const;
		},
	},
});
