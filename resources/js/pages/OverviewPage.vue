<template>
  <!-- Key Metrics Cards -->
  <v-container fluid>
    <v-row>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-6">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-caption mb-1">Total Orders</div>
              <div class="text-h4 font-weight-bold text-primary">{{ formatNumber(metrics.totalOrders) }}</div>
              <div class="text-caption mt-1">
                <v-icon size="small" color="success">mdi-trending-up</v-icon>
                <span class="text-success">+8.2%</span> from last month
              </div>
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
              <div class="text-caption mt-1">
                <v-icon size="small" color="success">mdi-trending-up</v-icon>
                <span class="text-success">+12.5%</span> from last month
              </div>
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
              <div class="text-caption mt-1">
                <v-icon size="small" color="warning">mdi-trending-neutral</v-icon>
                <span class="text-warning">+2.1%</span> from last month
              </div>
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
              <div class="text-caption mt-1">
                <v-icon size="small" color="success">mdi-trending-up</v-icon>
                <span class="text-success">+15.3%</span> from last month
              </div>
            </div>
            <v-avatar size="48" color="info" variant="tonal">
              <v-icon size="24">mdi-account-group</v-icon>
            </v-avatar>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts Section -->
    <v-row>
      <v-col cols="12" lg="8">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-h6 font-weight-bold">Sales Overview</div>
            <v-btn-toggle v-model="chartPeriod" variant="outlined" density="compact">
              <v-btn value="week" size="small">Week</v-btn>
              <v-btn value="month" size="small">Month</v-btn>
              <v-btn value="year" size="small">Year</v-btn>
            </v-btn-toggle>
          </v-card-title>
          <v-card-text class="pa-4">
            <div class="chart-placeholder">
              <v-sheet height="300" class="d-flex align-center justify-center">
                <div class="text-center">
                  <v-icon size="48" color="grey-lighten-2">mdi-chart-line</v-icon>
                  <div class="text-medium-emphasis mt-2">Sales Chart</div>
                  <div class="text-caption text-medium-emphasis">Revenue trends over time</div>
                </div>
              </v-sheet>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" lg="4">
        <v-card>
          <v-card-title class="text-h5 font-weight-bold">Order Status</v-card-title>
          <v-card-text class="pa-4">
            <div class="chart-placeholder">
              <v-sheet height="300" class="d-flex align-center justify-center">
                <div class="text-center">
                  <v-icon size="48" color="grey-lighten-2">mdi-chart-pie</v-icon>
                  <div class="text-medium-emphasis mt-2">Order Distribution</div>
                  <div class="text-caption text-medium-emphasis">Orders by status</div>
                </div>
              </v-sheet>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Orders & EMI Requests Section -->
    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-h6 font-weight-bold">Recent Orders</div>
            <v-btn variant="text" size="small" :to="{ name: 'admin.orders.list' }">
              View All
              <v-icon end>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <v-list density="compact">
              <v-list-item v-for="(order, index) in recentOrders" :key="order.id"
                :class="{ 'border-b': index < recentOrders.length - 1 }">
                <template #prepend>
                  <v-avatar size="32" color="primary" variant="tonal">
                    <v-icon size="16">mdi-shopping-outline</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="d-flex align-center justify-space-between">
                  <span>{{ order.order_number }}</span>
                  <v-chip size="x-small" :color="getOrderStatusColor(order.status)" variant="tonal">
                    {{ order.status }}
                  </v-chip>
                </v-list-item-title>
                <v-list-item-subtitle class="d-flex align-center justify-space-between mt-1">
                  <span>{{ order.customer?.name }}</span>
                  <span class="text-primary font-weight-medium">{{ formatAmount(order.total) }}</span>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-h6 font-weight-bold">Recent EMI Requests</div>
            <v-btn variant="text" size="small" :to="{ name: 'admin.emi.requests' }">
              View All
              <v-icon end>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <v-list density="compact">
              <v-list-item v-for="(emi, index) in recentEmiRequests" :key="emi.id"
                :class="{ 'border-b': index < recentEmiRequests.length - 1 }">
                <template #prepend>
                  <v-avatar size="32" color="success" variant="tonal">
                    <v-icon size="16">mdi-cash-multiple</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="d-flex align-center justify-space-between">
                  <span>{{ emi.product_name }}</span>
                  <v-chip size="x-small" :color="getEmiStatusColor(emi.status)" variant="tonal">
                    {{ emi.status }}
                  </v-chip>
                </v-list-item-title>
                <v-list-item-subtitle class="d-flex align-center justify-space-between mt-1">
                  <span>{{ emi.customer_name }}</span>
                  <span class="text-primary font-weight-medium">{{ formatAmount(emi.amount) }}/month</span>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Activity & Top Products Section -->
    <v-row>
      <v-col cols="12" lg="6">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-h6 font-weight-bold">Recent Activity</div>
            <v-btn variant="text" size="small">
              View All
              <v-icon end>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <v-list density="compact">
              <v-list-item v-for="(activity, index) in recentActivity" :key="activity.id"
                :class="{ 'border-b': index < recentActivity.length - 1 }">
                <template #prepend>
                  <v-avatar size="32" :color="activity.color" variant="tonal">
                    <v-icon size="16">{{ activity.icon }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ activity.title }}</v-list-item-title>
                <v-list-item-subtitle class="d-flex align-center justify-space-between mt-1">
                  <span>{{ activity.description }}</span>
                  <span class="text-caption text-medium-emphasis">{{ activity.time }}</span>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" lg="6">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="text-h6 font-weight-bold">Top Products</div>
            <v-btn variant="text" size="small" :to="{ name: 'admin.product.list' }">
              View All
              <v-icon end>mdi-chevron-right</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <v-list density="compact">
              <v-list-item v-for="(product, index) in topProducts" :key="product.id"
                :class="{ 'border-b': index < topProducts.length - 1 }">
                <template #prepend>
                  <v-avatar size="32" rounded>
                    <v-img v-if="product.thumb" :src="product.thumb" alt="Product" />
                    <v-icon v-else size="16">mdi-package-variant-closed</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ product.name }}</v-list-item-title>
                <v-list-item-subtitle class="d-flex align-center justify-space-between mt-1">
                  <span class="text-caption">{{ product.variants_count }} variants</span>
                  <v-chip size="x-small" :color="product.status ? 'success' : 'error'" variant="tonal">
                    {{ product.status ? 'Active' : 'Inactive' }}
                  </v-chip>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useAuthStore } from '../stores/auth.store';
