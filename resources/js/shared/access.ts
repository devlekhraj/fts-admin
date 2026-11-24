// Which permission each admin page needs. This only decides what to SHOW (menu items, pages, buttons):
// the server checks every request again, so a hidden screen is never the only protection.
// The permission names match app/Domains/Rbac/Support/PermissionCatalog.php.

import type { AdminProfile } from '@/stores/auth.store';

/** Any one of these lets a person browse and upload in the media library (the shared image picker). */
export const MEDIA_PERMISSIONS = [
  'add-product', 'edit-product', 'add-brand', 'edit-brand', 'add-product-category', 'edit-product-category',
  'add-blog', 'edit-blog', 'add-blog-category', 'edit-blog-category', 'add-banner', 'edit-banner',
  'manage-banner-images', 'pages-add', 'pages-edit', 'page-sections-manage', 'access-campaigns',
  'access-payment-methods', 'access-settings',
];

/** null means every signed-in admin. A route name that matches nothing here is for Administrators only. First match wins. */
const ROUTE_RULES: Array<[RegExp, string[] | null]> = [
  [/^admin\.(overview|analytics|forbidden|account)$/, null],

  [/^admin\.exchange\.pricing$/, ['exchange-pricing']],
  [/^admin\.exchange\./, ['access-exchange']],
  [/^admin\.emi\./, ['access-emi']],
  [/^admin\.orders\./, ['access-orders']],
  [/^admin\.customers\./, ['users-view']],
  [/^admin\.users\.list$/, ['users-view']],

  [/^admin\.product\.create$/, ['add-product']],
  [/^admin\.product\.edit$/, ['edit-product']],
  [/^admin\.product\.imports$/, ['access-data-import']],
  [/^admin\.product\.categories/, ['view-product-categories']],
  [/^admin\.product\.brands/, ['view-brands']],
  [/^admin\.product\./, ['view-products']],

  [/^admin\.blogs\.create$/, ['add-blog']],
  [/^admin\.blogs\.edit$/, ['edit-blog']],
  [/^admin\.blogs\./, ['view-blogs']],
  [/^admin\.blogCategories\./, ['view-blog-categories']],

  [/^admin\.banners\./, ['view-banners']],
  [/^admin\.campaigns\./, ['access-campaigns']],
  [/^admin\.pages/, ['pages-view']],
  [/^admin\.faqs\./, ['pages-view']],

  [/^admin\.paymentMethods\./, ['access-payment-methods']],
  [/^admin\.settings/, ['access-settings']],
  [/^admin\.developer/, ['access-developer']],
  [/^admin\.files\./, MEDIA_PERMISSIONS],

  [/^admin\.list$/, ['admins-view']],
  [/^admin\.roles\./, ['roles-view']],
];

export function permissionsForRoute(name: string | symbol | null | undefined): string[] | null | 'super' {
  const routeName = typeof name === 'string' ? name : '';
  for (const [pattern, permissions] of ROUTE_RULES) {
    if (pattern.test(routeName)) return permissions;
  }
  return 'super';
}

export function holdsAny(admin: AdminProfile | null | undefined, permissions: string[]): boolean {
  if (!admin) return false;
  if (admin.is_super) return true;
  const mine = Array.isArray(admin.permissions) ? (admin.permissions as string[]) : [];
  return permissions.some((permission) => mine.includes(permission));
}

export function canOpenRoute(admin: AdminProfile | null | undefined, name: string | symbol | null | undefined): boolean {
  if (!admin) return false;
  const needed = permissionsForRoute(name);
  if (needed === null) return true;
  if (needed === 'super') return Boolean(admin.is_super);
  return holdsAny(admin, needed);
}
