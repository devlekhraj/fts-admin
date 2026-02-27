<template>
    <!-- <AppPageHeader title="Role Management" subtitle="Manage roles and their permissions" /> -->

    <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading"
        :items-per-page="options.itemsPerPage" @update:options="onOptions">
        <template #actions>
            <v-container fluid>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-text-field density="compact" variant="outlined" label="Search"
                            placeholder="Search by name, email, or username" prepend-inner-icon="mdi-magnify"
                            hide-details clearable style="min-width: 200px" />
                    </v-col>

                    <v-col cols="12" md="2" class="d-flex align-center">
                        <v-btn color="primary" variant="tonal" height="40">
                            <v-icon start>mdi-magnify</v-icon>
                            Search
                        </v-btn>
                    </v-col>
                    <v-col cols="12" md="4" class="d-flex align-center justify-end">
                        <!-- <AdminCreateButton /> -->
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <div class="text-medium-emphasis">
                            <span class="text-primary" style="font-size: smaller;">
                                Total: {{ total }} Items found.
                            </span>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </template>
        <template #item.name="{ item }">
            <div class="d-flex align-center gap-2">
                <v-avatar size="28" color="grey-lighten-3">
                    <v-icon size="18" color="grey-darken-1">mdi-shield-account-outline</v-icon>
                </v-avatar>
                <span class="ml-2 text-capitalize">{{ item.name ?? '-' }}</span>
            </div>
        </template>
        <template #item.permissions="{ item }">
            <div class="d-flex align-center gap-2">
                <v-chip v-for="permission in item.permissions" :key="permission" size="small" variant="tonal"
                    class="text-uppercase font-weight-bold mr-2" label>
                    {{ permission }}
                </v-chip>
            </div>
        </template>
    </AppDataTable>
</template>

<script setup lang="ts">

import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { list as listRoles } from '@/api/roles.api';
import { onMounted, ref } from 'vue';
import { useSnackbarStore } from '@/stores/snackbar.store';
import { getErrorMessage } from '@/shared/errors';

type Role = {
    id?: number | string;
    name?: string | null;
};

const headers = [
    { title: 'Role', key: 'name', minWidth: '220', sortable: false },
    { title: 'Permissions', key: 'permissions', minWidth: '500', sortable: false },
];

const snackbar = useSnackbarStore();
const loading = ref(false);
const allRoles = ref<Role[]>([]);
const items = ref<Role[]>([]);
const total = ref(0);
const options = ref<DataTableOptions>({
    page: 1,
    itemsPerPage: 10,
    sortBy: [],
});

function applyPagination() {
    const start = (options.value.page - 1) * options.value.itemsPerPage;
    const end = start + options.value.itemsPerPage;
    items.value = allRoles.value.slice(start, end);
}

async function fetchRoles() {
    loading.value = true;
    try {
        const { data } = await listRoles();
        const list = Array.isArray(data) ? data : data?.data ?? [];
        allRoles.value = list;
        total.value = list.length;
        applyPagination();
    } catch (err) {
        const message = getErrorMessage(err);
        snackbar.show({ message, color: 'error' });
    } finally {
        loading.value = false;
    }
}

function onOptions(next: DataTableOptions) {
    options.value = next;
    applyPagination();
}

onMounted(fetchRoles);
</script>

<style scoped lang="scss">
:deep(.v-toolbar__content) {
    height: unset !important;
}
</style>
