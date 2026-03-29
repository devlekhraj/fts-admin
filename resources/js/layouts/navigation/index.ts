import { ecommerceNav } from './ecommerce';
import type { NavGroup, ProjectType } from './types';

export type { NavGroup, ProjectType } from './types';

export const menuByProject: Record<ProjectType, NavGroup[]> = {
    ecommerce: ecommerceNav,
};
