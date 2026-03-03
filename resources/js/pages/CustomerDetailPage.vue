<template>
  <AppPageHeader title="Customer Detail" subtitle="View customer information">
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
          <v-img v-if="customerDetail?.avatar_url" :src="String(customerDetail.avatar_url)" cover />
          <v-icon v-else size="32" color="grey-darken-1">mdi-account-circle</v-icon>
        </v-avatar>
      </div>
      <div>
        <div class="text-h6">{{ customerDetail?.name || '-' }}</div>
        <div class="text-body-2 text-medium-emphasis mt-2">{{ customerDetail?.email || '-' }}</div>
        <div class="text-body-2 text-medium-emphasis mt-1" v-if="customerDetail?.mobile">{{ customerDetail?.mobile || customerDetail?.contact_number || '-' }}</div>
        <div class="text-body-2 text-medium-emphasis mt-1">
          Orders: {{ Number(customerDetail?.total_order ?? 0) > 0 ? Number(customerDetail?.total_order ?? 0) : 'n/a' }}
        </div>
        <div class="text-body-2 text-medium-emphasis mt-1">
          EMI: {{ Number(customerDetail?.total_emi ?? 0) > 0 ? Number(customerDetail?.total_emi ?? 0) : 'n/a' }}
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading customer detail...</div>
  </v-card>

  <v-card class="mt-4">
    <v-tabs v-model="activeTab" color="primary">
      <v-tab v-for="tab in tabItems" :key="tab.value" :value="tab.value">
        <v-icon start size="16">{{ tab.icon }}</v-icon>
        {{ tab.label }}
      </v-tab>
    </v-tabs>
    <v-divider />
    <v-window v-model="activeTab">
      <v-window-item v-for="tab in tabItems" :key="tab.value" :value="tab.value">
        <component :is="tab.component" :item="customerDetail" />
      </v-window-item>
    </v-window>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getCustomerDetail, type CustomerDetailResponse } from '@/api/customers.api';
import CustomerDetailTabAddress from '@/components/customer/CustomerDetailTabAddress.vue';
import CustomerDetailTabEmi from '@/components/customer/CustomerDetailTabEmi.vue';
import CustomerDetailTabNotifications from '@/components/customer/CustomerDetailTabNotifications.vue';
import CustomerDetailTabOrders from '@/components/customer/CustomerDetailTabOrders.vue';
import CustomerDetailTabOverview from '@/components/customer/CustomerDetailTabOverview.vue';
import CustomerDetailTabSettings from '@/components/customer/CustomerDetailTabSettings.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
  { value: 'overview', label: 'Overview', icon: 'mdi-view-dashboard-outline', component: CustomerDetailTabOverview },
  { value: 'orders', label: 'Orders', icon: 'mdi-package-variant-closed', component: CustomerDetailTabOrders },
  { value: 'emi', label: 'EMI', icon: 'mdi-cash-multiple', component: CustomerDetailTabEmi },
  { value: 'address', label: 'Address', icon: 'mdi-map-marker-outline', component: CustomerDetailTabAddress },
  { value: 'settings', label: 'Settings', icon: 'mdi-cog-outline', component: CustomerDetailTabSettings },
  { value: 'notifications', label: 'Notifications', icon: 'mdi-bell-outline', component: CustomerDetailTabNotifications },
];

const customerId = computed(() => String(route.params.id ?? ''));
const loading = ref(false);
const customerDetail = ref<CustomerDetailResponse | null>(null);

async function fetchCustomerDetail() {
  if (!customerId.value) return;
  loading.value = true;
  try {
    customerDetail.value = await getCustomerDetail(customerId.value);
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.customers.list' });
}

onMounted(fetchCustomerDetail);
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
