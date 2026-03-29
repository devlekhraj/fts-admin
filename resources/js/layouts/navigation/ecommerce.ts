import type { NavGroup } from './types';

export const ecommerceNav: NavGroup[] = [
    {
        group: 'Dashboard',
        icon: 'mdi-hand-wave-outline',
        items: [
            { title: 'Overview', routeName: 'admin.overview', icon: 'mdi-view-dashboard-outline' },
            { title: 'Analytics', routeName: 'admin.analytics', icon: 'mdi-chart-box-outline' },
        ],
    },
    {
        group: 'Sales',
        icon: 'mdi-chart-line',
        items: [
            { title: 'Orders', routeName: 'admin.orders.list', icon: 'mdi-cart-outline' },
            { title: 'Customers', routeName: 'admin.customers.list', icon: 'mdi-account-group-outline' },
        ],
    },
    {
        group: 'EMI',
        icon: 'mdi-cash-multiple',
        items: [
            { title: 'EMI Requests', routeName: 'admin.emi.requests', icon: 'mdi-cash-multiple' },
            { title: 'EMI Applications', routeName: 'admin.emi.applications', icon: 'mdi-file-document-edit-outline' },
            { title: 'EMI Banks', routeName: 'admin.emi.banks', icon: 'mdi-bank-outline' },
            { title: 'EMI Finance', routeName: 'admin.emi.finance', icon: 'mdi-finance' },
        ],
    },
    {
        group: 'Catalog',
        icon: 'mdi-archive-outline',
        items: [
            { title: 'Products', routeName: 'admin.product.list', icon: 'mdi-package-variant-closed' },
            { title: 'Categories', routeName: 'admin.product.categories', icon: 'mdi-shape-outline' },
            { title: 'Attributes', routeName: 'admin.product.attributes', icon: 'mdi-tune-variant' },
            { title: 'Brands', routeName: 'admin.product.brands', icon: 'mdi-tag-outline' },
            { title: 'Product Reviews', routeName: 'admin.product.reviews', icon: 'mdi-star-box-outline' },
        ],
    },
    {
        group: 'Blogs / Articles',
        icon: 'mdi-text-box-multiple-outline',
        items: [
            { title: 'Blogs', routeName: 'admin.blogs.list', icon: 'mdi-post-outline' },
            { title: 'Blog Categories', routeName: 'admin.blogCategories.list', icon: 'mdi-folder-multiple-outline' },
        ],
    },
    {
        group: 'Marketing',
        icon: 'mdi-bullhorn-variant-outline',
        items: [
            { title: 'Campaigns', routeName: 'admin.campaigns.list', icon: 'mdi-bullhorn-outline' },
            { title: 'Banners', routeName: 'admin.banners.list', icon: 'mdi-image-outline' },
            { title: 'Coupons', routeName: 'admin.coupons.list', icon: 'mdi-ticket-percent-outline' },
        ],
    },
    {
        group: 'Configurations',
        icon: 'mdi-cog-outline',
        items: [
            { title: 'Payment Methods', routeName: 'admin.paymentMethods.list', icon: 'mdi-credit-card-outline' },
            { title: 'Files', routeName: 'admin.files.list', icon: 'mdi-image-multiple-outline' },
            { title: 'General Settings', routeName: 'admin.settings', icon: 'mdi-cog-outline' },
        ],
    },
    {
        group: 'System Admin',
        icon: 'mdi-cog',
        items: [
            { title: 'Admin Users', routeName: 'admin.list', icon: 'mdi-account-group-outline' },
            { title: 'Roles & Permissions', routeName: 'admin.roles.manage', icon: 'mdi-shield-account-outline' },
        ],
    },
    {
        group: 'Developer',
        icon: 'mdi-code-tags',
        routeName: 'admin.developer',
    },
    {
        group: 'Pages',
        icon: 'mdi-cog-outline',
        routeName: 'admin.pages',
    },
];
