<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" app width="280">
            <v-sheet class="drawer-header py-4">
                <div class="d-flex flex-column align-center text-center">
                    <v-avatar size="56" class="mb-2">
                        <v-img :src="typeof authStore.admin?.avatar === 'string' && authStore.admin?.avatar ? authStore.admin.avatar : 'https://placehold.co/64'" alt="Admin" />
                    </v-avatar>
                    <div class="text-subtitle-2 font-weight-semibold">
                        {{ authStore.admin?.name ?? 'Admin' }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                        {{ authStore.admin?.email ?? 'admin@example.com' }}
                    </div>
                </div>
                <v-divider class="my-3 border-0" />
            </v-sheet>
            <div class="px-6 pb-2">
                <v-text-field
                    v-model="navSearch"
                    density="compact"
                    variant="outlined"
                    placeholder="Search menu"
                    prepend-inner-icon="mdi-magnify"
                    hide-details
                    clearable
                />
            </div>
            <v-list density="comfortable" class="px-6" id="side-nav-items">
                <template v-for="group in filteredItems" :key="group.group">
                    <v-list-subheader class="mt-3" v-if="group && group.group">{{ group.group }}</v-list-subheader>
                    <v-list-item v-for="link in group.links" class="py-2" :key="link.to.name || link.title" :to="link.to" :title="link.title"
                        :prepend-icon="link.icon" rounded link />
                </template>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app flat class="admin-app-bar">
            <v-app-bar-nav-icon @click="drawer = !drawer" />
            <v-toolbar-title>
                <div>
                    <h5>{{ pageTitle }}</h5>
                    <div v-if="pageSubtitle" class="text-caption text-medium-emphasis">{{ pageSubtitle }}</div>
                </div>
            </v-toolbar-title>
            <v-spacer />
            <div class="d-flex align-center gap-3">
                <div class="text-caption text-medium-emphasis">Login at : 09:45 AM</div>
                <v-btn icon size="small" class="ml-3" color="error" @click="logout" aria-label="Logout">
                    <v-icon>mdi-logout</v-icon>
                </v-btn>
            </div>
        </v-app-bar>
        <!-- <v-divider></v-divider> -->

        <v-main class="admin-main">
            <v-container class="main-container-content py-4" fluid>
                <RouterView />
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.store';

const drawer = ref(true);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const titleByName: Record<string, string> = {
    dashboard: 'Dashboard',
    'emi.requests': 'EMI Requests',
    'emi.users': 'EMI Users',
    'orders.list': 'All Orders',
    'orders.pre': 'Pre Orders',
    'orders.cart': 'Cart Items',
    'orders.wish': 'Wish Pot',
    'customers.list': 'Customers',
    'products.list': 'Products',
    'products.create': 'Create Product',
    'products.edit': 'Edit Product',
    'categories.list': 'Categories',
    'brands.list': 'Brands',
    'attributes.list': 'Attributes',
    'blogs.list': 'All Blogs',
    'blogs.create': 'Create Blog',
    'blogs.edit': 'Edit Blog',
    'blogCategories.list': 'Blog Categories',
    'banners.list': 'Banners',
    'campaigns.list': 'Campaigns',
    'faqs.list': 'FAQs Management',
    'paymentMethods.list': 'Payment Methods',
    'admin.list': 'Admin Management',
    'roles.manage': 'Role Management',
    settings: 'Settings',
};

const pageTitle = computed(() => {
    return (route.meta.title as string) ?? 'Admin';
});

const pageSubtitle = computed(() => {
    if (route.name === 'emi.requests.detail' && route.params.id) {
        return `Request #${route.params.id}`;
    }
    return (route.meta.subtitle as string) ?? '';
});

const navSearch = ref('');

const items = [
    {
        group: '',
        links: [{ title: 'Dashboard', to: { name: 'dashboard' }, icon: 'mdi-view-dashboard-outline' }],
    },
    {
        group: 'EMI',
        links: [
            { title: 'EMI Requests', to: { name: 'emi.requests' }, icon: 'mdi-cash' },
            { title: 'EMI Banks', to: { name: 'emi.banks' }, icon: 'mdi-bank' },
            { title: 'EMI Users', to: { name: 'emi.users' }, icon: 'mdi-account-cash-outline' },
        ],
    },
    {
        group: 'Orders',
        links: [
            { title: 'All Orders', to: { name: 'orders.list' }, icon: 'mdi-cart-outline' },
            { title: 'Pre Orders', to: { name: 'orders.pre' }, icon: 'mdi-cart-arrow-down' },
            { title: 'Cart Items', to: { name: 'orders.cart' }, icon: 'mdi-cart-outline' },
            { title: 'Wish Pot', to: { name: 'orders.wish' }, icon: 'mdi-heart-outline' },
            { title: 'Customers', to: { name: 'customers.list' }, icon: 'mdi-account-outline' }
        ],
    },
    {
        group: 'Blogs',
        links: [
            { title: 'All Blogs', to: { name: 'blogs.list' }, icon: 'mdi-note-multiple-outline' },
            { title: 'Create Blog', to: { name: 'blogs.create' }, icon: 'mdi-note-plus-outline' },
            { title: 'Categories', to: { name: 'blogCategories.list' }, icon: 'mdi-folder-outline' },
        ],
    },
    {
        group: 'Catalog',
        links: [
            { title: 'Products', to: { name: 'products.list' }, icon: 'mdi-package-variant-closed' },
            { title: 'Categories', to: { name: 'categories.list' }, icon: 'mdi-shape-outline' },
            { title: 'Brands', to: { name: 'brands.list' }, icon: 'mdi-tag-outline' },
            { title: 'Attributes', to: { name: 'attributes.list' }, icon: 'mdi-tune-variant' },
        ],
    },
    {
        group: 'Marketing',
        links: [
            { title: 'Banners', to: { name: 'banners.list' }, icon: 'mdi-image-outline' },
            { title: 'Campaigns', to: { name: 'campaigns.list' }, icon: 'mdi-bullhorn-outline' },
        ],
    },
    {
        group: 'Settings',
        links: [
            { title: 'FAQs Management', to: { name: 'faqs.list' }, icon: 'mdi-help-circle-outline' },
            { title: 'Payment Methods', to: { name: 'paymentMethods.list' }, icon: 'mdi-credit-card-outline' },
            { title: 'Admin Management', to: { name: 'admin.list' }, icon: 'mdi-account-group-outline' },
            { title: 'Role Management', to: { name: 'roles.manage' }, icon: 'mdi-shield-account-outline' },
            { title: 'Settings', to: { name: 'settings' }, icon: 'mdi-cog-outline' },
        ],
    },
];

const filteredItems = computed(() => {
    const query = (navSearch.value ?? '').trim().toLowerCase();
    if (!query) return items;

    return items
        .map((group) => {
            const groupMatch = (group.group ?? '').toLowerCase().includes(query);
            const links = group.links.filter((link) =>
                link.title.toLowerCase().includes(query)
            );
            return {
                ...group,
                links: groupMatch ? group.links : links,
            };
        })
        .filter((group) => group.links.length > 0);
});

async function logout() {
    await authStore.logout();
    router.push('/login');
}
</script>
<style lang="scss" scoped>
main.v-main {
    background-color: #fff;
}

.custom-title {
    font-size: 0.9rem;
}

.main-container-content {
    max-height: calc(100vh - 65px);
    min-height: calc(100vh - 65px);
    overflow-y: scroll;
    background-color: #fff;
}

.hover-notification {
    transition: background-color 0.2s;
    border-radius: 4px;
}

.hover-notification:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

.v-navigation-drawer {
    border: 0 !important;
    box-shadow: none;
    background-color: transparent;
}

.v-navigation-drawer--temporary.v-navigation-drawer--active {
    box-shadow: none;

}

.v-list-item.v-list-item--active {
    background: linear-gradient(90deg, #f1f6f9 0%, #e3f2fd 100%);
    color: #1976d2;
}

.v-list-subheader__text {
    font-size: 0.8rem;
    font-weight: 500;

    &:not(:first-child) {
        margin-top: 20px;
    }
}

.v-list-item {
    min-height: 36px !important;
    font-size: 0.875rem;
    font-weight: 500;
}


.drawer-header {
    background: linear-gradient(262deg, #f4faff 0%, #fafafa 100%) !important;
    padding: 0 10px;
}

.admin-app-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background-color: #fff;
    border-bottom: 1px solid #f5f5f5;

}

.admin-main {
    padding-top: 64px;
}
</style>
