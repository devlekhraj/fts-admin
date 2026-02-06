<template>
  <v-app>
    <v-navigation-drawer v-model="drawer" app width="280">
      <v-sheet class="drawer-header py-1">
        <v-list-item title="Admin" subtitle="Control Panel" />
        <v-divider class="my-2 border-0" />
      </v-sheet>

      <v-list density="comfortable" class="pt-4">
        <template v-for="group in items" :key="group.group">
          <v-list-subheader>{{ group.group }}</v-list-subheader>
          <v-list-item v-for="link in group.links" :key="link.to" :to="link.to" :title="link.title"
            :prepend-icon="link.icon" rounded link />
        </template>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app flat class="admin-app-bar">
      <v-app-bar-nav-icon @click="drawer = !drawer" />
      <v-toolbar-title>
        <h5>{{ pageTitle }}</h5>
      </v-toolbar-title>
      <v-spacer />
      <v-btn variant="text" @click="logout">Logout</v-btn>
    </v-app-bar>

    <v-main class="admin-main">
      <v-container class="main-container-content pt-0" fluid>
        <RouterView />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const drawer = ref(true);
const router = useRouter();
const route = useRoute();

const titleByName: Record<string, string> = {
  dashboard: 'Dashboard',
  'emi.applications': 'EMI Application',
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
  'users.list': 'User Management',
  settings: 'Settings',
};

const pageTitle = computed(() => {
  const name = route.name ? String(route.name) : '';
  return titleByName[name] ?? 'Admin';
});

const items = [
  {
    group: 'Welcome',
    links: [{ title: 'Dashboard', to: '/dashboard', icon: 'mdi-view-dashboard-outline' }],
  },
  {
    group: 'EMI',
    links: [
      { title: 'EMI Application', to: '/emi/applications', icon: 'mdi-cash' },
      { title: 'EMI Users', to: '/emi/emi-users', icon: 'mdi-account-cash-outline' },
    ],
  },
  {
    group: 'Orders',
    links: [
      { title: 'All Orders', to: '/orders', icon: 'mdi-cart-outline' },
      { title: 'Pre Orders', to: '/pre-orders', icon: 'mdi-cart-arrow-down' },
      { title: 'Cart Items', to: '/cart-items', icon: 'mdi-cart-outline' },
      { title: 'Wish Pot', to: '/wish-pot', icon: 'mdi-heart-outline' },
      { title: 'Customers', to: '/customers', icon: 'mdi-account-outline' }
    ],
  },
  {
    group: 'Blogs',
    links: [
      { title: 'All Blogs', to: '/blogs', icon: 'mdi-note-multiple-outline' },
      { title: 'Create Blog', to: '/blogs/create', icon: 'mdi-note-plus-outline' },
      { title: 'Categories', to: '/blogs/categories', icon: 'mdi-folder-outline' },
    ],
  },
  {
    group: 'Catalog',
    links: [
      { title: 'Products', to: '/catalog/products', icon: 'mdi-package-variant-closed' },
      { title: 'Categories', to: '/catalog/categories', icon: 'mdi-shape-outline' },
      { title: 'Brands', to: '/catalog/brands', icon: 'mdi-tag-outline' },
      { title: 'Attributes', to: '/catalog/attributes', icon: 'mdi-tune-variant' },
    ],
  },
  {
    group: 'Marketing',
    links: [
      { title: 'Banners', to: '/marketing/banners', icon: 'mdi-image-outline' },
      { title: 'Campaigns', to: '/marketing/campaigns', icon: 'mdi-bullhorn-outline' },
    ],
  },
  {
    group: 'Settings',
    links: [
      { title: 'FAQs Management', to: '/settings/faqs', icon: 'mdi-help-circle-outline' },
      { title: 'Payment Methods', to: '/settings/payment-methods', icon: 'mdi-credit-card-outline' },
      { title: 'User Management', to: '/settings/users', icon: 'mdi-account-group-outline' },
      { title: 'Settings', to: '/settings', icon: 'mdi-cog-outline' },
    ],
  },
];

function logout() {
  localStorage.removeItem('admin_token');
  router.push('/login');
}
</script>
<style lang="scss">
header.v-toolbar.v-toolbar--collapse-start.v-toolbar--flat.v-toolbar--density-default.v-theme--light.v-locale--is-ltr.v-app-bar.admin-app-bar {
  background-color: transparent !important;
}

main.v-main {
  // background: linear-gradient(163deg, #f3fbff4f 0%, #f3f3f3 100%);
  background: linear-gradient(163deg, rgb(230 247 255 / 31%) 0%, #ffebeb 100%);
}

.custom-title {
  font-size: 0.9rem;
}

.main-container-content {
  max-height: calc(100vh - 65px);
  min-height: calc(100vh - 65px);
  overflow-y: scroll;
  background: linear-gradient(163deg, #f3fbff4f 0%, #f3f3f3 100%);
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

.v-list-item-title {
  font-size: 0.875rem !important;
  font-weight: 500;
}

.drawer-header {
  position: sticky;
  top: 0;
  z-index: 1001;
  background: rgb(var(--v-theme-surface));
}

.v-toolbar__content {
  background-color: transparent !important;
}

.admin-app-bar {
  background: transparent;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.admin-main {
  padding-top: 64px;
}
</style>
