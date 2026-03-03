<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="text-h6 mb-1">Overview</div>
        <div class="text-body-2 text-medium-emphasis mb-4">Basic customer information.</div>

        <div class="pa-1">
          <div class="d-flex flex-column ga-4">
            <div>
              <div class="text-caption text-medium-emphasis">Name</div>
              <div class="text-body-2">{{ item?.name ?? '-' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Email</div>
              <div class="text-body-2">{{ item?.email ?? '-' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Mobile</div>
              <div class="text-body-2">{{ formatPhoneNumber(item?.mobile) }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Since</div>
              <div class="text-body-2">{{ formatLongDate(item?.created_at) ?? '-' }}</div>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import type { CustomerDetailResponse } from '@/api/customers.api';
import { formatLongDate, formatPhoneNumber } from '@/shared/utils';

defineProps<{
  item: CustomerDetailResponse | null;
}>();
</script>
