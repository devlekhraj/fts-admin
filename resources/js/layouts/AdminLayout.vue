<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" app width="280">
            <v-sheet class="drawer-header py-4">
                <div class="d-flex flex-column align-center text-center">
                    <v-avatar size="56" class="mb-2">
                        <v-img
                            :src="typeof authStore.admin?.avatar === 'string' && authStore.admin?.avatar ? authStore.admin.avatar : 'https://placehold.co/64'"
                            alt="Admin" />
                    </v-avatar>
                    <div class="text-subtitle-2 font-weight-semibold">
                        {{ adminDisplayName }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                        {{ authStore.admin?.username ?? authStore?.admin?.fname + "@admin" }}
                    </div>
                </div>
                <v-divider class="my-3 border-0" />
            </v-sheet>
            <div class="px-6 pb-2">
                <v-text-field v-model="navSearch" color="primary" class="nav-search-field" density="compact" variant="outlined"
                    placeholder="Search menu" prepend-inner-icon="mdi-magnify" hide-details clearable />
            </div>
            <v-list density="comfortable" class="px-6" id="side-nav-items">
                <template v-for="group in filteredItems" :key="group.group">
                    <v-list-subheader class="mt-3" v-if="group && group.group">{{ group.group }}</v-list-subheader>
                    <v-list-item v-for="link in group.links" class="py-2" :key="link.to.name || link.title"
                        :to="link.to" :title="link.title" :prepend-icon="link.icon" rounded link />
                </template>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app flat class="admin-app-bar">
            <v-app-bar-nav-icon @click="drawer = !drawer" />
            <v-toolbar-title>
                <div class="toolbar-title-wrap">
                    <v-breadcrumbs class="toolbar-breadcrumbs pa-0" :items="breadcrumbItems">
                        <template #divider>
                            <v-icon size="14">mdi-chevron-right</v-icon>
                        </template>
                    </v-breadcrumbs>
                </div>
            </v-toolbar-title>
            <v-spacer />
            <div class="d-flex align-center ga-3 pr-6">

                <div class="d-flex align-center ga-2">
                    <v-avatar size="30">
                        <v-img v-if="adminAvatar" :src="adminAvatar" alt="Admin" />
                        <v-icon v-else size="28">mdi-account-circle</v-icon>
                    </v-avatar>
                    <div class="d-flex flex-column">
                        <div class="d-flex align-center ga-1 text-body-2 font-weight-medium">
                            <span>{{ adminDisplayName }}</span>
                            <v-btn icon size="x-small" color="error" :loading="isLoggingOut" variant="text" @click="logout">
                                <v-icon>mdi-logout</v-icon>
                            </v-btn>
                            
                        </div>
                    </div>
                </div>

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
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth.store';
import { logout as apiLogout } from '@/api/auth.api';

const drawer = ref(true);
const isLoggingOut = ref(false);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const titleByName: Record<string, string> = {
    'admin.dashboard': 'Dashboard',
    'admin.emi.requests': 'EMI Requests',
    'admin.emi.requests.detail': 'EMI Request Detail',
    'admin.emi.users': 'EMI Users',
    'admin.orders.list': 'All Orders',
    'admin.orders.pre': 'Pre Orders',
    'admin.orders.cart': 'Cart Items',
    'admin.orders.wish': 'Wish Pot',
    'admin.customers.list': 'Customers',
    'admin.products.list': 'Products',
    'admin.products.create': 'Create Product',
    'admin.products.edit': 'Edit Product',
    'admin.categories.list': 'Categories',
    'admin.brands.list': 'Brands',
    'admin.attributes.list': 'Attributes',
    'admin.treks.regions': 'Regions',
    'admin.treks.list': 'Tour / Treks',
    'admin.treks.fixed.departure': 'Fixed Departure',
    'admin.treks.guide.profile': 'Guide Profile',
    'admin.blogs.list': 'All Blogs',
    'admin.blogs.create': 'Create Blog',
    'admin.blogs.edit': 'Edit Blog',
    'admin.blogCategories.list': 'Blog Categories',
    'admin.banners.list': 'Banners',
    'admin.campaigns.list': 'Campaigns',
    'admin.pages.list': 'Pages',
    'admin.inquiries.list': 'Inquiries',
    'admin.users.list': 'User Lists',
    'admin.faqs.list': 'FAQs Management',
    'admin.paymentMethods.list': 'Payment Methods',
    'admin.list': 'Admin Management',
    'admin.roles.manage': 'Role Management',
    'admin.settings': 'Settings',
};

const parentRouteByName: Record<string, string> = {
    'admin.emi.requests.detail': 'admin.emi.requests',
    'admin.products.create': 'admin.products.list',
    'admin.products.edit': 'admin.products.list',
    'admin.blogs.create': 'admin.blogs.list',
    'admin.blogs.edit': 'admin.blogs.list',
};

const pageTitle = computed(() => {
    return (route.meta.title as string) ?? 'Admin';
});

const pageSubtitle = computed(() => {
    if (route.name === 'admin.emi.requests.detail' && route.params.id) {
        return `Request #${route.params.id}`;
    }
    return (route.meta.subtitle as string) ?? '';
});

const breadcrumbItems = computed(() => {
    const currentName = typeof route.name === 'string' ? route.name : '';
    const chain: string[] = [];

    if (currentName !== 'admin.dashboard') {
        chain.push('admin.dashboard');
    }

    const parentName = parentRouteByName[currentName];
    if (parentName && parentName !== 'admin.dashboard') {
        chain.push(parentName);
    }

    if (currentName) {
        chain.push(currentName);
    } else {
        chain.push('admin.dashboard');
    }

    return chain
        .filter((name, index, arr) => arr.indexOf(name) === index)
        .map((name, index, arr) => {
            const isLast = index === arr.length - 1;
            const title = name === currentName ? pageTitle.value : (titleByName[name] ?? name);
            return {
                title,
                disabled: isLast,
                to: isLast ? undefined : { name },
            };
        });
});

const now = ref(new Date());
const adminMenuOpen = ref(false);
const adminAvatar = computed(() => {
    const avatar = authStore.admin?.avatar;
    if (typeof avatar === 'string' && avatar.trim()) return avatar;
    return '';
});
const adminDisplayName = computed(() => {
    const admin = authStore.admin as Record<string, unknown> | null;
    if (!admin) return 'Admin';

    if (typeof admin.fname === 'string' && admin.fname.trim()) return admin.fname;
    if (typeof admin.name === 'string' && admin.name.trim()) return admin.name;
    return 'Admin';
});
const adminUsername = computed(() => {
    const admin = authStore.admin as Record<string, unknown> | null;
    if (!admin) return 'admin';

    if (typeof admin.username === 'string' && admin.username.trim()) return admin.username;
    if (typeof admin.fname === 'string' && admin.fname.trim()) return admin.fname.toLowerCase();
    return 'admin';
});
const adminRole = computed(() => {
    const admin = authStore.admin as Record<string, unknown> | null;
    if (!admin) return 'Admin';

    if (typeof admin.role === 'string' && admin.role.trim()) return admin.role;
    if (typeof admin.role_name === 'string' && admin.role_name.trim()) return admin.role_name;

    if (Array.isArray(admin.roles) && admin.roles.length > 0) {
        const firstRole = admin.roles[0];
        if (typeof firstRole === 'string' && firstRole.trim()) return firstRole;
        if (firstRole && typeof firstRole === 'object') {
            const roleObj = firstRole as Record<string, unknown>;
            if (typeof roleObj.name === 'string' && roleObj.name.trim()) return roleObj.name;
        }
    }

    return 'Admin';
});
const currentDateTime = computed(() =>
    new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(now.value)
);

let clockTimer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    clockTimer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onBeforeUnmount(() => {
    if (clockTimer) clearInterval(clockTimer);
});

const navSearch = ref('');

const items = [
    {
        group: 'Welcome',
        links: [{ title: 'Dashboard', to: { name: 'admin.dashboard' }, icon: 'mdi-view-dashboard-outline' }],
    },
    {
        group: 'Operations',
        links: [
            { title: 'Inquiries', to: { name: 'admin.inquiries.list' }, icon: 'mdi-message-text-outline' },
            { title: 'User Lists', to: { name: 'admin.users.list' }, icon: 'mdi-account-multiple-outline' },
        ],
    },
    {
        group: 'Catalog',
        links: [
            { title: 'Regions', to: { name: 'admin.treks.regions' }, icon: 'mdi-tag-multiple-outline' },
            { title: 'Tour / Treks', to: { name: 'admin.treks.list' }, icon: 'mdi-briefcase-variant' },
            { title: 'Fixed Departure', to: { name: 'admin.treks.fixed.departure' }, icon: 'mdi-calendar-check' },
            { title: 'Guide Profile', to: { name: 'admin.treks.guide.profile' }, icon: 'mdi-account-cowboy-hat-outline' },
        ],
    },
    {
        group: 'Blogs',
        links: [
            { title: 'All Blogs', to: { name: 'admin.blogs.list' }, icon: 'mdi-note-multiple-outline' },
            { title: 'Categories', to: { name: 'admin.blogCategories.list' }, icon: 'mdi-folder-outline' },
        ],
    },
    {
        group: 'Page Setup',
        links: [
            { title: 'Banners', to: { name: 'admin.banners.list' }, icon: 'mdi-image-outline' },
            { title: 'Pages', to: { name: 'admin.pages.list' }, icon: 'mdi-file-document-outline' },
        ],
    },
    {
        group: 'Settings',
        links: [
            { title: 'Admins', to: { name: 'admin.list' }, icon: 'mdi-account-group-outline' },
            { title: 'Roles', to: { name: 'admin.roles.manage' }, icon: 'mdi-shield-account-outline' },
            { title: 'FAQs', to: { name: 'admin.faqs.list' }, icon: 'mdi-help-circle-outline' },
            { title: 'Settings', to: { name: 'admin.settings' }, icon: 'mdi-cog-outline' },
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
    if (isLoggingOut.value) return;

    isLoggingOut.value = true;
    try {
        try {
            await apiLogout();
        } catch {
            // ignore logout API errors
        }
        authStore.token = null;
        authStore.admin = null;
        localStorage.removeItem('admin_token');
        await router.push({ name: 'admin.login' });
    } finally {
        isLoggingOut.value = false;
    }
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


.v-list-item {
    min-height: 36px !important;
    font-size: 0.8rem;
    // font-weight: 400;
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

.admin-menu-trigger {
    min-width: 22px;
    height: 22px;
}

.toolbar-title-wrap {
    display: flex;
    flex-direction: column;
}

.toolbar-breadcrumbs :deep(.v-breadcrumbs-item),
.toolbar-breadcrumbs :deep(.v-breadcrumbs-divider) {
    font-size: 0.875rem;
    color: rgba(var(--v-theme-on-surface), 0.58);
}

.toolbar-page-title {
    line-height: 1.1;
}

.nav-search-field :deep(.v-field__input) {
    font-size: 0.8rem;
}

.nav-search-field :deep(input::placeholder) {
    font-size: 0.8rem;
}

.nav-search-field :deep(.v-field) {
    --v-field-input-size: 0.8rem;
}
</style>
