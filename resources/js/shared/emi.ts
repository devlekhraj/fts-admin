export type EmiType = 'credit_card' | 'new_credit_card' | 'citizenship' | string;

export function getEmiTypeIcon(type: EmiType) {
  switch (type) {
    case 'credit_card':
      return 'mdi-credit-card-outline';
    case 'apply_card':
      return 'mdi-credit-card-plus-outline';
    case 'citizenship':
      return 'mdi-badge-account-horizontal-outline';
    default:
      return 'mdi-file-check-outline';
  }
}

export function getEmiIconColor(type: EmiType) {
  switch (type) {
    case 'credit_card':
      return 'success';
    case 'apply_card':
      return 'warning';
    case 'citizenship':
      return 'info';
    default:
      return 'success';
  }
}

export function getEmiIconTextColor(type: EmiType) {
  switch (type) {
    case 'credit_card':
      return 'text-success';
    case 'new_credit_card':
      return 'text-warning';
    case 'citizenship':
      return 'text-info';
    default:
      return 'text-success';
  }
}

export function statusColor(status: number | string) {
  const value = typeof status === 'string' ? Number(status) : status;
  switch (value) {
    case 0:
      return 'warning';
    case 1:
      return 'blue';
    case 2:
      return 'green';
    case 3:
      return 'indigo';
    case 4:
      return 'red';
    default:
      return 'black';
  }
}
