import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import AuthLayout from '../layouts/AuthLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import LoginPage from '../pages/auth/LoginPage.vue';
import DashboardPage from '../pages/dashboard/DashboardPage.vue';
import { useAuthStore } from '../stores/auth.store';

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
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'dashboard', component: DashboardPage, meta: { title: 'Dashboard' } },
      // emi requests
      { path: 'emi-requests', name: 'emi.requests', component: () => import('@/pages/emi/EmiRequestsListPage.vue'), meta: { title: 'EMI Requests' } },
      { path: 'emi-requests/:id', name: 'emi.requests.detail', component: () => import('@/pages/emi/EmiRequestDetailPage.vue'), meta: { title: 'EMI Request Detail' } },
      { path: 'emi-users', name: 'emi.users', component: () => import('@/pages/emi/EmiUsersListPage.vue'), meta: { title: 'EMI Users' } },
      { path: 'emi-banks', name: 'emi.banks', component: () => import('@/pages/emi/EmiBankListPage.vue'), meta: { title: 'EMI Banks' } },
      // blogs
      { path: 'blogs', name: 'blogs.list', component: () => import('@/pages/blogs/BlogsListPage.vue'), meta: { title: 'All Blogs' } },
      { path: 'blogs-create', name: 'blogs.create', component: () => import('@/pages/blogs/BlogCreatePage.vue'), meta: { title: 'Create Blog' } },
      { path: 'blogs/:id/edit', name: 'blogs.edit', component: () => import('@/pages/blogs/BlogEditPage.vue'), meta: { title: 'Edit Blog' } },
      { path: 'blogs-categories', name: 'blogCategories.list', component: () => import('@/pages/blog-categories/BlogCategoriesListPage.vue'), meta: { title: 'Blog Categories' } },
      // orders
      { path: 'orders', name: 'orders.list', component: () => import('@/pages/orders/OrdersListPage.vue'), meta: { title: 'All Orders' } },
      { path: 'pre-orders', name: 'orders.pre', component: () => import('@/pages/orders/PreOrdersListPage.vue'), meta: { title: 'Pre Orders' } },
      { path: 'cart-items', name: 'orders.cart', component: () => import('@/pages/orders/CartItemsListPage.vue'), meta: { title: 'Cart Items' } },
      { path: 'wish-pot', name: 'orders.wish', component: () => import('@/pages/orders/WishPotListPage.vue'), meta: { title: 'Wish Pot' } },
      // customers
      { path: 'customers', name: 'customers.list', component: () => import('@/pages/customers/CustomersListPage.vue'), meta: { title: 'Customers' } },
      // catalog
      { path: 'catalog-products', name: 'products.list', component: () => import('@/pages/products/ProductsListPage.vue'), meta: { title: 'Products' } },
      { path: 'catalog-products-create', name: 'products.create', component: () => import('@/pages/products/ProductCreatePage.vue'), meta: { title: 'Create Product' } },
      { path: 'catalog-products/:id/edit', name: 'products.edit', component: () => import('@/pages/products/ProductEditPage.vue'), meta: { title: 'Edit Product' } },
      { path: 'catalog-categories', name: 'categories.list', component: () => import('@/pages/categories/CategoriesListPage.vue'), meta: { title: 'Categories' } },
      { path: 'catalog-brands', name: 'brands.list', component: () => import('@/pages/brands/BrandsListPage.vue'), meta: { title: 'Brands' } },
      { path: 'catalog-attributes', name: 'attributes.list', component: () => import('@/pages/catalog/AttributesListPage.vue'), meta: { title: 'Attributes' } },
      // marketing
      { path: 'marketing-banners', name: 'banners.list', component: () => import('@/pages/banners/BannersListPage.vue'), meta: { title: 'Banners' } },
      { path: 'marketing-campaigns', name: 'campaigns.list', component: () => import('@/pages/campaigns/CampaignsListPage.vue'), meta: { title: 'Campaigns' } },
      // settings
      { path: 'settings-faqs', name: 'faqs.list', component: () => import('@/pages/settings/FaqsListPage.vue'), meta: { title: 'FAQs Management' } },
      { path: 'settings-payment-methods', name: 'paymentMethods.list', component: () => import('@/pages/settings/PaymentMethodsListPage.vue'), meta: { title: 'Payment Methods' } },
      { path: 'admin-list', name: 'admin.list', component: () => import('@/pages/settings/AdminListPage.vue'), meta: { title: 'Admin Management' } },
      { path: 'role-management', name: 'roles.manage', component: () => import('@/pages/settings/RoleManagementPage.vue'), meta: { title: 'Role Management' } },
      { path: 'settings', name: 'settings', component: () => import('@/pages/settings/SettingsPage.vue'), meta: { title: 'Settings' } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory('/admin/'),
  routes,
});

router.beforeEach((to) => {
  const auth = useAuthStore();
  const isLogin = to.path === '/login';
  const requiresAuth = to.matched.some((record) => Boolean(record.meta.requiresAuth));

  if (requiresAuth && !auth.isAuthenticated) {
    return { path: '/login' };
  }

  if (isLogin && auth.isAuthenticated) {
    return { path: '/dashboard' };
  }

  return true;
});

export async function initAuth() {
  const auth = useAuthStore();
  auth.initFromStorage();
  if (auth.token) {
    try {
      await auth.fetchMe();
    } catch {
      await auth.logout();
    }
  }
}

export default router;
