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

export type FileAssignPayload = {
  usage_type: string;
  usage_id: number | string;
  source: 'existing' | 'upload';
  image_id?: number | string | null;
  file?: File | null;
  alt_text: string;
  caption?: string | null;
  description?: string | null;
  directory?: string | null;
};

export type FileAssignResponse = {
  message: string;
  data?: {
    source?: 'existing' | 'upload';
    file?: FileListItem | null;
    usage?: Record<string, unknown> | null;
  };
};

export function fileAssign(payload: FileAssignPayload) {
  const formData = new FormData();
  formData.append('usage_type', String(payload.usage_type));
  formData.append('usage_id', String(payload.usage_id));
  formData.append('source', payload.source);
  formData.append('alt_text', String(payload.alt_text ?? ''));
  formData.append('caption', String(payload.caption ?? ''));
  formData.append('description', String(payload.description ?? ''));
  if (payload.directory) {
    formData.append('directory', String(payload.directory));
  }

  if (payload.source === 'existing' && payload.image_id !== null && payload.image_id !== undefined) {
    formData.append('image_id', String(payload.image_id));
  }

  if (payload.source === 'upload' && payload.file) {
    formData.append('file', payload.file);
  }

  return http.post<FileAssignResponse, FileAssignResponse>('/admin/file-assign', formData);
}

export type UpdateFileUsagePayload = {
  usage_type?: string;
  usage_id?: number | string;
  alt_text: string;
  link?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  seq_no?: number | null;
  is_default?: boolean;
  is_active?: boolean;
};

export function updateFileUsage(fileUsageId: number | string, payload: UpdateFileUsagePayload) {
  return http.put(`/admin/file-usage/${fileUsageId}`, payload);
}

export function deleteFileUsage(fileUsageId: number | string) {
  return http.delete(`/admin/file-usage/${fileUsageId}`);
}

export async function listFiles(params?: ListFilesParams): Promise<FileListResponse> {
  const response = await http.get('/admin/file-list', { params });
  return response as unknown as FileListResponse;
}

export async function listFilesWithUsages(params?: ListFilesParams): Promise<FileListResponse> {
  const response = await http.get('/admin/file-list-with-usages', { params });
  return response as unknown as FileListResponse;
}
