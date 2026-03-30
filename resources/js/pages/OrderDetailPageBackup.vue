<template>
    <AppPageHeader title="Order Detail" subtitle="View order information">
        <template #actions>
            <v-btn variant="tonal" color="primary" @click="goBack">
                <v-icon start>mdi-arrow-left</v-icon>
                Back
            </v-btn>
        </template>
    </AppPageHeader>

    <v-card class="pa-6 pb-0">
        <div class="top-grid">
            <div class="thumb-cell">
                <v-avatar size="112" rounded="lg" color="grey-lighten-3">
                    <v-img v-if="customerAvatar" :src="customerAvatar" cover />
                    <v-img v-else src="https://placehold.co/112x112?text=User" cover />
                </v-avatar>
            </div>
            <div>
                <h3>{{ orderDetail?.customer?.name || '-' }}</h3>
                <div class="text-body-2 text-medium-emphasis mt-2 text-primary">{{
                    formatPhoneNumber(orderDetail?.customer?.mobile) || '-' }}</div>
                <!-- <div class="text-body-2 text-medium-emphasis mt-2">{{ orderDetail?.order_number || '-' }}</div> -->
                <!-- <div class="text-body-2 text-medium-emphasis mt-1"><span class="text-primary">{{ Number(orderDetail?.items_count ?? 0) }} items</span></div> -->
                <div>
                    <h4 class="text-primary">{{ orderDetail?.total !== null && orderDetail?.total !== undefined ?
                        formatNPR(orderDetail.total) : '-' }}</h4>
                </div>
                <div class="text-body-2 text-medium-emphasis mt-1"><v-chip color="primary" label size="small">{{
                        orderDetail?.status || '-' }}</v-chip></div>
            </div>
            <div class="shipping-panel">
                <v-row class="ma-0" align="center">
                    <v-col cols="12" md="6" class="pa-0 pe-md-3">
                        <div class="d-flex ga-2 text-subtitle-2 font-weight-medium">
                            <v-icon size="28" color="primary">mdi-map-marker-outline</v-icon>
                            <div>
                                <h3>Shipping Address</h3>
                                <div class="address-text mt-2">{{ shippingRecipient }}</div>
                                <div class="address-text text-medium-emphasis mt-1">{{ shippingAddressLine1 }}</div>
                                <div class="address-text text-medium-emphasis">{{ shippingAddressLine2 }}</div>
                                <div class="address-text text-medium-emphasis">{{ shippingAddressLine3 }}</div>
                            </div>
                        </div>
                    </v-col>
                    <v-col cols="12" md="6" class="pa-0 pt-3 pt-md-0">
                        <iframe class="shipping-map" :src="shippingMapUrl" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" title="Shipping Address Map" />
                    </v-col>
                </v-row>
            </div>
        </div>
    </v-card>

    <v-card>
        <v-tabs v-model="activeTab" color="primary">
            <v-tab v-for="tab in tabItems" :key="tab.value" :value="tab.value">
                <v-icon start size="16">{{ tab.icon }}</v-icon>
                {{ tab.label }}
            </v-tab>
        </v-tabs>
        <v-divider />
        <v-window v-model="activeTab">
            <v-window-item v-for="tab in tabItems" :key="tab.value" :value="tab.value">
                <component :is="tab.component" :item="orderDetail" />
            </v-window-item>
        </v-window>
    </v-card>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import type { OrderDetailResponse } from '@/api/orders.api';
import { formatNPR } from '@/shared/formatters';
import OrderDetailTabOverview from '@/components/order/OrderDetailTabOverview.vue';
import OrderDetailTabCustomer from '@/components/order/OrderDetailTabCustomer.vue';
import OrderDetailTabIinvoice from '@/components/order/OrderDetailTabIinvoice.vue';
import { formatPhoneNumber } from '@/shared/utils';

