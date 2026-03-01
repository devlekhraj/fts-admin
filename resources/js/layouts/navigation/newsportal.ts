import type { NavGroup } from './types';

export const newsPortalNav: NavGroup[] = [
    {
        group: 'Welcome',
        links: [{ title: 'Dashboard', to: { name: 'admin.dashboard' }, icon: 'mdi-view-dashboard-outline' }],
    },
    {
        group: 'Editorial',
        links: [
            { title: 'All Blogs', to: { name: 'admin.blogs.list' }, icon: 'mdi-note-multiple-outline' },
            { title: 'Categories', to: { name: 'admin.blogCategories.list' }, icon: 'mdi-folder-outline' },
            { title: 'Pages', to: { name: 'admin.pages.list' }, icon: 'mdi-file-document-outline' },
        ],
    },
    {
        group: 'Audience',
        links: [
            { title: 'Inquiries', to: { name: 'admin.inquiries.list' }, icon: 'mdi-message-text-outline' },
            { title: 'User Lists', to: { name: 'admin.users.list' }, icon: 'mdi-account-multiple-outline' },
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
