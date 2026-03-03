<template>
  <div class="pa-6">
    <div class="config-wrap">
      <div class="d-flex align-center justify-space-between mb-4">
        <div>
          <div class="text-h6 mb-1">Configuration</div>
          <div class="text-body-2 text-medium-emphasis">
            Provider-specific configuration payload.
          </div>
        </div>
        <v-btn color="primary" variant="flat" @click="onUpdate">
          <v-icon start size="16">mdi-content-save-outline</v-icon>
          Update
        </v-btn>
      </div>

      <v-row v-if="configKeys.length">
        <v-col v-for="key in configKeys" :key="key" cols="12">
          <v-text-field
            v-model="form[key]"
            :label="toLabel(key)"
            variant="outlined"
            density="comfortable" />
        </v-col>
      </v-row>
      <div v-else class="text-body-2 text-medium-emphasis">No config fields found.</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { PaymentMethodDetailResponse } from '@/api/payment-methods.api';

const props = defineProps<{
  item: PaymentMethodDetailResponse | null;
}>();

const form = ref<Record<string, string>>({});
const configKeys = ref<string[]>([]);

watch(
  () => props.item?.config,
  (config) => {
    const source = config && typeof config === 'object' ? (config as Record<string, unknown>) : {};
    const next: Record<string, string> = {};
    Object.entries(source).forEach(([key, value]) => {
      if (value === null || value === undefined) {
        next[key] = '';
        return;
      }
      if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
        next[key] = String(value);
        return;
      }
      next[key] = JSON.stringify(value);
    });
    form.value = next;
    configKeys.value = Object.keys(next);
  },
  { immediate: true },
);

function toLabel(key: string): string {
  return key
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

function onUpdate() {
  // TODO: replace with update API call.
  console.log('Update payment method config:', form.value);
}
</script>

<style scoped>
.config-wrap {
  max-width: 880px;
  margin: 0 auto;
}
</style>
