<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Support;

/**
 * Every permission a role can hold, grouped the way the Role Management page shows them.
 * The names are the ones the roles already store (kept so existing roles keep working); the exchange, developer and
 * customer entries were added for the parts of the admin that came later.
 */
final class PermissionCatalog
{
    /** @return array<string, array{label: string, permissions: array<string, string>}> */
    public static function groups(): array
    {
        return [
            'orders' => ['label' => 'Orders', 'permissions' => [
                'access-orders' => 'Orders, carts and wish lists: view and update',
            ]],
            'emi' => ['label' => 'EMI', 'permissions' => [
                'access-emi' => 'EMI requests, applications, banks and users: view and decide',
            ]],
            'exchange' => ['label' => 'Exchange', 'permissions' => [
                'access-exchange' => 'Exchange requests: view, add, approve, reject and complete',
                'exchange-pricing' => 'Exchange prices and deduction rules',
            ]],
            'customers' => ['label' => 'Customers', 'permissions' => [
                'users-view' => 'View customers',
            ]],
            'products' => ['label' => 'Products', 'permissions' => [
                'view-products' => 'View products and attributes',
                'add-product' => 'Add and duplicate products',
                'edit-product' => 'Edit products, prices, variants, images and attributes',
                'delete-product' => 'Delete products',
                'access-data-import' => 'Import products from a file',
            ]],
            'categories' => ['label' => 'Product categories', 'permissions' => [
                'view-product-categories' => 'View categories',
                'add-product-category' => 'Add categories',
                'edit-product-category' => 'Edit categories',
                'delete-product-category' => 'Delete categories',
            ]],
            'brands' => ['label' => 'Brands', 'permissions' => [
                'view-brands' => 'View brands',
                'add-brand' => 'Add brands',
                'edit-brand' => 'Edit brands',
                'delete-brand' => 'Delete brands',
            ]],
            'blogs' => ['label' => 'Blogs', 'permissions' => [
                'view-blogs' => 'View blogs',
                'add-blog' => 'Add blogs',
                'edit-blog' => 'Edit blogs',
                'delete-blog' => 'Delete blogs',
                'view-blog-categories' => 'View blog categories',
                'add-blog-category' => 'Add blog categories',
                'edit-blog-category' => 'Edit blog categories',
                'delete-blog-category' => 'Delete blog categories',
            ]],
            'marketing' => ['label' => 'Marketing', 'permissions' => [
                'access-campaigns' => 'Campaigns: view and manage',
                'view-banners' => 'View banners',
                'add-banner' => 'Add banners',
                'edit-banner' => 'Edit banners',
                'delete-banner' => 'Delete banners',
                'manage-banner-images' => 'Manage banner images',
            ]],
            'pages' => ['label' => 'Pages and FAQs', 'permissions' => [
                'pages-view' => 'View pages and FAQs',
                'pages-add' => 'Add pages',
                'pages-edit' => 'Edit pages and FAQs',
                'pages-delete' => 'Delete pages and FAQs',
                'page-sections-manage' => 'Manage page sections',
            ]],
            'configuration' => ['label' => 'Configuration', 'permissions' => [
                'access-payment-methods' => 'Payment methods',
                'access-settings' => 'Site settings and logos',
                'access-developer' => 'Developer: API keys',
            ]],
            'team' => ['label' => 'Team and roles', 'permissions' => [
                'admins-view' => 'View admin users',
                'admins-add' => 'Add admin users',
                'admins-edit' => 'Edit admin users (name, email, password, role)',
                'admins-delete' => 'Delete admin users',
                'roles-view' => 'View roles',
                'roles-add' => 'Add roles',
                'roles-edit' => 'Edit roles',
                'roles-delete' => 'Delete roles',
            ]],
            'other' => ['label' => 'Kept from the old admin (no screen uses these yet)', 'permissions' => [
                'view-menus' => 'View menus',
                'add-menu' => 'Add menus',
                'edit-menu' => 'Edit menus',
                'delete-menu' => 'Delete menus',
                'discounts-view' => 'View discounts',
                'discounts-add' => 'Add discounts',
                'discounts-edit' => 'Edit discounts',
                'discounts-delete' => 'Delete discounts',
                'contacts-view' => 'View contact messages',
                'contacts-reply' => 'Reply to contact messages',
            ]],
        ];
    }

    /** @return string[] */
    public static function all(): array
    {
        $all = [];
        foreach (self::groups() as $group) {
            foreach (array_keys($group['permissions']) as $key) {
                $all[] = $key;
            }
        }

        return $all;
    }

    public static function exists(string $permission): bool
    {
        return in_array($permission, self::all(), true);
    }

    /**
     * Any one of these lets a person upload to and browse the media library: it is the shared picker behind
     * the product, brand, blog, banner, page, campaign and settings forms.
     *
     * @return string[]
     */
    public static function mediaPermissions(): array
    {
        return [
            'add-product', 'edit-product', 'add-brand', 'edit-brand', 'add-product-category', 'edit-product-category',
            'add-blog', 'edit-blog', 'add-blog-category', 'edit-blog-category', 'add-banner', 'edit-banner',
            'manage-banner-images', 'pages-add', 'pages-edit', 'page-sections-manage', 'access-campaigns',
            'access-payment-methods', 'access-settings',
        ];
    }
}
