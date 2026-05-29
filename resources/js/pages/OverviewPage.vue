<template>
  <!-- Key Metrics Cards -->
  <v-container fluid>
    <v-row>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-6">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption mb-1">Total Orders</div>
              <div class="text-h4 font-weight-bold">{{ formatNumber(metrics.totalOrders) }}</div>
            
            </div>
            <v-avatar size="48" color="primary" variant="tonal">
              <v-icon size="24">mdi-shopping-outline</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-6">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption mb-1">Total EMI Requests</div>
              <div class="text-h4 font-weight-bold">{{ formatNumber(metrics.totalEmiRequests) }}</div>
            
            </div>
            <v-avatar size="48" color="success" variant="tonal">
              <v-icon size="24">mdi-cash-multiple</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-6">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption mb-1">Total Products</div>
              <div class="text-h4 font-weight-bold">{{ formatNumber(metrics.totalProducts) }}</div>
           
            </div>
            <v-avatar size="48" color="warning" variant="tonal">
              <v-icon size="24">mdi-package-variant-closed</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-6">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption mb-1">Total Customers</div>
              <div class="text-h4 font-weight-bold">{{ formatNumber(metrics.totalCustomers) }}</div>
            
            </div>
            <v-avatar size="48" color="info" variant="tonal">
              <v-icon size="24">mdi-account-group</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>


    <!-- Recent Orders & EMI Requests Section -->
    <v-row>
      <v-col cols="12" md="7">
        <div class="mb-6">
          <v-card>
            <v-card-title class="d-flex align-center justify-space-between">
              <div class="text-h6">Recent Orders</div>
              <v-btn variant="text" size="small" :to="{ name: 'admin.orders.list' }">
                View All
                <v-icon end>mdi-chevron-right</v-icon>
              </v-btn>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-4">
              <template v-if="latestLoading">
                <v-skeleton-loader type="table" />
              </template>
              <v-alert v-else-if="latestError" type="error" variant="tonal" density="comfortable">
                {{ latestError }}
              </v-alert>
              <v-alert v-else-if="recentOrders.length === 0" type="info" variant="tonal" density="comfortable">
                No recent orders.
              </v-alert>
              <v-table v-else density="comfortable">
                <thead>
                  <tr>
                    <th class="text-left">Order</th>
                    <th class="text-left">Status</th>
                    <th class="">Time</th>
                    <th class="text-right">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in recentOrders" :key="order.id">
                    <td class="text-left">
                      <div class="d-flex align-center ga-2">
                        <v-avatar size="28" color="grey-lighten-3">
                          <v-img v-if="order.customer?.avatar" :src="String(order.customer.avatar)" cover />
                          <v-icon v-else size="16" color="grey-darken-1">mdi-account</v-icon>
                        </v-avatar>
                        <div class="min-w-0">
                          <div class="text-caption text-primary text-truncate">
                            {{ order.order_number ?? '-' }}
                          </div>
                          <div class="text-medium-emphasis text-truncate">
                            {{ order.customer?.name ?? '-' }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="">
                      <v-chip size="small" label :color="getOrderStatusColor(order.status)" variant="tonal">
                        {{ order.status ?? '-' }}
                      </v-chip>
                    </td>
                    <td class="text-caption text-medium-emphasis">
                      {{ order.created_at ? timeAgo(order.created_at) : '-' }}
                    </td>
                    <td class="text-right">
                      <v-btn
                        variant="outlined"
                        size="small"
                        color="primary"
                        :to="{ name: 'admin.orders.detail', params: { id: order.id } }"
                      >
                        Details 
                      </v-btn>  
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </div>
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-h6">Recent EMI Requests</div>
            <v-btn variant="text" size="small" :to="{ name: 'admin.emi.requests' }">
              View All
              <v-icon end>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <template v-if="latestLoading">
              <v-skeleton-loader type="table" />
            </template>
            <v-alert v-else-if="latestError" type="error" variant="tonal" density="comfortable">
              {{ latestError }}
            </v-alert>
            <v-alert v-else-if="recentEmiRequests.length === 0" type="info" variant="tonal" density="comfortable">
              No recent EMI requests.
            </v-alert>
            <v-table v-else density="compact">
              <thead>
                <tr>
                  <th class="text-left">Product</th>
                  <th class="text-left">Status</th>
                  <th class="">Time</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="emi in recentEmiRequests" :key="emi.id">
                  <td class="text-left">
                    <div class="d-flex align-center ga-2">
                      <v-avatar size="28" color="grey-lighten-3">
                        <v-img v-if="emi.customer_avatar" :src="String(emi.customer_avatar)" cover />
                        <v-icon v-else size="16" color="grey-darken-1">mdi-account</v-icon>
                      </v-avatar>
                      <div class="min-w-0">
                        <div class="text-caption text-truncate text-primary">
                          {{ emi.product_name ?? '-' }}
                        </div>
                        <div class="text-caption text-medium-emphasis text-truncate">
                          {{ emi.customer_name ?? '-' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="text-left">
                    <v-chip size="small" label :color="getStatusColor(emi.status)" variant="tonal">
                      {{ emi.status ?? '-' }}
                    </v-chip>
                  </td>
                  <td class="text-caption text-medium-emphasis">
                    {{ emi.created_at ? timeAgo(emi.created_at) : '-' }}
                  </td>
                  <td class="text-right">
                    <v-btn
                      variant="outlined"
                      size="small"
                      color="primary"
                      :to="{ name: 'admin.emi.requests.detail', params: { id: emi.id } }"
                    >
                      Details
                    </v-btn>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="5">
        <RecentActivityCard :items="recentActivity" />
      </v-col>
    </v-row>

 
  </v-container>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth.store';
import { formatAmount, getStatusColor, timeAgo } from '@/shared/utils';
import { getDashboardLatest, getDashboardMetrics, type DashboardLatestActivityLog, type DashboardLatestEmiRequest, type DashboardLatestOrder } from '@/api/dashboard.api';
import RecentActivityCard, { type RecentActivityItem } from '@/components/dashboard/RecentActivityCard.vue';

const authStore = useAuthStore();

const chartPeriod = ref('month');

const recentOrders = ref<DashboardLatestOrder[]>([]);
const recentEmiRequests = ref<DashboardLatestEmiRequest[]>([]);
const latestLoading = ref(false);
const latestError = ref<string | null>(null);

const topProducts = ref([
  { id: 1, name: 'MacBook Pro 16"', status: true, emi_enabled: true, variants_count: 3, thumb: null },
  { id: 2, name: 'iPhone 15 Pro', status: true, emi_enabled: true, variants_count: 4, thumb: null },
  { id: 3, name: 'AirPods Pro', status: true, emi_enabled: false, variants_count: 2, thumb: null },
  { id: 4, name: 'iPad Air', status: true, emi_enabled: true, variants_count: 3, thumb: null },
  { id: 5, name: 'Apple Watch Ultra', status: false, emi_enabled: true, variants_count: 2, thumb: null }
]);

const recentActivity = ref<RecentActivityItem[]>([]);

const metrics = ref({
  totalOrders: 0,
  totalEmiRequests: 0,
  totalProducts: 0,
  totalCustomers: 0,
});

const metricsLoading = ref(false);
const metricsError = ref<string | null>(null);

function formatNumber(num: number): string {
  return new Intl.NumberFormat('en-IN').format(num);
}

function getOrderStatusColor(status?: string | null): string {
  const statusColors: Record<string, string> = {
    draft: 'grey',
    placed: 'warning',
    confirmed: 'info',
    dispatched: 'primary',
    delivered: 'primary',
    completed: 'success',
    canceled: 'error',
    cancelled: 'error',
  };
  return statusColors[status?.toLowerCase() || ''] || 'grey';
}

async function fetchMetrics() {
  metricsLoading.value = true;
  metricsError.value = null;

  try {
    const data = await getDashboardMetrics();
    metrics.value = {
      totalOrders: Number(data.totalOrders ?? 0),
      totalEmiRequests: Number(data.totalEmiRequests ?? 0),
      totalProducts: Number(data.totalProducts ?? 0),
      totalCustomers: Number(data.totalCustomers ?? 0),
    };
  } catch (error) {
    console.error('Failed to load dashboard metrics', error);
    metricsError.value = 'Failed to load metrics';
  } finally {
    metricsLoading.value = false;
  }
}

async function fetchLatest() {
  latestLoading.value = true;
  latestError.value = null;
  try {
    const data = await getDashboardLatest();
    recentOrders.value = Array.isArray(data?.orders) ? data.orders : [];
    recentEmiRequests.value = Array.isArray(data?.emi_requests) ? data.emi_requests : [];
    const logs = Array.isArray(data?.activity_logs) ? data.activity_logs : [];
    recentActivity.value = logs.map(mapActivityLogToItem);
  } catch (error) {
    console.error('Failed to load dashboard latest', error);
    latestError.value = 'Failed to load latest data';
  } finally {
    latestLoading.value = false;
  }
}

function mapActivityLogToItem(log: DashboardLatestActivityLog): RecentActivityItem {
  const createdAt = log?.created_at ?? null;
  const entityType = String(log?.entity_type ?? '').trim();
  const normalizedEntityType = entityType.toLowerCase();

  let icon: string | undefined = undefined;
  if (normalizedEntityType === 'emi_requests' || normalizedEntityType.includes('emirequest')) {
    icon = 'mdi-bell-check-outline';
  } else if (normalizedEntityType === 'orders' || normalizedEntityType.includes('order')) {
    icon = 'mdi-shopping-outline';
  }else{
    icon = 'mdi-bell-check-outline';
  }

  return {
    id: log.id,
    title: String(log.label ?? ''),
    description: String(log.description ?? ''),
    time: createdAt ? timeAgo(createdAt) : '',
    actor_name: log.actor_name ?? null,
    entity_type: log.entity_type ?? null,
    entity_id: log.entity_id ?? null,
    icon,
  };
}

function refreshData() {
  void fetchMetrics();
  void fetchLatest();
}

onMounted(() => {
  void fetchMetrics();
  void fetchLatest();
});
</script>

<style scoped>
.chart-placeholder {
  background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
  border-radius: 8px;
}

.border-b {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.border-b:last-child {
  border-bottom: none;
}
</style>
