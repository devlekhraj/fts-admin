export type NavLink = {
    title: string;
    to: { name: string };
    icon: string;
};

export type NavGroup = {
    group: string;
    links: NavLink[];
};

export type ProjectType = 'travel' | 'ecommerce' | 'newsportal';
