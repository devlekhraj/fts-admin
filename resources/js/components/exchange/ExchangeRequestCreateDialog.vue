<template>
  <v-dialog :model-value="modelValue" max-width="720" scrollable persistent @update:model-value="onToggle">
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <span>Add Exchange Request</span>
        <v-btn icon="mdi-close" variant="text" density="comfortable" aria-label="Close" :disabled="saving" @click="close" />
      </v-card-title>
      <v-card-subtitle class="text-wrap pb-2">
        For a customer at the counter or on the phone. It is saved as pending and marked for an in-person check.
      </v-card-subtitle>
      <v-divider />

      <v-card-text>
        <v-form ref="formRef" @submit.prevent="submit">
          <div class="text-subtitle-2 mb-2">Customer</div>
          <v-autocomplete
            v-model="selectedCustomer"
            v-model:search="customerSearch"
            :items="customerItems"
            :loading="customerLoading"
            item-title="label"
            item-value="id"
            return-object
            label="Link to an existing customer (optional)"
            hint="Pick an account so the customer also gets the decision by email. Leave empty for a walk-in."
            persistent-hint
            clearable
            hide-no-data
            no-filter
            density="comfortable"
            variant="outlined"
            class="mb-4"
            @update:model-value="onCustomerPicked"
          />
          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.customer_name"
                label="Customer name *"
                :rules="[required('Enter the customer name')]"
                :error-messages="errors.customer_name"
                maxlength="120"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.customer_phone"
                label="Phone number *"
                inputmode="tel"
                :rules="[required('Enter the phone number'), phoneRule]"
                :error-messages="errors.customer_phone"
                maxlength="30"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
          </v-row>

          <div class="text-subtitle-2 mt-2 mb-2">Old device</div>
          <v-btn-toggle v-model="form.device_type" mandatory divided color="primary" density="comfortable" class="mb-4">
            <v-btn value="phone" prepend-icon="mdi-cellphone">Mobile</v-btn>
            <v-btn value="laptop" prepend-icon="mdi-laptop">Laptop</v-btn>
          </v-btn-toggle>
          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.device_name"
                label="Model *"
                placeholder="e.g. iPhone 13 (128GB)"
                :rules="[required('Enter the device model')]"
                :error-messages="errors.device_name"
                maxlength="255"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.serial_number"
                :label="isLaptop ? 'Serial number *' : 'IMEI *'"
                :hint="isLaptop ? '5 to 40 letters and numbers' : 'Dial *#06# on the phone. 15 digits.'"
                persistent-hint
                :inputmode="isLaptop ? 'text' : 'numeric'"
                :maxlength="isLaptop ? 40 : 24"
                :rules="[deviceIdRule]"
                :error-messages="errors.serial_number"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.estimated_value"
                label="Estimated value (NPR) *"
                type="number"
                min="0"
                :rules="[amountRule]"
                :error-messages="errors.estimated_value"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
          </v-row>

          <div class="text-subtitle-2 mt-2 mb-2">New product (optional)</div>
          <v-row dense>
            <v-col cols="12" sm="8">
              <v-text-field
                v-model="form.target_name"
                label="Product the customer wants"
                maxlength="255"
                :error-messages="errors.target_name"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field
                v-model="form.target_price"
                label="Price (NPR)"
                type="number"
                min="0"
                :error-messages="errors.target_price"
                density="comfortable"
                variant="outlined"
              />
            </v-col>
          </v-row>

          <v-textarea
            v-model="form.admin_note"
            label="Note (optional)"
            rows="2"
            auto-grow
            maxlength="2000"
            :error-messages="errors.admin_note"
            density="comfortable"
            variant="outlined"
          />
        </v-form>
      </v-card-text>

      <v-divider />
      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn variant="text" :disabled="saving" @click="close">Cancel</v-btn>
        <v-btn color="primary" variant="flat" :loading="saving" @click="submit">Create request</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { createExchangeRequest } from '@/api/exchange.api';
import { list as listCustomers } from '@/api/customers.api';
import { checkDeviceId } from '@/shared/exchange';
import { useSnackbar } from '@/composables/snackbar';

const props = defineProps<{ modelValue: boolean }>();
const emit = defineEmits<{
  (e: 'update:modelValue', open: boolean): void;
  (e: 'created', payload: { id: number; reference: string }): void;
}>();

