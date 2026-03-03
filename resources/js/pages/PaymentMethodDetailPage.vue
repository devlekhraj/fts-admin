<template>
  <AppPageHeader title="Payment Method Detail" subtitle="View payment method information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="top-grid">
      <div class="thumb-cell">
        <v-avatar size="112" rounded="lg" color="grey-lighten-3">
          <v-img v-if="paymentMethodDetail?.logo_url" :src="String(paymentMethodDetail.logo_url)" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-credit-card-outline</v-icon>
        </v-avatar>
      </div>
      <div>
        <div class="text-h6">{{ paymentMethodDetail?.name || '-' }}</div>
        <div class="d-flex align-center ga-2 mt-2">
          <span class="text-body-2 text-medium-emphasis">{{ paymentMethodDetail?.slug || '-' }}</span>
        </div>
        <div class="d-flex align-center ga-2 mt-3">
          <v-chip size="small" label variant="tonal" :color="paymentMethodDetail?.status ? 'success' : 'warning'">
            {{ paymentMethodDetail?.status ? 'Active' : 'Inactive' }}
          </v-chip>
          <v-chip size="small" label variant="tonal" :color="paymentMethodDetail?.test_mode ? 'grey' : 'primary'">
            <v-icon
              v-if="!paymentMethodDetail?.test_mode"
              start
              size="10"
              color="success"
              class="live-blink">
              mdi-circle
            </v-icon>
            {{ paymentMethodDetail?.test_mode ? 'Test Mode' : 'Live Mode' }}
          </v-chip>
          <v-chip size="small" label variant="tonal" :color="paymentMethodDetail?.is_international ? 'info' : 'grey'">
            {{ paymentMethodDetail?.is_international ? 'International' : 'Domestic' }}
          </v-chip>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading payment method detail...</div>
  </v-card>

  <v-card class="mt-4">
    <v-tabs v-model="activeTab" color="primary">
      <v-tab v-for="tab in tabItems" :key="tab.value" :value="tab.value">{{ tab.label }}</v-tab>
    </v-tabs>
    <v-divider />
    <v-window v-model="activeTab">
      <v-window-item v-for="tab in tabItems" :key="tab.value" :value="tab.value">
        <component :is="tab.component" :item="paymentMethodDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getPaymentMethodDetail, type PaymentMethodDetailResponse } from '@/api/payment-methods.api';
import PaymentMethodDetailTabConfig from '@/components/payment-method/PaymentMethodDetailTabConfig.vue';
import PaymentMethodDetailTabImages from '@/components/payment-method/PaymentMethodDetailTabImages.vue';
import PaymentMethodDetailTabOverview from '@/components/payment-method/PaymentMethodDetailTabOverview.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
  { value: 'overview', label: 'Overview', component: PaymentMethodDetailTabOverview },
  { value: 'images', label: 'Images', component: PaymentMethodDetailTabImages },
  { value: 'config', label: 'Config', component: PaymentMethodDetailTabConfig },
];

const paymentMethodId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const paymentMethodDetail = ref<PaymentMethodDetailResponse | null>(null);

async function fetchPaymentMethodDetail() {
  if (!paymentMethodId.value) return;
  loading.value = true;
  try {
    paymentMethodDetail.value = await getPaymentMethodDetail(paymentMethodId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.paymentMethods.list' });
}

onMounted(fetchPaymentMethodDetail);
</script>

<style scoped>
.top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

.thumb-cell {
  display: flex;
  justify-content: center;
}

.live-blink {
  animation: live-blink 1.1s infinite ease-in-out;
}

@keyframes live-blink {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.25;
  }
}

@media (min-width: 960px) {
  .top-grid {
    grid-template-columns: 128px minmax(0, 1fr);
    gap: 20px;
  }

  .thumb-cell {
    justify-content: flex-start;
  }
}
</style>