import { formatAmount } from '@/shared/utils';
import AppPageHeader from '@/components/AppPageHeader.vue';

const authStore = useAuthStore();
const admin = computed(() => authStore.admin);

const chartPeriod = ref('month');

// Static data for dashboard
const recentOrders = ref([
  {
    id: 1,
    order_number: 'ORD-2024-001',
    status: 'completed',
    total: 45999,
    customer: {
      id: 1,
      name: 'Rajesh Kumar',
      email: 'rajesh@email.com',
      avatar: null
    }
  },
  {
    id: 2,
    order_number: 'ORD-2024-002',
    status: 'processing',
    total: 28999,
    customer: {
      id: 2,
      name: 'Priya Sharma',
      email: 'priya@email.com',
      avatar: null
    }
  },
  {
    id: 3,
    order_number: 'ORD-2024-003',
    status: 'pending',
    total: 75999,
    customer: {
      id: 3,
      name: 'Amit Singh',
      email: 'amit@email.com',
      avatar: null
    }
  },
  {
    id: 4,
    order_number: 'ORD-2024-004',
    status: 'shipped',
    total: 12999,
    customer: {
      id: 4,
      name: 'Neha Patel',
      email: 'neha@email.com',
      avatar: null
    }
  },
  {
    id: 5,
    order_number: 'ORD-2024-005',
    status: 'cancelled',
    total: 35999,
    customer: {
      id: 5,
      name: 'Vikram Reddy',
      email: 'vikram@email.com',
      avatar: null
    }
  }
]);

const topProducts = ref([
  { id: 1, name: 'MacBook Pro 16"', status: true, emi_enabled: true, variants_count: 3, thumb: null },
  { id: 2, name: 'iPhone 15 Pro', status: true, emi_enabled: true, variants_count: 4, thumb: null },
  { id: 3, name: 'AirPods Pro', status: true, emi_enabled: false, variants_count: 2, thumb: null },
  { id: 4, name: 'iPad Air', status: true, emi_enabled: true, variants_count: 3, thumb: null },
  { id: 5, name: 'Apple Watch Ultra', status: false, emi_enabled: true, variants_count: 2, thumb: null }
]);

const recentEmiRequests = ref([
  { id: 1, product_name: 'MacBook Pro 16"', customer_name: 'Rajesh Kumar', status: 'pending', amount: 8999 },
  { id: 2, product_name: 'iPhone 15 Pro', customer_name: 'Priya Sharma', status: 'approved', amount: 3499 },
  { id: 3, product_name: 'iPad Air', customer_name: 'Amit Singh', status: 'processing', amount: 2499 },
  { id: 4, product_name: 'AirPods Pro', customer_name: 'Neha Patel', status: 'completed', amount: 899 },
  { id: 5, product_name: 'Apple Watch Ultra', customer_name: 'Vikram Reddy', status: 'pending', amount: 4499 }
]);

const recentActivity = ref([
  { id: 1, title: 'New Order Received', description: 'Order #ORD-2024-006 from Suresh Kumar', time: '2 hours ago', icon: 'mdi-shopping-outline', color: 'primary' },
  { id: 2, title: 'Product Updated', description: 'MacBook Pro 16" price updated', time: '4 hours ago', icon: 'mdi-package-variant-closed', color: 'success' },
  { id: 3, title: 'EMI Request Approved', description: 'iPhone 15 Pro EMI for Priya Sharma', time: '6 hours ago', icon: 'mdi-cash-multiple', color: 'info' },
  { id: 4, title: 'New Customer Registered', description: 'Anita Verma joined the platform', time: '8 hours ago', icon: 'mdi-account-plus', color: 'warning' },
  { id: 5, title: 'Payment Received', description: formatAmount(45999) + ' from Order #ORD-2024-005', time: '12 hours ago', icon: 'mdi-currency-usd', color: 'success' }
]);

const metrics = ref({
  totalOrders: 1847,
  totalEmiRequests: 342,
  totalProducts: 387,
  totalCustomers: 1256
});

function formatNumber(num: number): string {
  return new Intl.NumberFormat('en-IN').format(num);
}

function getOrderStatusColor(status?: string | null): string {
  const statusColors: Record<string, string> = {
    'pending': 'warning',
    'processing': 'info',
    'completed': 'success',
    'cancelled': 'error',
    'shipped': 'primary'
  };
  return statusColors[status?.toLowerCase() || ''] || 'grey';
}

function getEmiStatusColor(status?: string | null): string {
  const statusColors: Record<string, string> = {
    'pending': 'warning',
    'processing': 'info',
    'approved': 'success',
    'rejected': 'error',
    'completed': 'primary'
  };
  return statusColors[status?.toLowerCase() || ''] || 'grey';
}

function refreshData() {
  // Static data - no refresh needed
  console.log('Dashboard uses static data');
}
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
