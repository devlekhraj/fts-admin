export function formatLongDate(value: unknown): string | null {
  if (typeof value !== 'string' || !value) return null;

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return null;

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(date);
}

export function formatPhoneNumber(value: unknown): string {
  if (typeof value !== 'string' || !value) return '-';

  const digits = value.replace(/\D/g, '');
  if (digits.length === 10) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
  }

  return value;
}

export function underscoreToSpace(value: unknown): string {
  if (value === null || value === undefined) return '';
  return String(value).replaceAll('_', ' ');
}

export function dashToSpace(value: unknown): string {
  if (value === null || value === undefined) return '';
  return String(value).replaceAll('-', ' ');
}

export function formatBytes(value: number | null | undefined): string {
  const size = Number(value ?? 0);
  if (!Number.isFinite(size) || size <= 0) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  const index = Math.min(Math.floor(Math.log(size) / Math.log(1024)), units.length - 1);
  const amount = size / 1024 ** index;
  return `${amount.toFixed(index === 0 ? 0 : 2)} ${units[index]}`;
}

export function formatMetaValue(key: string | number, value: unknown): string {
  const normalizedKey = String(key);
  if ((normalizedKey === 'start_date' || normalizedKey === 'end_date') && value) {
    return formatLongDate(value) ?? String(value);
  }
  return value === null || value === undefined || value === '' ? '-' : String(value);
}

export function getDisplayLink(meta: Record<string, unknown> | undefined, fileUrl?: string | null): string {
  const link = meta?.link;
  if (typeof link === 'string' && link.trim()) return link;
  if (typeof fileUrl === 'string' && fileUrl.trim()) return fileUrl;
  return '-';
}
