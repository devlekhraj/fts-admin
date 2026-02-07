import { http } from './http';

export interface AdminProfile {
  id?: string;
  name?: string;
  email?: string;
  [key: string]: unknown;
}

export interface LoginResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  admin?: AdminProfile;
}

export async function login(email: string, password: string): Promise<LoginResponse> {
  const { data } = await http.post<LoginResponse>('/auth/admin/login', { email, password });
  return data;
}

export async function me(): Promise<AdminProfile> {
  const { data } = await http.get<AdminProfile>('/auth/admin/me');
  return data;
}

export async function logout(): Promise<void> {
  await http.post('/auth/admin/logout');
}

export async function refresh(): Promise<LoginResponse> {
  const { data } = await http.post<LoginResponse>('/auth/admin/refresh');
  return data;
}
