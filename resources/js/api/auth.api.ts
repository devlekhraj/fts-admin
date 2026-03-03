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

function unwrap<T>(response: unknown): T {
  const payload = response as { data?: unknown };
  if (payload && typeof payload === 'object' && 'data' in payload && payload.data) {
    return payload.data as T;
  }
  return response as T;
}

export async function login(email: string, password: string): Promise<LoginResponse> {
  const response = await http.post<LoginResponse>('/auth/admin/login', { email, password });
  return unwrap<LoginResponse>(response);
}

export async function profile(): Promise<AdminProfile> {
  const response = await http.get<AdminProfile>('/auth/admin/me');
  return unwrap<AdminProfile>(response);
}

export async function logout(): Promise<void> {
  await http.post('/auth/admin/logout');
}

export async function refresh(): Promise<LoginResponse> {
  const response = await http.post<LoginResponse>('/auth/admin/refresh');
  return unwrap<LoginResponse>(response);
}