const { showSuccess, showError } = useSnackbar();

const blank = () => ({
  customer_name: '',
  customer_phone: '',
  user_id: null as number | null,
  device_type: 'phone' as 'phone' | 'laptop',
  device_name: '',
  serial_number: '',
  estimated_value: '' as string | number,
  target_name: '',
  target_price: '' as string | number,
  admin_note: '',
});

const form = reactive(blank());
const errors = ref<Record<string, string[]>>({});
const saving = ref(false);
const formRef = ref<{ validate: () => Promise<{ valid: boolean }>; resetValidation: () => void } | null>(null);
const isLaptop = computed(() => form.device_type === 'laptop');

// ---- existing customer lookup
type CustomerChoice = { id: number; label: string; name: string; phone: string };
const selectedCustomer = ref<CustomerChoice | null>(null);
const customerSearch = ref('');
const customerItems = ref<CustomerChoice[]>([]);
const customerLoading = ref(false);
let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(customerSearch, (text) => {
  clearTimeout(searchTimer);
  const query = (text ?? '').trim();
  // Picking a customer sets the text to the chosen label: do not search again for that.
  if (query.length < 2 || query === selectedCustomer.value?.label) return;
  searchTimer = setTimeout(async () => {
    customerLoading.value = true;
    try {
      const response = await listCustomers({ search: query, per_page: 8 });
      customerItems.value = (response.data ?? []).map((c) => {
        const phone = String(c.mobile ?? c.contact_number ?? '').replace(/^n\/a$|^-$/i, '');
        const name = String(c.name ?? '');
        return { id: Number(c.id), name, phone, label: [name, c.email, phone].filter(Boolean).join(' · ') };
      });
    } catch {
      customerItems.value = [];
    } finally {
      customerLoading.value = false;
    }
  }, 300);
});

function onCustomerPicked(choice: CustomerChoice | null) {
  form.user_id = choice ? choice.id : null;
  if (choice) {
    if (choice.name) form.customer_name = choice.name;
    if (choice.phone) form.customer_phone = choice.phone;
  }
}

// ---- rules (the server checks all of this again)
const required = (message: string) => (v: unknown) => (v !== null && v !== undefined && String(v).trim() !== '') || message;
const phoneRule = (v: string) => /^\+?[0-9][0-9\s-]{5,28}$/.test((v ?? '').trim()) || 'Enter a valid phone number';
const deviceIdRule = (v: string) => checkDeviceId(v, isLaptop.value) ?? true;
const amountRule = (v: unknown) => (v !== '' && v !== null && Number(v) >= 0 && Number.isFinite(Number(v))) || 'Enter the estimated value';

watch(() => form.device_type, () => {
  form.serial_number = '';
  delete errors.value.serial_number;
});
watch(() => form.serial_number, () => delete errors.value.serial_number);

function reset() {
  Object.assign(form, blank());
  errors.value = {};
  selectedCustomer.value = null;
  customerSearch.value = '';
  customerItems.value = [];
  formRef.value?.resetValidation();
}

watch(() => props.modelValue, (open) => {
  if (open) reset();
});

function close() {
  if (saving.value) return;
  emit('update:modelValue', false);
}

function onToggle(open: boolean) {
  if (!open) close();
}

async function submit() {
  if (saving.value) return;
  errors.value = {};
  const check = await formRef.value?.validate();
  if (check && !check.valid) return;

  saving.value = true;
  try {
    const response = await createExchangeRequest({
      customer_name: form.customer_name.trim(),
      customer_phone: form.customer_phone.trim(),
      user_id: form.user_id,
      device_type: form.device_type,
      device_name: form.device_name.trim(),
      serial_number: form.serial_number.trim(),
      estimated_value: Number(form.estimated_value),
      target_name: form.target_name.trim() || null,
      target_price: form.target_price === '' ? null : Number(form.target_price),
      admin_note: form.admin_note.trim() || null,
    });
    showSuccess(response.message || 'Exchange request created');
    emit('created', response.data);
    emit('update:modelValue', false);
  } catch (e) {
    const data = (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })?.response?.data;
    errors.value = data?.errors ?? {};
    showError(data?.message ?? 'Could not create the request');
  } finally {
    saving.value = false;
  }
}
</script>
