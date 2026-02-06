import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { applyGuards } from './guards';
import AuthLayout from '../layouts/AuthLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import LoginPage from '../pages/LoginPage.vue';
import DashboardPage from '../pages/dashboard/DashboardPage.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    component: AuthLayout,
    children: [
      { path: '', name: 'login', component: LoginPage },
    ],
  },
  {
    path: '/',
    component: AdminLayout,
    redirect: '/dashboard',
    meta: { requiresAuth: false },
    children: [
      { path: 'dashboard', name: 'dashboard', component: DashboardPage, meta: { requiresAuth: false } },
      { path: 'emi/applications', name: 'emi.applications', component: () => import('../pages/emi/EmiApplicationsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'emi/emi-users', name: 'emi.users', component: () => import('../pages/emi/EmiUsersListPage.vue'), meta: { requiresAuth: false } },
      { path: 'blogs', name: 'blogs.list', component: () => import('../pages/blogs/BlogsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'blogs/create', name: 'blogs.create', component: () => import('../pages/blogs/BlogCreatePage.vue'), meta: { requiresAuth: false } },
      { path: 'blogs/:id/edit', name: 'blogs.edit', component: () => import('../pages/blogs/BlogEditPage.vue'), meta: { requiresAuth: false } },
      { path: 'blogs/categories', name: 'blogCategories.list', component: () => import('../pages/blog-categories/BlogCategoriesListPage.vue'), meta: { requiresAuth: false } },
      { path: 'orders', name: 'orders.list', component: () => import('../pages/orders/OrdersListPage.vue'), meta: { requiresAuth: false } },
      { path: 'pre-orders', name: 'orders.pre', component: () => import('../pages/orders/PreOrdersListPage.vue'), meta: { requiresAuth: false } },
      { path: 'cart-items', name: 'orders.cart', component: () => import('../pages/orders/CartItemsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'wish-pot', name: 'orders.wish', component: () => import('../pages/orders/WishPotListPage.vue'), meta: { requiresAuth: false } },
      { path: 'customers', name: 'customers.list', component: () => import('../pages/customers/CustomersListPage.vue'), meta: { requiresAuth: false } },
      { path: 'catalog/products', name: 'products.list', component: () => import('../pages/products/ProductsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'catalog/products/create', name: 'products.create', component: () => import('../pages/products/ProductCreatePage.vue'), meta: { requiresAuth: false } },
      { path: 'catalog/products/:id/edit', name: 'products.edit', component: () => import('../pages/products/ProductEditPage.vue'), meta: { requiresAuth: false } },
      { path: 'catalog/categories', name: 'categories.list', component: () => import('../pages/categories/CategoriesListPage.vue'), meta: { requiresAuth: false } },
      { path: 'catalog/brands', name: 'brands.list', component: () => import('../pages/brands/BrandsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'catalog/attributes', name: 'attributes.list', component: () => import('../pages/catalog/AttributesListPage.vue'), meta: { requiresAuth: false } },
      { path: 'marketing/banners', name: 'banners.list', component: () => import('../pages/banners/BannersListPage.vue'), meta: { requiresAuth: false } },
      { path: 'marketing/campaigns', name: 'campaigns.list', component: () => import('../pages/campaigns/CampaignsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'settings/faqs', name: 'faqs.list', component: () => import('../pages/settings/FaqsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'settings/payment-methods', name: 'paymentMethods.list', component: () => import('../pages/settings/PaymentMethodsListPage.vue'), meta: { requiresAuth: false } },
      { path: 'settings/users', name: 'users.list', component: () => import('../pages/settings/UsersListPage.vue'), meta: { requiresAuth: false } },
      { path: 'settings', name: 'settings', component: () => import('../pages/settings/SettingsPage.vue'), meta: { requiresAuth: false } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory('/admin/'),
  routes,
});

applyGuards(router);

export default router;