const router = useRouter();
const activeTab = ref('overview');
const tabItems = [
    { value: 'overview', label: 'Overview', icon: 'mdi-view-dashboard-outline', component: OrderDetailTabOverview },
    { value: 'customer', label: 'Customer', icon: 'mdi-account-outline', component: OrderDetailTabCustomer },
    { value: 'payment', label: 'Invoice', icon: 'mdi-credit-card-outline', component: OrderDetailTabIinvoice },
];

const orderDetail = ref<OrderDetailResponse | null>({
    id: 5012,
    order_number: 'ORD-5012',
    status: 'Confirmed',
    total: 73500,
    items_count: 3,
    currency: 'NPR',
    created_at: '2026-03-03T10:30:00.000000Z',
    customer: {
        id: 88,
        name: 'Riya Shrestha',
        email: 'riya@example.com',
        mobile: '9861234567',
        avatar_url: 'https://placehold.co/112x112?text=R',
    },
    payment_method: {
        id: 2,
        name: 'eSewa',
        slug: 'esewa',
    },
    subtotal: 70000,
    discount_total: 1500,
    tax_total: 2000,
    shipping_total: 3000,
    notes: 'Deliver in afternoon.',
    shipping_address: {
        first_name: 'Riya',
        last_name: 'Shrestha',
        city: 'Kathmandu',
        district: 'Kathmandu',
        province: 'Bagmati',
        country: 'Nepal',
        landmark: 'Near New Road Gate',
    },
});

const customerAvatar = computed(() => {
    const customer = orderDetail.value?.customer as Record<string, unknown> | undefined;
    return String(customer?.avatar_url ?? customer?.avatar ?? '').trim();
});

const shippingAddress = computed(() => {
    const raw = orderDetail.value?.shipping_address;
    return (raw && typeof raw === 'object') ? (raw as Record<string, unknown>) : null;
});

const shippingRecipient = computed(() => {
    const firstName = String(shippingAddress.value?.first_name ?? '').trim();
    const lastName = String(shippingAddress.value?.last_name ?? '').trim();
    const fullName = [firstName, lastName].filter(Boolean).join(' ');
    return fullName || orderDetail.value?.customer?.name || '-';
});

const shippingAddressLine1 = computed(() => {
    const landmark = String(shippingAddress.value?.landmark ?? '').trim();
    return landmark || '-';
});

const shippingAddressLine2 = computed(() => {
    const city = String(shippingAddress.value?.city ?? '').trim();
    const district = String(shippingAddress.value?.district ?? '').trim();
    return [city, district].filter(Boolean).join(', ') || '-';
});

const shippingAddressLine3 = computed(() => {
    const province = String(shippingAddress.value?.province ?? '').trim();
    const country = String(shippingAddress.value?.country ?? '').trim();
    return [province, country].filter(Boolean).join(', ') || '-';
});

const shippingMapUrl = computed(() => {
    const query = [
        shippingAddressLine1.value,
        shippingAddressLine2.value,
        shippingAddressLine3.value,
    ]
        .filter((part) => part && part !== '-')
        .join(', ');

    const q = encodeURIComponent(query || 'Nepal');
    return `https://maps.google.com/maps?q=${q}&z=15&output=embed`;
});

function goBack() {
    router.push({ name: 'admin.orders.list' });
}
</script>

<style scoped>
.top-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    align-items: start;
}

.thumb-cell {
    display: flex;
    justify-content: center;
}

.shipping-panel {
    border: 1px solid rgb(var(--v-theme-outline-variant));
    border-radius: 10px;
    padding: 12px;
}

.shipping-map {
    width: 100%;
    min-height: 170px;
    border: 0;
    border-radius: 6px;
    display: block;
}

@media (min-width: 960px) {
    .top-grid {
        grid-template-columns: 128px minmax(0, 1fr) minmax(280px, 500px);
        gap: 20px;
    }

    .thumb-cell {
        justify-content: flex-start;
    }
}

.address-text {
    font-size: 0.8rem;
}
</style>
