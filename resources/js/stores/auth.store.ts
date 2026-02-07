import { defineStore } from 'pinia';
import * as authApi from '@/api/auth.api';

export interface AdminProfile {
	id?: string;
	name?: string;
	email?: string;
	[key: string]: unknown;
}

export const useAuthStore = defineStore('auth', {
	state: () => ({
		token: null as string | null,
		admin: null as AdminProfile | null,
	}),
	getters: {
		isAuthenticated: (state) => Boolean(state.token),
	},
	actions: {
		initFromStorage() {
			this.token = localStorage.getItem('admin_token');
		},
		async login(email: string, password: string) {
			const response = await authApi.login(email, password);
			this.token = response.access_token;
			localStorage.setItem('admin_token', response.access_token);
			this.admin = response.admin ?? null;
		},
		async fetchMe() {
			const admin = await authApi.me();
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
	},
});
