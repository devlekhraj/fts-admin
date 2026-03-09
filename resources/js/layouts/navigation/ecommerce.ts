import type { NavGroup } from './types';

export const ecommerceNav: NavGroup[] = [
    {
        group: 'Welcome',
        links: [{ title: 'Dashboard', to: { name: 'admin.dashboard' }, icon: 'mdi-view-dashboard-outline' }],
    },
    {
        group: 'Sales',
        links: [
            { title: 'Orders', to: { name: 'admin.orders.list' }, icon: 'mdi-cart-outline' },
            { title: 'Customers', to: { name: 'admin.customers.list' }, icon: 'mdi-account-group-outline' },
            { title: 'EMI Requests', to: { name: 'admin.emi.requests' }, icon: 'mdi-cash-multiple' },
        ],
    },
    {
        group: 'Catalog',
        links: [
            { title: 'Products', to: { name: 'admin.product.list' }, icon: 'mdi-package-variant-closed' },
            { title: 'Categories', to: { name: 'admin.product.categories' }, icon: 'mdi-shape-outline' },
            { title: 'Attributes', to: { name: 'admin.product.attributes' }, icon: 'mdi-tune-variant' },
            { title: 'Brands', to: { name: 'admin.product.brands' }, icon: 'mdi-tag-outline' },
            { title: 'Product Reviews', to: { name: 'admin.product.reviews' }, icon: 'mdi-star-box-outline' },
        ],
    },
    {
        group: 'Blogs / Articles',
        links: [
            { title: 'Blogs', to: { name: 'admin.blogs.list' }, icon: 'mdi-post-outline' },
            { title: 'Blog Categories', to: { name: 'admin.blogCategories.list' }, icon: 'mdi-folder-multiple-outline' },
        ],
    },
    {
        group: 'Marketing',
        links: [
            { title: 'Campaigns', to: { name: 'admin.campaigns.list' }, icon: 'mdi-bullhorn-outline' },
            { title: 'Banners', to: { name: 'admin.banners.list' }, icon: 'mdi-image-outline' },
        ],
    },
    {
        group: 'Settings',
        links: [
            { title: 'Admins', to: { name: 'admin.list' }, icon: 'mdi-account-group-outline' },
            { title: 'Roles', to: { name: 'admin.roles.manage' }, icon: 'mdi-shield-account-outline' },
            { title: 'Payment Methods', to: { name: 'admin.paymentMethods.list' }, icon: 'mdi-credit-card-outline' },
            { title: 'Files', to: { name: 'admin.files.list' }, icon: 'mdi-image-multiple-outline' },
            { title: 'Developer', to: { name: 'admin.developer' }, icon: 'mdi-code-tags' },
            { title: 'Settings', to: { name: 'admin.settings' }, icon: 'mdi-cog-outline' },
        ],
    },
];
