<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" app width="280">
            <v-sheet class="drawer-header py-4">
                <div class="d-flex flex-column align-center text-center">
                    <template v-if="isAdminLoading">
                        <v-skeleton-loader class="mb-2 drawer-admin-avatar-skeleton" type="avatar" />
                        <v-skeleton-loader class="drawer-admin-name-skeleton" type="text" />
                        <v-skeleton-loader class="drawer-admin-username-skeleton" type="text" />
                    </template>
                    <template v-else>
                        <v-avatar size="56" class="mb-2">
                            <v-img v-if="adminAvatar" :src="adminAvatar" alt="Admin" />
                            <v-icon v-else size="52">mdi-account-circle</v-icon>
                        </v-avatar>
                        <div class="text-subtitle-2 font-weight-semibold">
                            {{ adminDisplayName }}
                        </div>
                        <div class="text-caption text-medium-emphasis text-lowercase">
                            @{{ authStore.admin?.username }}
                        </div>
                    </template>
                </div>
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
import { useAuthStore } from '@/stores/auth.store';
import { logout as apiLogout } from '@/api/auth.api';
import { menuByProject, type NavGroup, type ProjectType } from '@/layouts/navigation';
import { breadcrumbRouteConfig } from '@/layouts/navigation/breadcrumbs';

const drawer = ref(true);
const isLoggingOut = ref(false);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const pageTitle = computed(() => {
    return (route.meta.title as string) ?? 'Admin';
});

const breadcrumbItems = computed(() => {
    const currentName = typeof route.name === 'string' ? route.name : '';
    const chain: string[] = [];

    if (currentName !== 'admin.dashboard') {
        chain.push('admin.dashboard');
    }

    const parentName = breadcrumbRouteConfig[currentName]?.parent;
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
            const title = name === currentName ? pageTitle.value : (breadcrumbRouteConfig[name]?.title ?? name);
            return {
                title,
                disabled: isLast,
                to: isLast ? undefined : { name },
            };
        });
});

const isAdminLoading = computed(() => authStore.isAuthenticated && !authStore.admin);

const adminAvatar = computed(() => {
    const avatar = authStore.admin?.avatar_url;
    if (typeof avatar === 'string' && avatar.trim()) return avatar;
    return '';
});
const adminDisplayName = computed(() => {
    return authStore.admin?.name?.trim() || 'Admin';
});
const navSearch = ref('');

const projectType = String(import.meta.env.VITE_PROJECT_TYPE ?? 'ecommerce').toLowerCase() as ProjectType;

const items = computed<NavGroup[]>(() => menuByProject[projectType] ?? menuByProject.travel);

const filteredItems = computed(() => {
    const query = (navSearch.value ?? '').trim().toLowerCase();
    if (!query) return items.value;

    return items.value
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

.drawer-admin-avatar-skeleton {
    width: 56px;
    height: 56px;
}

.drawer-admin-name-skeleton {
    width: 150px;
    height: 20px;
}

.drawer-admin-username-skeleton {
    width: 120px;
    height: 16px;
}

.drawer-admin-avatar-skeleton :deep(.v-skeleton-loader__avatar),
.drawer-admin-name-skeleton :deep(.v-skeleton-loader__text),
.drawer-admin-username-skeleton :deep(.v-skeleton-loader__text) {
    margin: 0;
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
