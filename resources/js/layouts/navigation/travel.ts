import type { NavGroup } from './types';

export const travelNav: NavGroup[] = [
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
