<template>
  <div class="pa-6 pt-10">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="d-flex align-center justify-space-between mb-4">
          <div>
            <div class="text-h6 mb-1">Payment Method Overview</div>
            <div class="text-body-2 text-medium-emphasis">
              Basic information and status flags.
            </div>
          </div>
          <v-btn color="primary" variant="flat" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-row>
          <v-col cols="12">
            <v-text-field :model-value="item?.name ?? ''" label="Name" variant="outlined" density="comfortable" />
          </v-col>
          <v-col cols="12">
            <v-text-field :model-value="item?.slug ?? ''" label="Slug" variant="outlined" density="comfortable" />
          </v-col>
          <v-col cols="4">
            <v-select
              :model-value="Boolean(item?.status)"
              :items="statusItems"
              item-title="label"
              item-value="value"
              label="Status"
              variant="outlined"
              density="comfortable"
              />
          </v-col>
         <v-col cols="4">
            <v-select
              :model-value="Boolean(item?.test_mode)"
              :items="testModeItems"
              item-title="label"
              item-value="value"
              label="Test Mode"
              variant="outlined"
              density="comfortable"
              />
          </v-col>
          <v-col cols="4">
            <v-select
              :model-value="Boolean(item?.is_international)"
              :items="internationalItems"
              item-title="label"
              item-value="value"
              label="International"
              variant="outlined"
              density="comfortable"
              />
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import type { PaymentMethodDetailResponse } from '@/api/payment-methods.api';

defineProps<{
  item: PaymentMethodDetailResponse | null;
}>();

const statusItems = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false },
];

const testModeItems = [
  { label: 'Enabled', value: true },
  { label: 'Disabled', value: false },
];

const internationalItems = [
  { label: 'Yes', value: true },
  { label: 'No', value: false },
];

function onUpdate() {
  // TODO: replace with update API call.
  console.log('Update payment method overview');
}
</script>
