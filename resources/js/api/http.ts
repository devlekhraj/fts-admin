import axios from 'axios';

function resolveApiBaseUrl(): string {
  const rawBaseUrl = (import.meta.env.VITE_API_BASE_URL as string | undefined)?.trim();
  const mode = String(import.meta.env.MODE ?? '');
  const isProductionMode = mode === 'production';
  const fallbackBaseUrl = isProductionMode
    ? 'https://dev.fatafatsewa.com/api'
    : 'https://fatafat.test/api';

  if (!rawBaseUrl) {
    return fallbackBaseUrl;
  }

  if (rawBaseUrl.startsWith('/')) {
    return rawBaseUrl.replace(/\/+$/, '');
  }

  try {
    const url = new URL(rawBaseUrl);
    url.pathname = url.pathname.replace(/\/+$/, '');
    return url.toString().replace(/\/+$/, '');
  } catch {
    return fallbackBaseUrl;
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
  (response) => response.data,
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
