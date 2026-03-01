import { ecommerceNav } from './ecommerce';
import { newsPortalNav } from './newsportal';
import { travelNav } from './travel';
import type { NavGroup, ProjectType } from './types';

export type { NavGroup, ProjectType } from './types';

export const menuByProject: Record<ProjectType, NavGroup[]> = {
    travel: travelNav,
    ecommerce: ecommerceNav,
    newsportal: newsPortalNav,
};
