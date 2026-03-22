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

export function formatTime12h(time24: { split: (arg0: string) => [any, any]; }) {
  if (!time24) return 'N/A'
  const [hourStr, minute] = time24.split(':')
  let hour = parseInt(hourStr, 10)

  const ampm = hour >= 12 ? 'PM' : 'AM'
  hour = hour % 12
  if (hour === 0) hour = 12

  return `${hour}:${minute} ${ampm}`
}

export function formatDate(date: string | number | Date) {
  if (!date) return 'N/A'
  const d = new Date(date)

  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')

  return `${year}/${month}/${day}`
}

export function formatDateLong(date: string | number | Date) {
  if (!date) return 'N/A'
  const d = new Date(date)

  // Use Intl.DateTimeFormat for proper month abbreviation
  const month = new Intl.DateTimeFormat('en', { month: 'short' }).format(d)
  const day = d.getDate()
  const year = d.getFullYear()

  return `${month} ${day}, ${year}`
}


export function timeAgo(date: string | number | Date) {
  if (!date) return 'N/A';

  const now = new Date();
  const past = new Date(date);
  if (Number.isNaN(past.getTime())) return 'Invalid date';

  const diffInSeconds = Math.floor((now.getTime() - past.getTime()) / 1000);

  const units = [
    { max: 60, value: 1, names: ['second', 'seconds'] },
    { max: 3600, value: 60, names: ['minute', 'minutes'] },
    { max: 86400, value: 3600, names: ['hour', 'hours'] },
    { max: 604800, value: 86400, names: ['day', 'days'] },
    { max: 2592000, value: 604800, names: ['week', 'weeks'] },
    { max: 31536000, value: 2592000, names: ['month', 'months'] },
    { max: Infinity, value: 31536000, names: ['year', 'years'] },
  ];

  for (const unit of units) {
    if (diffInSeconds < unit.max) {
      const value = Math.floor(diffInSeconds / unit.value);
      const name = value === 1 ? unit.names[0] : unit.names[1];
      return `${value} ${name} ago`;
    }
  }

  return 'a long time ago';
}


export function formatAmount(amount: number | bigint | null) {
  if (amount == null || (typeof amount === 'number' && Number.isNaN(amount))) return 'N/A';

  return 'Rs. ' + new Intl.NumberFormat('en-IN').format(amount as number);
}

export function formatDateTime(date: string | number | Date) {
  if (!date) return 'N/A'
  const d = new Date(date)

  const datePart = d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })

  let hours = d.getHours()
  const minutes = String(d.getMinutes()).padStart(2, '0')
  const ampm = hours >= 12 ? 'pm' : 'am'

  hours = hours % 12
  hours = hours ? hours : 12 // the hour '0' should be '12'

  return `${datePart} ${hours}:${minutes} ${ampm}`
}

