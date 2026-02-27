import axios from 'axios';

function resolveApiBaseUrl(): string {
  const rawBaseUrl = (import.meta.env.VITE_API_BASE_URL as string | undefined)?.trim();

  if (!rawBaseUrl) {
    return '/api';
  }

  if (rawBaseUrl.startsWith('/')) {
    return rawBaseUrl.endsWith('/api') ? rawBaseUrl : `${rawBaseUrl.replace(/\/+$/, '')}/api`;
  }

  try {
    const url = new URL(rawBaseUrl);
    url.pathname = url.pathname.replace(/\/+$/, '');
    if (!url.pathname.endsWith('/api')) {
      url.pathname = `${url.pathname}/api`.replace(/\/{2,}/g, '/');
    }
    return url.toString().replace(/\/+$/, '');
  } catch {
    return '/api';
  }
}

const baseURL = resolveApiBaseUrl();

export const http = axios.create({
  baseURL,
});

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token');
  if (token) {
    config.headers = config.headers ?? {};
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem('admin_token');
      if (!window.location.pathname.startsWith('/admin/login')) {
        window.location.href = '/admin/login';
      }
    }
    return Promise.reject(error);
  },
);
