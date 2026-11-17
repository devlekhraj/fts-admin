<template>
  <div>
    <AppPageHeader :title="request ? `Exchange ${request.reference}` : 'Exchange request'" subtitle="Review the device and decide the offer">
      <template #actions>
        <v-btn variant="text" :to="{ name: 'admin.exchange.requests' }">Back to list</v-btn>
      </template>
    </AppPageHeader>

    <v-container fluid>
      <v-skeleton-loader v-if="loading" type="card, card" />
      <v-alert v-else-if="!request" type="error" variant="tonal">This request could not be loaded.</v-alert>

      <v-row v-else>
        <v-col cols="12" lg="8">
          <v-card variant="flat" class="mb-4">
            <v-card-title class="d-flex align-center justify-space-between flex-wrap ga-2">
              <span>Customer and device</span>
              <v-chip :color="exchangeStatusColor(request.status)" variant="tonal" label>{{ exchangeStatusLabel(request.status) }}</v-chip>
            </v-card-title>
            <v-card-text>
              <v-row dense>
                <v-col cols="12" md="6"><div class="label">Customer</div>{{ request.customer_name }}</v-col>
                <v-col cols="12" md="6"><div class="label">Phone</div>{{ request.customer_phone }}</v-col>
                <v-col cols="12" md="6"><div class="label">Device</div>{{ request.device_name }}</v-col>
                <v-col cols="12" md="6"><div class="label">IMEI / serial</div>{{ request.serial_number }}</v-col>
                <v-col cols="12" md="6"><div class="label">Submitted</div>{{ formatDateTime(request.created_at) }}</v-col>
                <v-col cols="12" md="6">
                  <div class="label">Wants to buy</div>
                  {{ request.target_name || '-' }}<span v-if="request.target_price"> ({{ formatNPR(request.target_price) }})</span>
                </v-col>
              </v-row>
              <div class="mt-4">
                <div class="label">Device photo</div>
                <a v-if="request.photo_url" :href="request.photo_url" target="_blank" rel="noopener noreferrer">
                  <v-img :src="request.photo_url" max-width="260" max-height="260" class="rounded border" />
                  <span class="text-caption">Open full size (link expires after a few hours)</span>
                </a>
                <span v-else class="text-medium-emphasis">No photo sent</span>
              </div>
            </v-card-text>
          </v-card>

          <v-card variant="flat" class="mb-4">
            <v-card-title>Customer's estimate</v-card-title>
            <v-card-text>
              <div class="text-h6 mb-2">{{ formatNPR(request.customer_estimate) }}</div>
              <v-alert v-if="request.needs_manual_review" type="warning" variant="tonal" density="compact" class="mb-3">
                Check this device in person before confirming a price (low-confidence model, severe damage, or an estimate made on the old form).
                <ul v-if="breakdown?.flags?.length" class="mt-2 ml-4">
                  <li v-for="flag in breakdown.flags" :key="flag">{{ flag }}</li>
                </ul>
              </v-alert>

              <template v-if="breakdown">
                <div class="mb-2">
                  <strong>{{ breakdown.model }}</strong> {{ breakdown.storage_gb }} GB, clean value {{ formatNPR(breakdown.base_value) }}
                </div>
                <v-table density="compact">
                  <thead><tr><th>Condition</th><th class="text-right">Deduction</th><th class="text-right">Amount</th></tr></thead>
                  <tbody>
                    <tr v-for="(d, key) in breakdown.deductions" :key="key">
                      <td>{{ d.label }}</td>
                      <td class="text-right">{{ d.percent }}%</td>
                      <td class="text-right">- {{ formatNPR(d.amount) }}</td>
                    </tr>
                    <tr>
                      <td><strong>Total deduction<span v-if="breakdown.capped"> (capped)</span></strong></td>
                      <td class="text-right"><strong>{{ breakdown.total_deduction_percent }}%</strong></td>
                      <td />
                    </tr>
                  </tbody>
                </v-table>
              </template>
              <div v-else-if="legacyAnswers.length" class="text-body-2">
                <div v-for="row in legacyAnswers" :key="row.k"><span class="text-medium-emphasis">{{ row.k }}:</span> {{ row.v }}</div>
              </div>
            </v-card-text>
          </v-card>

          <v-card variant="flat" class="mb-4">
            <v-card-title>Pickup and customer's expectation</v-card-title>
            <v-card-text>
              <div v-if="pickupLines.length" class="mb-3">
                <div v-for="row in pickupLines" :key="row.k"><span class="text-medium-emphasis">{{ row.k }}:</span> {{ row.v }}</div>
              </div>
              <div v-else class="text-medium-emphasis mb-3">No pickup details.</div>
              <div v-if="request.is_satisfied === false">
                Customer wants {{ request.expected_amount ? formatNPR(request.expected_amount) : 'more' }}.
                <span v-if="request.satisfaction_reason" class="text-medium-emphasis">Reason: {{ request.satisfaction_reason }}</span>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" lg="4">
          <v-card variant="flat" class="mb-4">
            <v-card-title>Decision</v-card-title>
            <v-card-text>
              <div class="mb-3">
                <div class="label">Our offer</div>
                <div class="text-h6">{{ request.admin_offer === null ? 'Not set' : formatNPR(request.admin_offer) }}</div>
              </div>
              <div v-if="request.customer_note" class="mb-3"><div class="label">Message shown to the customer</div>{{ request.customer_note }}</div>
              <div v-if="request.admin_note" class="mb-3"><div class="label">Internal note</div>{{ request.admin_note }}</div>

              <div v-if="request.allowed_statuses.length" class="d-flex flex-column ga-2">
                <v-btn v-if="can('under_review')" color="info" variant="flat" @click="open('under_review')">Mark under review</v-btn>
                <v-btn v-if="can('approved')" color="success" variant="flat" @click="open('approved')">Approve with offer</v-btn>
                <v-btn v-if="can('completed')" color="primary" variant="flat" @click="open('completed')">Mark completed</v-btn>
                <v-btn v-if="can('rejected')" color="error" variant="outlined" @click="open('rejected')">Reject</v-btn>
              </div>
              <div v-else class="text-medium-emphasis">This request is closed.</div>
            </v-card-text>
          </v-card>

          <v-card variant="flat">
            <v-card-title>History</v-card-title>
            <v-card-text>
              <div v-for="log in request.logs" :key="log.id" class="mb-3">
                <div>
                  <strong>{{ exchangeStatusLabel(log.to_status) }}</strong>
                  <span class="text-medium-emphasis"> by {{ log.changed_by_type }}</span>
                </div>
                <div class="text-caption text-medium-emphasis">{{ formatDateTime(log.created_at) }}</div>
                <div v-if="log.note" class="text-body-2">{{ log.note }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <v-dialog v-model="dialog" max-width="480" persistent>
      <v-card>
        <v-card-title>{{ dialogTitle }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-if="target === 'approved'"
            v-model="offer"
            label="Offer amount (NPR)"
            type="number"
            min="0"
            variant="outlined"
            density="comfortable"
            :error-messages="errors.admin_offer"
          />
          <v-textarea
            v-if="target === 'rejected' || target === 'approved'"
            v-model="customerNote"
            :label="target === 'rejected' ? 'Reason shown to the customer' : 'Message shown to the customer (optional)'"
            rows="3"
            auto-grow
            variant="outlined"
            :error-messages="errors.customer_note"
          />
          <v-textarea v-model="adminNote" label="Internal note (optional)" rows="2" auto-grow variant="outlined" />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" :disabled="saving" @click="dialog = false">Cancel</v-btn>
          <v-btn :color="target === 'rejected' ? 'error' : 'primary'" variant="flat" :loading="saving" @click="submit">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import {
  getExchangeRequest,
  updateExchangeRequestStatus,
  type ExchangeRequestDetail,
  type ExchangeStatus,
} from '@/api/exchange.api';
import { exchangeStatusColor, exchangeStatusLabel } from '@/shared/exchange';
import { formatDateTime, formatNPR } from '@/shared/formatters';
import { useSnackbar } from '@/composables/snackbar';

type Breakdown = {
  model?: string;
  storage_gb?: number;
  base_value?: number;
  total_deduction_percent?: number;
  capped?: boolean;
  flags?: string[];
  deductions: Record<string, { label: string; percent: number; amount: number }>;
};

const route = useRoute();
const { showSuccess, showError } = useSnackbar();

const request = ref<ExchangeRequestDetail | null>(null);
const loading = ref(true);
const dialog = ref(false);
const saving = ref(false);
const target = ref<ExchangeStatus>('under_review');
const offer = ref('');
const customerNote = ref('');
const adminNote = ref('');
const errors = ref<Record<string, string[]>>({});

const breakdown = computed<Breakdown | null>(() => {
  const b = request.value?.estimate_breakdown as Breakdown | null | undefined;
  return b && !Array.isArray(b) && b.deductions ? b : null;
});

function toLines(source: unknown): { k: string; v: string }[] {
  if (!source || typeof source !== 'object') return [];
  return Object.entries(source as Record<string, unknown>)
    .filter(([, v]) => v !== null && v !== '' && typeof v !== 'object')
    .map(([k, v]) => ({ k: k.replace(/_/g, ' '), v: String(v) }));
}

const legacyAnswers = computed(() => (breakdown.value ? [] : toLines(request.value?.condition_answers)));
const pickupLines = computed(() => toLines(request.value?.pickup_info));

const dialogTitle = computed(() => ({
  under_review: 'Mark this request under review',
  approved: 'Approve and set the offer',
  rejected: 'Reject this request',
  completed: 'Mark this exchange completed',
  pending: '',
  cancelled: '',
}[target.value]));

function can(status: ExchangeStatus) {
  return request.value?.allowed_statuses.includes(status) ?? false;
}

function open(status: ExchangeStatus) {
  target.value = status;
  offer.value = status === 'approved' && request.value ? String(request.value.admin_offer ?? request.value.customer_estimate ?? '') : '';
  customerNote.value = '';
  adminNote.value = '';
  errors.value = {};
  dialog.value = true;
}

async function load() {
  loading.value = true;
  try {
    request.value = (await getExchangeRequest(String(route.params.id))).data;
  } catch {
    request.value = null;
  } finally {
    loading.value = false;
  }
}

async function submit() {
  if (!request.value || saving.value) return;
  saving.value = true;
  errors.value = {};
  try {
    await updateExchangeRequestStatus(request.value.id, {
      status: target.value,
      admin_offer: target.value === 'approved' ? Number(offer.value) : null,
      admin_note: adminNote.value.trim() || null,
      customer_note: customerNote.value.trim() || null,
    });
    showSuccess('Request updated');
    dialog.value = false;
    await load();
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })?.response?.data;
    errors.value = data?.errors ?? {};
    showError(data?.message ?? 'Could not update the request');
  } finally {
    saving.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.label {
  font-size: 12px;
  color: rgb(var(--v-theme-on-surface-variant, 100, 100, 100));
  margin-bottom: 2px;
}
</style>
