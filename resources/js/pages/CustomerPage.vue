<template>
    <AppPageHeader title="Customers" subtitle="Customer list">
        <template #actions>
            <v-menu location="bottom start">
                <template #activator="{ props }">
                    <v-btn v-bind="props" variant="outlined" color="primary" prepend-icon="mdi-download-outline">
                        Export
                    </v-btn>
                </template>
                <v-list class="export-menu-list" density="comfortable" min-width="170">
                    <v-list-item v-for="option in exportOptions" :key="option.type" :title="option.title"
                        :prepend-icon="option.icon" @click="onExport(option.type)" />
                </v-list>
            </v-menu>

            <v-btn color="primary" variant="flat" prepend-icon="mdi-account-plus-outline">
                Add Customer
            </v-btn>
        </template>
    </AppPageHeader>
    <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading" :page="options.page"
        :items-per-page="options.itemsPerPage" @update:options="onOptions">
        <template #actions>
            <v-container fluid class="py-4">
                <v-row align="center">
                    <v-col cols="12" md="6" lg="4">
                        <div class="d-flex align-center ga-3">
                            <AppTextField v-model="search" label="Search" placeholder="Search by name..."
                                prepend-inner-icon="mdi-magnify" hide-details clearable style="min-width: 260px"
                                @click:clear="onClearSearch" />
                            <v-btn color="primary" variant="tonal" height="40">
                                <v-icon start>mdi-magnify</v-icon>
                                Search
                            </v-btn>
                        </div>
                    </v-col>
                    <v-spacer></v-spacer>

                    <v-col cols="12" md="auto" class="text-right">
                        <div class="text-medium-emphasis">
                            <span class="text-primary" style="font-size: smaller;">Total: {{ total }} Items
                                found.</span>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </template>

        <template #item.name="{ item }">
            <div class="d-flex align-center gap-2">
                <v-avatar size="26" color="grey-lighten-3" rounded>
                    <v-img v-if="item.avatar_url" :src="item.avatar_url" :alt="item.name ?? 'Customer'" cover />
                    <v-icon v-else size="18" color="grey-darken-1">mdi-account-circle</v-icon>
                </v-avatar>
                <div class="ml-2 text-capitalize">{{ item.name ?? '-' }}</div>
            </div>
        </template>
        <template #item.created_at="{ item }">
            <span>{{ item.created_at ?? '-' }}</span>
        </template>
        <template #item.total_order="{ item }">
            <span>{{ Number(item.total_order ?? 0) > 0 ? `${Number(item.total_order ?? 0)} Orders` : '-' }}</span>
        </template>
        <template #item.total_emi="{ item }">
            <span>{{ Number(item.total_emi ?? 0) > 0 ? `${Number(item.total_emi ?? 0)} Requests` : '-' }}</span>
        </template>
        <template #item.action="{ item }">
            <div class="d-flex align-center justify-end ga-1">
                <v-btn size="small" variant="flat" color="primary"
                    @click="router.push({ name: 'admin.customers.detail', params: { id: item.id } })">
                    detail
                </v-btn>
            </div>
        </template>
    </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import PageFilter from '@/components/filters/PageFilter.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listCustomers, type CustomersListItem } from '@/api/customers.api';
import { formatLongDate, formatPhoneNumber } from '@/shared/utils';
import AppTextField from '@/components/shared/AppTextField.vue';

type Customer = {
    id: number | string;
    name?: string | null;
    avatar_url?: string | null;
    email?: string | null;
    mobile?: string | null;
    total_order?: number;
    total_emi?: number;
    email_verified_at?: string | null;
    created_at?: string | null;
};

type ExportType = 'csv' | 'excel' | 'pdf';

const exportOptions: Array<{ type: ExportType; title: string; icon: string }> = [
    { type: 'csv', title: 'Export CSV', icon: 'mdi-file-delimited-outline' },
    { type: 'excel', title: 'Export Excel', icon: 'mdi-microsoft-excel' },
    { type: 'pdf', title: 'Export PDF', icon: 'mdi-file-pdf-box' },
];

const headers = [
    { title: 'Name', key: 'name', sortable: false, minWidth: '180' },
    { title: 'Email', key: 'email', sortable: false, minWidth: '220' },
    { title: 'Mobile', key: 'mobile', sortable: false, minWidth: '220' },
    { title: 'Orders', key: 'total_order', sortable: false, minWidth: '130' },
    { title: 'EMI', key: 'total_emi', sortable: false, minWidth: '120' },
    { title: 'Since', key: 'created_at', sortable: false, minWidth: '140' },
    { title: 'Actions', key: 'action', sortable: false, minWidth: '100', align: 'end' as const },
];

const items = ref<Customer[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');
const options = ref<DataTableOptions>({
    page: 1,
    itemsPerPage: 10,
    sortBy: [],
});
const hasLoadedOnce = ref(false);
const router = useRouter();

function onExport(type: ExportType) {
    // TODO: replace with real export API/download logic.
    console.log(`Export clicked: ${type}`);
}

async function fetchCustomers() {
    loading.value = true;
    try {
        const response = await listCustomers({
            page: options.value.page,
            per_page: options.value.itemsPerPage,
            search: search.value.trim() || undefined,
        });

        const list = Array.isArray(response) ? response : response?.data ?? [];
        items.value = list.map((customer: CustomersListItem) => ({
            id: String(customer.id ?? ''),
            name: customer.name ?? '-',
            avatar_url: customer.avatar_url ?? null,
            email: customer.email ?? '-',
            mobile: formatPhoneNumber(customer.contact_number
                ?? customer.mobile
                ?? '-'),
            total_order: Number(customer.total_order ?? 0),
            total_emi: Number(customer.total_emi ?? 0),
            email_verified_at: customer.email_verified_at ?? null,
            created_at: formatLongDate(customer.created_at),
        }));
        total.value = Number(response?.total ?? response?.meta?.total ?? list.length);
        if (response?.meta?.current_page) {
            options.value.page = Number(response.meta.current_page);
        }
        if (response?.meta?.per_page) {
            options.value.itemsPerPage = Number(response.meta.per_page);
        }
    } finally {
        loading.value = false;
    }
}

function onOptions(next: DataTableOptions) {
    options.value = next;
    if (!hasLoadedOnce.value) {
        hasLoadedOnce.value = true;
    }
    fetchCustomers();
}

function onSearch() {
    options.value.page = 1;
    fetchCustomers();
}

function onClearSearch() {
    search.value = '';
    options.value.page = 1;
    fetchCustomers();
}

onMounted(() => {
    if (!hasLoadedOnce.value) {
        fetchCustomers();
        hasLoadedOnce.value = true;
    }
});
</script>

<style scoped>
.export-menu-list :deep(.v-list-item-title) {
    font-size: 0.9rem;
}
</style>
