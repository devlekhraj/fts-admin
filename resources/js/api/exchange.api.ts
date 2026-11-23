import { http } from './http';

export type ExchangeStatus = 'pending' | 'under_review' | 'approved' | 'rejected' | 'cancelled' | 'completed';

export type ExchangeRequestListItem = {
  id: number;
  reference: string;
  status: ExchangeStatus;
  customer_name: string;
  customer_phone: string;
  device_name: string;
  serial_number: string | null;
  device_type: 'phone' | 'laptop';
  target_name: string | null;
  customer_estimate: number;
  admin_offer: number | null;
  needs_manual_review: boolean;
  hold: boolean;
  created_at: string;
};

export type ExchangeAuditMeta = {
  admin_name?: string | null;
  rule_version?: string | null;
  base_value?: number | null;
  calculated_value?: number | null;
  final_offer?: number | null;
  inspection_note?: string | null;
  override_reason?: string | null;
  cap_override_reason?: string | null;
  flags?: string[];
  hold?: boolean;
} | null;

export type ExchangeRequestLog = {
  id: number;
  from_status: string | null;
  to_status: string;
  changed_by_type: string;
  note: string | null;
  meta: ExchangeAuditMeta;
  created_at: string;
};

export type ExchangeEstimateBreakdown = {
  model?: string;
  storage_gb?: number;
  base_value?: number;
  total_deduction_percent?: number;
  capped?: boolean;
  confidence?: string;
  needs_manual_review?: boolean;
  deductions?: Record<string, { label: string; percent: number; amount: number }>;
  [key: string]: unknown;
} | unknown[] | null;

export type ExchangeRequestDetail = ExchangeRequestListItem & {
  serial_number: string;
  photo_url: string | null;
  condition_answers: Record<string, unknown> | null;
  estimate_breakdown: ExchangeEstimateBreakdown;
  target_name: string | null;
  target_price: number | null;
  pickup_info: Record<string, unknown> | null;
  is_satisfied: boolean | null;
  expected_amount: number | null;
  satisfaction_reason: string | null;
  admin_note: string | null;
  customer_note: string | null;
  allowed_statuses: ExchangeStatus[];
  rule_version: string | null;
  flags: string[];
  approval_requires: { inspection_note: boolean; override_reason: boolean; cap_floor: number | null };
  logs: ExchangeRequestLog[];
};

export type ExchangeRequestListResponse = {
  data: ExchangeRequestListItem[];
  meta: { current_page: number; per_page: number; total: number; last_page: number };
  counts: Record<string, number>;
};

export type ExchangeValuation = {
  id: number;
  brand: string;
  model: string;
  storage_gb: number;
  base_value: number;
  admin_override: number | null;
  final_value: number;
  confidence: 'verified' | 'estimate' | 'low';
  evidence_note: string | null;
  effective_date: string | null;
  is_active: boolean;
};

export type ExchangeDamageRule = {
  id: number;
  category: 'display' | 'battery' | 'back_panel';
  label: string;
  deduction_percent: number;
  guidance: string | null;
  requires_manual_review: boolean;
  is_active: boolean;
};

export type ExchangeStatusPayload = {
  status: ExchangeStatus;
  admin_offer?: number | null;
  admin_note?: string | null;
  customer_note?: string | null;
  inspection_note?: string | null;
  override_reason?: string | null;
  cap_override_reason?: string | null;
};

export type ExchangePricingAuditEntry = {
  id: number;
  entity: 'valuation' | 'rule';
  entity_id: number;
  summary: string;
  changes: Record<string, { from: unknown; to: unknown }>;
  admin_name: string | null;
  created_at: string;
};

export async function listExchangeRequests(params?: Record<string, unknown>): Promise<ExchangeRequestListResponse> {
  return (await http.get('/admin/exchange-requests', { params })) as unknown as ExchangeRequestListResponse;
}

export async function getExchangeRequest(id: number | string): Promise<{ data: ExchangeRequestDetail }> {
  return (await http.get(`/admin/exchange-requests/${id}`)) as unknown as { data: ExchangeRequestDetail };
}

export async function updateExchangeRequestStatus(id: number | string, payload: ExchangeStatusPayload) {
  return (await http.post(`/admin/exchange-requests/${id}/status`, payload)) as unknown as { success: boolean; message: string };
}

export async function listExchangeValuations(): Promise<{ data: ExchangeValuation[] }> {
  return (await http.get('/admin/exchange-valuations')) as unknown as { data: ExchangeValuation[] };
}

export async function createExchangeValuation(payload: {
  brand?: string;
  model: string;
  storage_gb: number;
  base_value: number;
  confidence?: ExchangeValuation['confidence'];
  evidence_note?: string | null;
}) {
  return (await http.post('/admin/exchange-valuations', payload)) as unknown as { success: boolean; message: string; data: ExchangeValuation };
}

export async function updateExchangeValuation(
  id: number,
  payload: Pick<ExchangeValuation, 'base_value' | 'admin_override' | 'confidence' | 'evidence_note' | 'is_active'>,
) {
  return (await http.put(`/admin/exchange-valuations/${id}`, payload)) as unknown as { success: boolean; message: string; data: ExchangeValuation };
}

export async function listExchangeDamageRules(): Promise<{ data: ExchangeDamageRule[] }> {
  return (await http.get('/admin/exchange-damage-rules')) as unknown as { data: ExchangeDamageRule[] };
}

export async function updateExchangeDamageRule(
  id: number,
  payload: Pick<ExchangeDamageRule, 'deduction_percent' | 'guidance' | 'requires_manual_review' | 'is_active'>,
) {
  return (await http.put(`/admin/exchange-damage-rules/${id}`, payload)) as unknown as { success: boolean; message: string; data: ExchangeDamageRule };
}

export async function listExchangePricingAudit(): Promise<{ data: ExchangePricingAuditEntry[] }> {
  return (await http.get('/admin/exchange-pricing-audit')) as unknown as { data: ExchangePricingAuditEntry[] };
}

export type ExchangeCreatePayload = {
  customer_name: string;
  customer_phone: string;
  user_id: number | null;
  device_type: 'phone' | 'laptop';
  device_name: string;
  serial_number: string;
  estimated_value: number;
  target_name: string | null;
  target_price: number | null;
  admin_note: string | null;
};

/** Staff enter a request for a customer who is at the counter or on the phone. */
export async function createExchangeRequest(payload: ExchangeCreatePayload) {
  return (await http.post('/admin/exchange-requests', payload)) as unknown as {
    success: boolean;
    message: string;
    data: { id: number; reference: string };
  };
}

export type ExchangeBulkResult = {
  success: boolean;
  message: string;
  data: { updated: number[]; skipped: { id: number; reference: string | null; reason: string }[] };
};

/** The same status change for several requests. Approving is not allowed here: it needs each request's offer and note. */
export async function bulkUpdateExchangeStatus(payload: {
  ids: number[];
  status: 'under_review' | 'rejected' | 'completed';
  customer_note?: string | null;
}) {
  return (await http.post('/admin/exchange-requests/bulk-status', payload)) as unknown as ExchangeBulkResult;
}
