export type EmiType = 'credit_card' | 'new_credit_card' | 'citizenship' | string;

export function getEmiTypeIcon(type: EmiType) {
  switch (type) {
    case 'credit_card':
      return 'mdi-credit-card-check';
    case 'new_credit_card':
      return 'mdi-credit-card-plus';
    case 'citizenship':
      return 'mdi-card-account-details';
    default:
      return 'mdi-card-account-details';
  }
}

export function getEmiIconColor(type: EmiType) {
  switch (type) {
    case 'credit_card':
      return 'info';
    case 'new_credit_card':
      return 'purple';
    case 'citizenship':
      return 'success';
    default:
      return 'success';
  }
}

export function getEmiIconTextColor(type: EmiType) {
  switch (type) {
    case 'credit_card':
      return 'text-info';
    case 'new_credit_card':
      return 'text-purple';
    case 'citizenship':
      return 'text-success';
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
