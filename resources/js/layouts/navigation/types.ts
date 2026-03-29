export type NavItem = {
    title: string;
    routeName: string;
    icon: string;
};

export type NavGroup = {
    icon: string;
    group: string;
    items?: NavItem[];
    routeName?: string;
};

export type ProjectType = 'ecommerce';
