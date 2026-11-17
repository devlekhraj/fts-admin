<template>
  <div>
    <AppPageHeader title="Exchange Prices" subtitle="What customers are offered for an old iPhone, and the deduction for each condition" />

    <v-container fluid>
      <v-alert type="info" variant="tonal" density="compact" class="mb-4">
        Customers see an estimate made from these numbers. Prices marked "estimate" or "low" came from the planning workbook and
        should be replaced with your own completed purchases. "Low" prices always go to manual review.
      </v-alert>

      <v-tabs v-model="tab" class="mb-4">
        <v-tab value="prices">Prices</v-tab>
        <v-tab value="conditions">Conditions</v-tab>
        <v-tab value="history" @click="loadHistory">Change history</v-tab>
      </v-tabs>

      <v-window v-model="tab">
        <v-window-item value="prices">
          <v-card variant="flat">
            <v-card-text>
              <div class="d-flex align-center flex-wrap ga-3 mb-3">
                <v-text-field v-model="priceSearch" label="Search model" density="compact" variant="outlined" hide-details clearable style="max-width: 260px" />
                <v-spacer />
                <v-btn color="primary" variant="flat" @click="openNew">Add a model and storage</v-btn>
              </div>
              <v-skeleton-loader v-if="loadingPrices" type="table" />
              <v-table v-else density="compact">
                <thead>
                  <tr>
                    <th>Model</th><th>Storage</th><th class="text-right">Clean value</th><th class="text-right">Override</th>
                    <th class="text-right">Offered</th><th>Confidence</th><th>Active</th><th />
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="v in filteredPrices" :key="v.id" :class="{ 'text-medium-emphasis': !v.is_active }">
                    <td>{{ v.model }}</td>
                    <td>{{ v.storage_gb }} GB</td>
                    <td class="text-right">{{ formatNPR(v.base_value) }}</td>
                    <td class="text-right">{{ v.admin_override === null ? '-' : formatNPR(v.admin_override) }}</td>
                    <td class="text-right"><strong>{{ formatNPR(v.final_value) }}</strong></td>
                    <td><v-chip size="x-small" label variant="tonal" :color="confidenceColor(v.confidence)">{{ v.confidence }}</v-chip></td>
                    <td>{{ v.is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-right"><v-btn size="small" variant="outlined" color="primary" @click="openEdit(v)">Edit</v-btn></td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-window-item>

        <v-window-item value="conditions">
          <v-card v-for="group in ruleGroups" :key="group.key" variant="flat" class="mb-4">
            <v-card-title>{{ group.title }}</v-card-title>
            <v-card-text>
              <v-skeleton-loader v-if="loadingRules" type="table" />
              <v-table v-else density="compact">
                <thead><tr><th>Condition</th><th class="text-right">Deduction</th><th>Manual review</th><th>Active</th><th /></tr></thead>
                <tbody>
                  <tr v-for="r in group.rules" :key="r.id" :class="{ 'text-medium-emphasis': !r.is_active }">
                    <td>{{ r.label }}<div v-if="r.guidance" class="text-caption text-medium-emphasis">{{ r.guidance }}</div></td>
                    <td class="text-right">{{ r.deduction_percent }}%</td>
                    <td>{{ r.requires_manual_review ? 'Yes' : 'No' }}</td>
                    <td>{{ r.is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-right"><v-btn size="small" variant="outlined" color="primary" @click="openRule(r)">Edit</v-btn></td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
          <div class="text-caption text-medium-emphasis">The total deduction is always capped at 70% of the clean value.</div>
        </v-window-item>
        <v-window-item value="history">
          <v-card variant="flat">
            <v-card-text>
              <v-skeleton-loader v-if="loadingHistory" type="table" />
              <div v-else-if="history.length === 0" class="text-medium-emphasis">No price or condition has been changed yet.</div>
              <v-table v-else density="compact">
                <thead><tr><th>When</th><th>Who</th><th>What</th><th>Change</th></tr></thead>
                <tbody>
                  <tr v-for="h in history" :key="h.id">
                    <td>{{ formatDateTime(h.created_at) }}</td>
                    <td>{{ h.admin_name || '-' }}</td>
                    <td>{{ h.summary }}</td>
                    <td>
                      <div v-for="(c, field) in h.changes" :key="field" class="text-caption">
                        {{ field.replace(/_/g, ' ') }}: {{ c.from ?? '-' }} &rarr; {{ c.to ?? '-' }}
                      </div>
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-window-item>
      </v-window>
    </v-container>

    <v-dialog v-model="priceDialog" max-width="480" persistent>
      <v-card>
        <v-card-title>{{ editingPrice ? `${editingPrice.model} ${editingPrice.storage_gb} GB` : 'Add a model and storage' }}</v-card-title>
        <v-card-text>
          <template v-if="!editingPrice">
            <v-text-field v-model="form.model" label="Model (for example iPhone 16 Pro)" variant="outlined" density="comfortable" :error-messages="errors.model" />
            <v-text-field v-model="form.storage_gb" label="Storage (GB)" type="number" variant="outlined" density="comfortable" :error-messages="errors.storage_gb" />
          </template>
          <v-text-field v-model="form.base_value" label="Clean value (NPR)" type="number" min="0" variant="outlined" density="comfortable" :error-messages="errors.base_value" />
          <v-text-field
            v-if="editingPrice"
            v-model="form.admin_override"
            label="Override (NPR, leave empty to use the clean value)"
            type="number"
            min="0"
            variant="outlined"
            density="comfortable"
            :error-messages="errors.admin_override"
          />
          <v-select v-model="form.confidence" :items="confidenceItems" label="Confidence" variant="outlined" density="comfortable" />
          <v-text-field v-model="form.evidence_note" label="Note (where this price comes from)" variant="outlined" density="comfortable" />
          <v-switch v-if="editingPrice" v-model="form.is_active" label="Customers can pick this model and storage" color="primary" hide-details />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" :disabled="saving" @click="priceDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" :loading="saving" @click="savePrice">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="ruleDialog" max-width="480" persistent>
      <v-card>
        <v-card-title>{{ editingRule?.label }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="ruleForm.deduction_percent" label="Deduction (% of clean value)" type="number" min="0" max="100" step="0.5" variant="outlined" density="comfortable" :error-messages="errors.deduction_percent" />
          <v-text-field v-model="ruleForm.guidance" label="Guidance for the team" variant="outlined" density="comfortable" />
          <v-switch v-model="ruleForm.requires_manual_review" label="Always needs manual review" color="primary" hide-details />
          <v-switch v-model="ruleForm.is_active" label="Customers can pick this condition" color="primary" hide-details />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" :disabled="saving" @click="ruleDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" :loading="saving" @click="saveRule">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import {
  createExchangeValuation,
  listExchangeDamageRules,
  listExchangePricingAudit,
  listExchangeValuations,
  updateExchangeDamageRule,
  updateExchangeValuation,
  type ExchangeDamageRule,
  type ExchangePricingAuditEntry,
  type ExchangeValuation,
} from '@/api/exchange.api';
import { formatDateTime, formatNPR } from '@/shared/formatters';
import { useSnackbar } from '@/composables/snackbar';

const { showSuccess, showError } = useSnackbar();

const tab = ref('prices');
const prices = ref<ExchangeValuation[]>([]);
const rules = ref<ExchangeDamageRule[]>([]);
const loadingPrices = ref(true);
const loadingRules = ref(true);
const history = ref<ExchangePricingAuditEntry[]>([]);
const loadingHistory = ref(false);

async function loadHistory() {
  loadingHistory.value = true;
  try {
    history.value = (await listExchangePricingAudit()).data ?? [];
  } finally {
    loadingHistory.value = false;
  }
}
const priceSearch = ref('');
const saving = ref(false);
const errors = ref<Record<string, string[]>>({});

const confidenceItems = [
  { title: 'Verified (from our own purchases)', value: 'verified' },
  { title: 'Estimate (planning figure)', value: 'estimate' },
  { title: 'Low (always manual review)', value: 'low' },
];

const filteredPrices = computed(() => {
  const q = (priceSearch.value ?? '').trim().toLowerCase();
  return q ? prices.value.filter((v) => v.model.toLowerCase().includes(q)) : prices.value;
});

const ruleGroups = computed(() => [
  { key: 'display', title: 'Display', rules: rules.value.filter((r) => r.category === 'display') },
  { key: 'battery', title: 'Battery health', rules: rules.value.filter((r) => r.category === 'battery') },
  { key: 'back_panel', title: 'Back panel', rules: rules.value.filter((r) => r.category === 'back_panel') },
]);

function confidenceColor(c: string) {
  return c === 'verified' ? 'success' : c === 'low' ? 'warning' : 'grey';
}

// ---- prices
const priceDialog = ref(false);
const editingPrice = ref<ExchangeValuation | null>(null);
const form = ref({
  model: '',
  storage_gb: '' as string | number,
  base_value: '' as string | number,
  admin_override: '' as string | number | null,
  confidence: 'verified' as ExchangeValuation['confidence'],
  evidence_note: '' as string | null,
  is_active: true,
});

function openNew() {
  editingPrice.value = null;
  form.value = { model: '', storage_gb: '', base_value: '', admin_override: null, confidence: 'verified', evidence_note: '', is_active: true };
  errors.value = {};
  priceDialog.value = true;
}

function openEdit(v: ExchangeValuation) {
  editingPrice.value = v;
  form.value = {
    model: v.model,
    storage_gb: v.storage_gb,
    base_value: v.base_value,
    admin_override: v.admin_override,
    confidence: v.confidence,
    evidence_note: v.evidence_note ?? '',
    is_active: v.is_active,
  };
  errors.value = {};
  priceDialog.value = true;
}

async function savePrice() {
  saving.value = true;
  errors.value = {};
  try {
    const note = (form.value.evidence_note ?? '').toString().trim() || null;
    if (editingPrice.value) {
      const override = form.value.admin_override;
      await updateExchangeValuation(editingPrice.value.id, {
        base_value: Number(form.value.base_value),
        admin_override: override === '' || override === null ? null : Number(override),
        confidence: form.value.confidence,
        evidence_note: note,
        is_active: form.value.is_active,
      });
    } else {
      await createExchangeValuation({
        model: form.value.model.trim(),
        storage_gb: Number(form.value.storage_gb),
        base_value: Number(form.value.base_value),
        confidence: form.value.confidence,
        evidence_note: note,
      });
    }
    showSuccess('Price saved');
    priceDialog.value = false;
    await loadPrices();
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })?.response?.data;
    errors.value = data?.errors ?? {};
    showError(data?.message ?? 'Could not save the price');
  } finally {
    saving.value = false;
  }
}

async function loadPrices() {
  loadingPrices.value = true;
  try {
    prices.value = (await listExchangeValuations()).data ?? [];
  } finally {
    loadingPrices.value = false;
  }
}

// ---- conditions
const ruleDialog = ref(false);
const editingRule = ref<ExchangeDamageRule | null>(null);
const ruleForm = ref({ deduction_percent: '' as string | number, guidance: '' as string | null, requires_manual_review: false, is_active: true });

function openRule(r: ExchangeDamageRule) {
  editingRule.value = r;
  ruleForm.value = {
    deduction_percent: r.deduction_percent,
    guidance: r.guidance ?? '',
    requires_manual_review: r.requires_manual_review,
    is_active: r.is_active,
  };
  errors.value = {};
  ruleDialog.value = true;
}

async function saveRule() {
  if (!editingRule.value) return;
  saving.value = true;
  errors.value = {};
  try {
    await updateExchangeDamageRule(editingRule.value.id, {
      deduction_percent: Number(ruleForm.value.deduction_percent),
      guidance: (ruleForm.value.guidance ?? '').toString().trim() || null,
      requires_manual_review: ruleForm.value.requires_manual_review,
      is_active: ruleForm.value.is_active,
    });
    showSuccess('Condition saved');
    ruleDialog.value = false;
    await loadRules();
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })?.response?.data;
    errors.value = data?.errors ?? {};
    showError(data?.message ?? 'Could not save the condition');
  } finally {
    saving.value = false;
  }
}

async function loadRules() {
  loadingRules.value = true;
  try {
    rules.value = (await listExchangeDamageRules()).data ?? [];
  } finally {
    loadingRules.value = false;
  }
}

onMounted(() => {
  loadPrices();
  loadRules();
});
</script>
