import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import AuthLayout from '../layouts/AuthLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import LoginPage from '../pages/LoginPage.vue';
import DashboardPage from '../pages/DashboardPage.vue';
import { useAuthStore } from '../stores/auth.store';

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    component: AuthLayout,
    children: [
      { path: '', name: 'admin.login', component: LoginPage },
    ],
  },
  {
    path: '/',
    component: AdminLayout,
    redirect: { name: 'admin.dashboard' },
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'admin.dashboard', component: DashboardPage, meta: { title: 'Dashboard' } },
      // emi requests
      { path: 'emi-requests', name: 'admin.emi.requests', component: () => import('@/pages/EmiRequestsListPage.vue'), meta: { title: 'EMI Requests' } },
      { path: 'emi-requests/:id', name: 'admin.emi.requests.detail', component: () => import('@/pages/EmiRequestDetailPage.vue'), meta: { title: 'EMI Request Detail' } },
      { path: 'emi-users', name: 'admin.emi.users', component: () => import('@/pages/EmiUsersListPage.vue'), meta: { title: 'EMI Users' } },
      { path: 'emi-banks', name: 'admin.emi.banks', component: () => import('@/pages/EmiBankListPage.vue'), meta: { title: 'EMI Banks' } },
      // blogs
      { path: 'blogs', name: 'admin.blogs.list', component: () => import('@/pages/BlogsListPage.vue'), meta: { title: 'All Blogs' } },
      { path: 'blogs-create', name: 'admin.blogs.create', component: () => import('@/pages/BlogCreatePage.vue'), meta: { title: 'Create Blog' } },
      { path: 'blogs/:id/edit', name: 'admin.blogs.edit', component: () => import('@/pages/BlogEditPage.vue'), meta: { title: 'Edit Blog' } },
      { path: 'blog-categories', name: 'admin.blogCategories.list', component: () => import('@/pages/BlogCategoriesListPage.vue'), meta: { title: 'Blog Categories' } },
      // orders
      { path: 'orders', name: 'admin.orders.list', component: () => import('@/pages/OrdersListPage.vue'), meta: { title: 'All Orders' } },
      { path: 'pre-orders', name: 'admin.orders.pre', component: () => import('@/pages/PreOrdersListPage.vue'), meta: { title: 'Pre Orders' } },
      { path: 'cart-items', name: 'admin.orders.cart', component: () => import('@/pages/CartItemsListPage.vue'), meta: { title: 'Cart Items' } },
      { path: 'wish-pot', name: 'admin.orders.wish', component: () => import('@/pages/WishPotListPage.vue'), meta: { title: 'Wish Pot' } },
      // customers
      { path: 'customers', name: 'admin.customers.list', component: () => import('@/pages/CustomersListPage.vue'), meta: { title: 'Customers' } },
      // catalog
      { path: 'catalog-products', name: 'admin.products.list', component: () => import('@/pages/ProductsListPage.vue'), meta: { title: 'Products' } },
      { path: 'catalog-products-create', name: 'admin.products.create', component: () => import('@/pages/ProductCreatePage.vue'), meta: { title: 'Create Product' } },
      { path: 'catalog-products/:id/edit', name: 'admin.products.edit', component: () => import('@/pages/ProductEditPage.vue'), meta: { title: 'Edit Product' } },
      { path: 'catalog-categories', name: 'admin.categories.list', component: () => import('@/pages/CategoriesListPage.vue'), meta: { title: 'Categories' } },
      { path: 'catalog-brands', name: 'admin.brands.list', component: () => import('@/pages/BrandsListPage.vue'), meta: { title: 'Brands' } },
      { path: 'catalog-attributes', name: 'admin.attributes.list', component: () => import('@/pages/AttributesListPage.vue'), meta: { title: 'Attributes' } },
      { path: 'treks-regions', name: 'admin.treks.regions', component: () => import('@/pages/TreksRegionsPage.vue'), meta: { title: 'Regions' } },
      { path: 'treks-list', name: 'admin.treks.list', component: () => import('@/pages/TreksListPage.vue'), meta: { title: 'Tour / Treks' } },
      { path: 'treks-fixed-departure', name: 'admin.treks.fixed.departure', component: () => import('@/pages/TreksFixedDeparturePage.vue'), meta: { title: 'Fixed Departure' } },
      { path: 'treks-guide-profile', name: 'admin.treks.guide.profile', component: () => import('@/pages/TreksGuideProfilePage.vue'), meta: { title: 'Guide Profile' } },
      // marketing
      { path: 'marketing-banners', name: 'admin.banners.list', component: () => import('@/pages/BannersListPage.vue'), meta: { title: 'Banners' } },
      { path: 'marketing-campaigns', name: 'admin.campaigns.list', component: () => import('@/pages/CampaignsListPage.vue'), meta: { title: 'Campaigns' } },
      { path: 'pages', name: 'admin.pages.list', component: () => import('@/pages/PagesListPage.vue'), meta: { title: 'Pages' } },
      { path: 'inquiries', name: 'admin.inquiries.list', component: () => import('@/pages/InquiriesListPage.vue'), meta: { title: 'Inquiries' } },
      { path: 'user-lists', name: 'admin.users.list', component: () => import('@/pages/UserListsPage.vue'), meta: { title: 'User Lists' } },
      // settings
      { path: 'settings-faqs', name: 'admin.faqs.list', component: () => import('@/pages/FaqsListPage.vue'), meta: { title: 'FAQs Management' } },
      { path: 'settings-payment-methods', name: 'admin.paymentMethods.list', component: () => import('@/pages/PaymentMethodsListPage.vue'), meta: { title: 'Payment Methods' } },
      { path: 'admin-list', name: 'admin.list', component: () => import('@/pages/AdminListPage.vue'), meta: { title: 'Admin Management' } },
      { path: 'role-management', name: 'admin.roles.manage', component: () => import('@/pages/RoleManagementPage.vue'), meta: { title: 'Role Management' } },
      { path: 'settings', name: 'admin.settings', component: () => import('@/pages/SettingsPage.vue'), meta: { title: 'Settings' } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory('/admin/'),
  routes,
});

router.beforeEach((to) => {
  const auth = useAuthStore();
  const isLogin = to.name === 'admin.login';
  const requiresAuth = to.matched.some((record) => Boolean(record.meta.requiresAuth));

  if (requiresAuth && !auth.isAuthenticated) {
    return { name: 'admin.login' };
  }

  if (isLogin && auth.isAuthenticated) {
    return { name: 'admin.dashboard' };
  }

  return true;
});

export async function initAuth() {
  const auth = useAuthStore();
  auth.initFromStorage();
  if (auth.token) {
    try {
      await auth.fetchProfile();
    } catch {
      await auth.logout();
    }
  }
}

export default router;
