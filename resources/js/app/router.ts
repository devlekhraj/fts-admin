import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import AuthLayout from '../layouts/AuthLayout.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import LoginPage from '../pages/LoginPage.vue';
import OverviewPage from '../pages/OverviewPage.vue';
import AnalyticsPage from '../pages/AnalyticsPage.vue';
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
    redirect: { name: 'admin.overview' },
    meta: { requiresAuth: true },
    children: [
      { path: 'overview', name: 'admin.overview', component: OverviewPage, meta: { title: 'Overview' } },
      { path: 'analytics', name: 'admin.analytics', component: AnalyticsPage, meta: { title: 'Analytics' } },
      // emi requests
      { path: 'emi-requests', name: 'admin.emi.requests', component: () => import('@/pages/EmiRequestPage.vue'), meta: { title: 'EMI Requests' } },
      { path: 'emi-requests/:id', name: 'admin.emi.requests.detail', component: () => import('@/pages/EmiRequestDetailPage.vue'), meta: { title: 'EMI Request Detail' } },
      { path: 'emi-users', name: 'admin.emi.users', component: () => import('@/pages/EmiUsersListPage.vue'), meta: { title: 'EMI Users' } },
      { path: 'emi-applications', name: 'admin.emi.applications', component: () => import('@/pages/EmiUsersListPage.vue'), meta: { title: 'EMI Applications' } },
      { path: 'emi-banks', name: 'admin.emi.banks', component: () => import('@/pages/EmiBankListPage.vue'), meta: { title: 'EMI Banks' } },
      { path: 'emi-finance', name: 'admin.emi.finance', component: () => import('@/pages/EmiFinancePage.vue'), meta: { title: 'EMI Finance' } },
      // blogs
      { path: 'blogs', name: 'admin.blogs.list', component: () => import('@/pages/BlogPage.vue'), meta: { title: 'All Blogs' } },
      { path: 'blogs-create', name: 'admin.blogs.create', component: () => import('@/pages/BlogCreatePage.vue'), meta: { title: 'Create Blog' } },
      { path: 'blogs/:id', name: 'admin.blogs.detail', component: () => import('@/pages/BlogDetailPage.vue'), meta: { title: 'Blog Detail' } },
      { path: 'blogs/:id/edit', name: 'admin.blogs.edit', component: () => import('@/pages/BlogEditPage.vue'), meta: { title: 'Edit Blog' } },
      { path: 'blog-categories', name: 'admin.blogCategories.list', component: () => import('@/pages/BlogCategoryPage.vue'), meta: { title: 'Blog Categories' } },
      { path: 'blog-categories/:id', name: 'admin.blogCategories.detail', component: () => import('@/pages/BlogCategoryDetailPage.vue'), meta: { title: 'Blog Category Detail' } },
      // orders
      { path: 'orders', name: 'admin.orders.list', component: () => import('@/pages/OrderPage.vue'), meta: { title: 'All Orders' } },
      { path: 'orders/:id', name: 'admin.orders.detail', component: () => import('@/pages/OrderDetailPage.vue'), meta: { title: 'Order Detail' } },
      { path: 'pre-orders', name: 'admin.orders.pre', component: () => import('@/pages/PreOrdersListPage.vue'), meta: { title: 'Pre Orders' } },
      { path: 'cart-items', name: 'admin.orders.cart', component: () => import('@/pages/CartItemsListPage.vue'), meta: { title: 'Cart Items' } },
      { path: 'wish-pot', name: 'admin.orders.wish', component: () => import('@/pages/WishPotListPage.vue'), meta: { title: 'Wish Pot' } },
      // customers
      { path: 'customers', name: 'admin.customers.list', component: () => import('@/pages/CustomerPage.vue'), meta: { title: 'Customers' } },
      { path: 'customers/:id', name: 'admin.customers.detail', component: () => import('@/pages/CustomerDetailPage.vue'), meta: { title: 'Customer Detail' } },
      // catalog
      { path: 'product-list', name: 'admin.product.list', component: () => import('@/pages/ProductPage.vue'), meta: { title: 'Products' } },
      { path: 'product-reviews', name: 'admin.product.reviews', component: () => import('@/pages/ProductReviewsPage.vue'), meta: { title: 'Product Reviews' } },
      { path: 'product-create', name: 'admin.product.create', component: () => import('@/pages/ProductCreatePage.vue'), meta: { title: 'Create Product' } },
      { path: 'product/:id', name: 'admin.product.detail', component: () => import('@/pages/ProductDetailPage.vue'), meta: { title: 'Product Detail' } },
      { path: 'product/:id/edit', name: 'admin.product.edit', component: () => import('@/pages/ProductEditPage.vue'), meta: { title: 'Edit Product' } },
      { path: 'product-categories', name: 'admin.product.categories', component: () => import('@/pages/ProductCategoryPage.vue'), meta: { title: 'Categories' } },
      { path: 'product-categories/:id', name: 'admin.product.categories.detail', component: () => import('@/pages/ProductCategoryDetailPage.vue'), meta: { title: 'Category Detail' } },
      { path: 'product-brands', name: 'admin.product.brands', component: () => import('@/pages/ProductBrandPage.vue'), meta: { title: 'Brands' } },
      { path: 'product-brands/:id', name: 'admin.product.brands.detail', component: () => import('@/pages/ProductBrandDetailPage.vue'), meta: { title: 'Brand Detail' } },
      { path: 'product-attributes', name: 'admin.product.attributes', component: () => import('@/pages/ProductAttributePage.vue'), meta: { title: 'Attributes' } },
      { path: 'product-attributes/:id', name: 'admin.product.attributes.detail', component: () => import('@/pages/ProductAttributeDetailPage.vue'), meta: { title: 'Attribute Detail' } },
      
      { path: 'treks-regions', name: 'admin.treks.regions', component: () => import('@/pages/TreksRegionsPage.vue'), meta: { title: 'Regions' } },
      { path: 'treks-list', name: 'admin.treks.list', component: () => import('@/pages/TreksListPage.vue'), meta: { title: 'Tour / Treks' } },
      { path: 'treks-fixed-departure', name: 'admin.treks.fixed.departure', component: () => import('@/pages/TreksFixedDeparturePage.vue'), meta: { title: 'Fixed Departure' } },
      { path: 'treks-guide-profile', name: 'admin.treks.guide.profile', component: () => import('@/pages/TreksGuideProfilePage.vue'), meta: { title: 'Guide Profile' } },
      
      // marketing
      { path: 'marketing-banners', name: 'admin.banners.list', component: () => import('@/pages/BannerPage.vue'), meta: { title: 'Banners' } },
      { path: 'marketing-banners/:id', name: 'admin.banners.detail', component: () => import('@/pages/BannerDetailPage.vue'), meta: { title: 'Banner Detail' } },
      { path: 'marketing-campaign', alias: ['marketing-campaigns'], name: 'admin.campaigns.list', component: () => import('@/pages/CampaignPage.vue'), meta: { title: 'Campaigns' } },
      { path: 'marketing-campaign/:id', name: 'admin.campaigns.detail', component: () => import('@/pages/CampaignDetailPage.vue'), meta: { title: 'Campaign Detail' } },
      { path: 'marketing-coupons', name: 'admin.coupons.list', component: () => import('@/pages/CouponPage.vue'), meta: { title: 'Coupons' } },
      
      
      { path: 'pages', name: 'admin.pages', component: () => import('@/pages/WebPage.vue'), meta: { title: 'Pages' } },
      { path: 'pages/:id', name: 'admin.pages.detail', component: () => import('@/pages/PageDetailPage.vue'), meta: { title: 'Page Detail' } },
      { path: 'inquiries', name: 'admin.inquiries.list', component: () => import('@/pages/InquiriesListPage.vue'), meta: { title: 'Inquiries' } },
      { path: 'user-lists', name: 'admin.users.list', component: () => import('@/pages/UserListsPage.vue'), meta: { title: 'User Lists' } },
      // settings
      { path: 'settings-faqs', name: 'admin.faqs.list', component: () => import('@/pages/FaqsListPage.vue'), meta: { title: 'FAQs Management' } },
      { path: 'payment-methods', name: 'admin.paymentMethods.list', component: () => import('@/pages/PaymentMethodPage.vue'), meta: { title: 'Payment Methods' } },
      { path: 'payment-methods/:id', name: 'admin.paymentMethods.detail', component: () => import('@/pages/PaymentMethodDetailPage.vue'), meta: { title: 'Payment Method Detail' } },
      { path: 'admin-list', name: 'admin.list', component: () => import('@/pages/AdminPage.vue'), meta: { title: 'Admin Management' } },
      { path: 'role-management', name: 'admin.roles.manage', component: () => import('@/pages/RoleManagementPage.vue'), meta: { title: 'Role Management' } },
      { path: 'files', name: 'admin.files.list', component: () => import('@/pages/FilesPage.vue'), meta: { title: 'Files' } },
      { path: 'developer', name: 'admin.developer', component: () => import('@/pages/DeveloperPage.vue'), meta: { title: 'Developer' } },
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
    return { name: 'admin.overview' };
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
