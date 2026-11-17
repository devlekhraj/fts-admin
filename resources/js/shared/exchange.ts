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
