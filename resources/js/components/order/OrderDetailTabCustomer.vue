<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="text-h6 mb-1">Customer</div>
        <div class="text-body-2 text-medium-emphasis mb-4">Customer details for this order.</div>

        <div class="pa-1">
          <div class="d-flex flex-column ga-4">
            <div>
              <v-avatar size="80" color="grey-lighten-3 text-uppercase">
                <v-img v-if="avatarUrl" :src="avatarUrl" cover />
                <v-icon v-else size="40" color="grey-darken-1">mdi-account-circle</v-icon>
              </v-avatar>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Name</div>
              <div class="text-body-2">{{ item?.customer?.name ?? '-' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Email</div>
              <div class="text-body-2">{{ item?.customer?.email ?? '-' }}</div>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Mobile</div>
              <div class="text-body-2">{{ formatPhoneNumber(item?.customer?.mobile) }}</div>
            </div>
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { OrderDetailResponse } from '@/api/orders.api';
import { formatPhoneNumber } from '@/shared/utils';

const props = defineProps<{
  item: OrderDetailResponse | null;
}>();

const avatarUrl = computed(() => {
  const customer = props.item?.customer as Record<string, unknown> | undefined;
  return String(customer?.avatar_url ?? customer?.avatar ?? '').trim();
});
</script>
