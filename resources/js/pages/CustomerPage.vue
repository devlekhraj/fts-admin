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
    </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listCustomers, type CustomersListItem } from '@/api/customers.api';
import { formatLongDate, formatPhoneNumber } from '@/shared/utils';

type Customer = {
    id: number | string;
    name?: string | null;
    avatar_url?: string | null;
    email?: string | null;
    mobile?: string | null;
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
    { title: 'Created', key: 'created_at', sortable: false, minWidth: '140' },
];

const items = ref<Customer[]>([]);
const total = ref(0);
const loading = ref(false);
const options = ref<DataTableOptions>({
    page: 1,
    itemsPerPage: 10,
    sortBy: [],
});
const hasLoadedOnce = ref(false);

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
