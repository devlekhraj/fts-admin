<template>
  <div class="pa-6">
    <div class="tab-wrap">
      <div class="text-h6 mb-1">Shipping Address</div>
      <div class="text-body-2 text-medium-emphasis mb-4">Customer shipping addresses.</div>

      <div v-if="addresses.length" class="address-list">
        <div v-for="address in addresses" :key="String(address.id)" class="address-card">
          <div class="contact-row">
            <v-avatar size="34" color="grey-lighten-3">
              <v-icon size="18" color="grey-darken-1">mdi-account-outline</v-icon>
            </v-avatar>
            <div class="contact-main">
              <div class="d-flex align-center justify-space-between ga-3">
                <div class="text-subtitle-2 font-weight-medium">{{ fullName(address) }}</div>
                <v-chip
                  v-if="Number(address.is_default ?? 0) === 1"
                  size="small"
                  color="primary"
                  variant="tonal">
                  Default
                </v-chip>
              </div>
              <div class="text-body-2 text-medium-emphasis mt-1">
                {{ address.contact_number || '-' }}
              </div>
            </div>
          </div>
          <div class="text-body-2 mt-2">{{ address.landmark || '-' }}</div>
          <div class="text-body-2 text-medium-emphasis mt-1">
            {{ compactAddress(address) }}
          </div>
        </div>
      </div>

      <div v-else class="empty-state text-body-2 text-medium-emphasis">
        No shipping address found.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { CustomerDetailResponse } from '@/api/customers.api';

type ShippingAddress = {
  id?: number | string | null;
  first_name?: string | null;
  last_name?: string | null;
  contact_number?: string | null;
  country?: string | null;
  province?: string | null;
  district?: string | null;
  city?: string | null;
  landmark?: string | null;
  is_default?: number | string | null;
};

const props = defineProps<{
  item: CustomerDetailResponse | null;
}>();

const addresses = computed<ShippingAddress[]>(() => {
  const list = props.item?.shipping_address;
  return Array.isArray(list) ? (list as ShippingAddress[]) : [];
});

function fullName(address: ShippingAddress): string {
  const first = String(address.first_name ?? '').trim();
  const last = String(address.last_name ?? '').trim();
  const name = `${first} ${last}`.trim();
  return name || '-';
}

function compactAddress(address: ShippingAddress): string {
  const parts = [address.city, address.district, address.province, address.country]
    .map((value) => String(value ?? '').trim())
    .filter(Boolean);

  return parts.length ? parts.join(', ') : '-';
}
</script>

<style scoped>
.tab-wrap {
  max-width: 880px;
  margin: 0 auto;
}

.address-list {
  display: grid;
  gap: 12px;
}

.address-card {
  border: 1px solid rgb(var(--v-theme-outline-variant));
  border-radius: 12px;
  padding: 14px 16px;
}

.contact-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.contact-main {
  flex: 1;
  min-width: 0;
}

.empty-state {
  border: 1px dashed rgb(var(--v-theme-outline-variant));
  border-radius: 12px;
  padding: 14px 16px;
}
</style>
