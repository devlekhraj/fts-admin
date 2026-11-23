import type { ExchangeStatus } from '@/api/exchange.api';

export const EXCHANGE_STATUS_LABEL: Record<ExchangeStatus, string> = {
  pending: 'Pending',
  under_review: 'Under review',
  approved: 'Approved',
  rejected: 'Rejected',
  cancelled: 'Cancelled',
  completed: 'Completed',
};

const COLORS: Record<ExchangeStatus, string> = {
  pending: 'warning',
  under_review: 'info',
  approved: 'success',
  rejected: 'error',
  cancelled: 'grey',
  completed: 'primary',
};

export function exchangeStatusLabel(status: string): string {
  return EXCHANGE_STATUS_LABEL[status as ExchangeStatus] ?? status;
}

export function exchangeStatusColor(status: string): string {
  return COLORS[status as ExchangeStatus] ?? 'grey';
}

export const EXCHANGE_STATUS_OPTIONS = (Object.keys(EXCHANGE_STATUS_LABEL) as ExchangeStatus[]).map((value) => ({
  title: EXCHANGE_STATUS_LABEL[value],
  value,
}));

/** Upper case, no spaces or dashes: the form saved and compared for duplicates (same as the website). */
export function normalizeDeviceId(value: string): string {
  return value.replace(/[\s-]+/g, '').toUpperCase();
}

function isValidImei(imei: string): boolean {
  if (!/^\d{15}$/.test(imei)) return false;
  let sum = 0;
  for (let i = 0; i < 15; i++) {
    let digit = Number(imei[14 - i]);
    if (i % 2 === 1) {
      digit *= 2;
      if (digit > 9) digit -= 9;
    }
    sum += digit;
  }
  return sum % 10 === 0;
}

/** A message when the IMEI (phones) or serial number (laptops) is not acceptable, otherwise null. Same rules as the server. */
export function checkDeviceId(value: string, isLaptop: boolean): string | null {
  const id = normalizeDeviceId(value ?? '');
  if (isLaptop) {
    if (id === '') return 'Enter the serial number of the laptop';
    return /^[A-Z0-9]{5,40}$/.test(id) ? null : 'The serial number may contain only letters and numbers (5 to 40 characters)';
  }
  if (id === '') return 'Enter the IMEI of the phone';
  if (!/^\d{15}$/.test(id)) return 'The IMEI must be exactly 15 digits. Dial *#06# on the phone to see it';
  return isValidImei(id) ? null : 'This IMEI is not valid. Dial *#06# on the phone to see the correct 15-digit number';
}
