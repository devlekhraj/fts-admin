import { http } from './http';

export type ListFilesParams = {
  page?: number;
  per_page?: number;
  search?: string;
  tag?: string;
};

export type FileUsageItem = {
  id: number | string;
  usage_type?: string | null;
  usage_id?: number | string | null;
  title?: string | null;
  alt_text?: string | null;
  meta?: Record<string, unknown> | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type FileListItem = {
  id: number | string;
  file_name?: string | null;
  file_path?: string | null;
  title?: string | null;
  url?: string | null;
  file_size?: number | null;
  size?: number | null;
  width?: number | null;
  height?: number | null;
  created_at?: string | null;
  tags?: string[];
  usage_count?: number;
  usage_types?: string[];
  usages?: FileUsageItem[];
  [key: string]: unknown;
};

export type FileListMeta = {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
};

export type FileListResponse = {
  data: FileListItem[];
  meta?: FileListMeta;
  tags?: string[];
};

export function fileUpload(payload: FormData) {
  return http.post('/admin/file-upload', payload);
}

export async function listFiles(params?: ListFilesParams): Promise<FileListResponse> {
  const response = await http.get('/admin/file-list', { params });
  return response as unknown as FileListResponse;
}

export async function listFilesWithUsages(params?: ListFilesParams): Promise<FileListResponse> {
  const response = await http.get('/admin/file-list-with-usages', { params });
  return response as unknown as FileListResponse;
}
