<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Support;

/**
 * Which permission each admin API request needs. One list for the whole admin, so nothing depends on a rule being
 * remembered when a route is added: a request that matches no rule is refused (and a test lists every admin route
 * to make sure each one has a rule).
 *
 * `*` in a path stands for one segment (an id), `**` for the rest of the path. A person needs ANY ONE of the listed
 * permissions. Administrators (the role marked as super) are never checked.
 */
final class AccessMap
{
    /** @var array<int, array{methods: string[], regex: string, any: string[]|null}>|null */
    private static ?array $compiled = null;

    /**
     * @return array{any: string[]}|array{public: true}|null  null when no rule matches
     */
    public static function resolve(string $method, string $path): ?array
    {
        $method = strtoupper($method);
        $path = trim($path, '/');

        foreach (self::compiled() as $rule) {
            if (! in_array($method, $rule['methods'], true) || preg_match($rule['regex'], $path) !== 1) {
                continue;
            }

            return $rule['any'] === null ? ['public' => true] : ['any' => $rule['any']];
        }

        return null;
    }

    /** @return array<int, array{methods: string[], regex: string, any: string[]|null}> */
    private static function compiled(): array
    {
        if (self::$compiled !== null) {
            return self::$compiled;
        }

        $compiled = [];
        foreach (self::rules() as [$methods, $path, $any]) {
            $regex = '#^' . str_replace(['\*\*', '\*'], ['.+', '[^/]+'], preg_quote($path, '#')) . '$#';
            $compiled[] = ['methods' => $methods === '*' ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] : explode('|', $methods), 'regex' => $regex, 'any' => $any];
        }

        return self::$compiled = $compiled;
    }

    /**
     * [methods, path (after /api/admin/), permissions]. First match wins, so the specific rule goes before the general one.
     * Permissions null means any signed-in admin.
     *
     * @return array<int, array{0: string, 1: string, 2: string[]|null}>
     */
    private static function rules(): array
    {
        $media = PermissionCatalog::mediaPermissions();

        return [
            // ---- everyone who is signed in
            ['GET', 'dashboard/**', null],   // the dashboard itself only returns what the role may see
            ['PUT', 'account/*', null],      // your own name and password

            // ---- team and roles
            ['GET', 'admin-list', ['admins-view']],
            ['POST', 'admin-create', ['admins-add']],
            ['PUT|POST', 'admin-users/*/basic-info', ['admins-edit']],
            ['PUT|POST', 'admin-users/*/password', ['admins-edit']],
            ['PUT|POST', 'admin-users/*/role', ['admins-edit']],
            ['PUT|POST', 'admin-users/*/email', ['admins-edit']],
            ['POST', 'admin-users/*/email/verification-code', ['admins-edit']],
            ['DELETE', 'admin-users/*/delete', ['admins-delete']],
            // the role list also fills the role drop-down of the admin forms
            ['GET', 'rbac/roles', ['roles-view', 'admins-view', 'admins-add', 'admins-edit']],
            ['GET', 'rbac/permissions', ['roles-view', 'roles-add', 'roles-edit']],
            ['POST', 'rbac/roles', ['roles-add']],
            ['PUT', 'rbac/roles/*', ['roles-edit']],
            ['DELETE', 'rbac/roles/*', ['roles-delete']],

            // ---- orders, carts, wish lists
            ['*', 'orders/**', ['access-orders']],
            ['*', 'carts', ['access-orders']],
            ['*', 'carts/**', ['access-orders']],
            ['*', 'wishlists', ['access-orders']],
            ['*', 'wishlists/**', ['access-orders']],

            // ---- EMI
            ['*', 'emi-requests', ['access-emi']],
            ['*', 'emi-requests/**', ['access-emi']],
            ['*', 'emi-applications/**', ['access-emi']],
            ['*', 'emi-banks', ['access-emi']],
            ['*', 'emi-banks/**', ['access-emi']],
            ['*', 'emi-users', ['access-emi']],

            // ---- exchange
            ['*', 'exchange-valuations', ['exchange-pricing']],
            ['*', 'exchange-valuations/**', ['exchange-pricing']],
            ['*', 'exchange-damage-rules', ['exchange-pricing']],
            ['*', 'exchange-damage-rules/**', ['exchange-pricing']],
            ['*', 'exchange-pricing-audit', ['exchange-pricing']],
            ['*', 'exchange-requests', ['access-exchange']],
            ['*', 'exchange-requests/**', ['access-exchange']],

            // ---- customers (the exchange form also searches customers to link one)
            ['GET', 'customer-list', ['users-view', 'access-exchange']],
            ['GET', 'customers/*', ['users-view']],

            // ---- products
            ['POST', 'products/import/preview', ['access-data-import']],
            ['POST', 'products/import', ['access-data-import']],
            ['DELETE', 'products/bulk-delete', ['delete-product']],
            ['DELETE', 'products/*/delete', ['delete-product']],
            ['DELETE', 'products/**', ['edit-product']],      // images, gifts
            ['POST', 'products/*/duplicate', ['add-product']],
            ['POST', 'products', ['add-product']],
            ['POST', 'products/**', ['edit-product']],        // variants, gifts
            ['PUT', 'products/**', ['edit-product']],
            ['PUT', 'product-variants/**', ['edit-product']],
            ['DELETE', 'product-variants/**', ['edit-product']],
            ['GET', 'products', ['view-products']],
            ['GET', 'products/**', ['view-products']],
            ['GET', 'product-list', ['view-products', 'access-campaigns']],
            ['GET', 'attribute-list', ['view-products', 'add-product', 'edit-product']],
            ['GET', 'attributes/**', ['view-products', 'add-product', 'edit-product']],
            ['PATCH', 'attributes/**', ['edit-product']],

            // ---- categories
            ['GET', 'product-categorie-list', ['view-product-categories', 'view-products', 'add-product', 'edit-product']],
            ['GET', 'product-categorie-lookups', ['view-product-categories', 'view-products', 'add-product', 'edit-product']],
            ['DELETE', 'product-categories/bulk-delete', ['delete-product-category']],
            ['GET', 'product-categories', ['view-product-categories']],
            ['GET', 'product-categories/**', ['view-product-categories']],
            ['POST', 'product-categories', ['add-product-category']],
            ['POST', 'product-categories/**', ['edit-product-category']],   // banners
            ['PUT', 'product-categories/**', ['edit-product-category']],
            ['DELETE', 'product-categories/*/banner/*', ['edit-product-category']],
            ['DELETE', 'product-categories/*/images/*', ['edit-product-category']],
            ['DELETE', 'product-categories/*', ['delete-product-category']],

            // ---- brands
            ['GET', 'product-brand-list', ['view-brands', 'view-products', 'add-product', 'edit-product']],
            ['DELETE', 'brands/bulk-delete', ['delete-brand']],
            ['GET', 'brands', ['view-brands']],
            ['GET', 'brands/**', ['view-brands']],
            ['POST', 'brands', ['add-brand']],
            ['POST', 'brands/**', ['edit-brand']],                           // banners
            ['PUT', 'brands/**', ['edit-brand']],
            ['DELETE', 'brands/*/banner/*', ['edit-brand']],
            ['DELETE', 'brands/*/images/*', ['edit-brand']],
            ['DELETE', 'brands/*', ['delete-brand']],

            // ---- blogs
            ['GET', 'blog-categories', ['view-blog-categories']],
            ['GET', 'blog-categories/*', ['view-blog-categories']],
            ['POST', 'blog-categories', ['add-blog-category']],
            ['PUT', 'blog-categories/**', ['edit-blog-category']],
            ['DELETE', 'blog-categories/*/images/*', ['edit-blog-category']],
            ['DELETE', 'blog-categories/*', ['delete-blog-category']],
            ['GET', 'blogs', ['view-blogs']],
            ['GET', 'blogs/*', ['view-blogs']],
            ['POST', 'blogs', ['add-blog']],
            ['PUT', 'blogs/**', ['edit-blog']],
            ['DELETE', 'blogs/*/images/*', ['edit-blog']],
            ['DELETE', 'blogs/*', ['delete-blog']],

            // ---- marketing
            ['*', 'campaigns', ['access-campaigns']],
            ['*', 'campaigns/**', ['access-campaigns']],
            ['*', 'campaign-products/**', ['access-campaigns']],
            ['GET', 'banners', ['view-banners']],
            ['GET', 'banners/*', ['view-banners']],
            ['POST', 'banners', ['add-banner']],
            ['PUT', 'banners/*', ['edit-banner']],
            ['DELETE', 'banners/*', ['delete-banner']],

            // ---- pages and FAQs
            ['GET', 'pages', ['pages-view']],
            ['GET', 'pages/*', ['pages-view']],
            ['POST', 'pages', ['pages-add']],
            ['PUT', 'pages/*', ['pages-edit', 'page-sections-manage']],
            ['DELETE', 'pages/*', ['pages-delete']],
            ['GET', 'faqs', ['pages-view']],
            ['POST', 'faqs/save', ['pages-edit']],
            ['DELETE', 'faqs/*', ['pages-delete']],

            // ---- configuration
            ['*', 'payment-methods', ['access-payment-methods']],
            ['*', 'payment-methods/**', ['access-payment-methods']],
            ['*', 'settings', ['access-settings']],
            ['*', 'settings/**', ['access-settings']],
            ['GET', 'logo-images', ['access-settings']],
            ['*', 'developer/**', ['access-developer']],

            // ---- media library
            ['*', 'file-upload', $media],
            ['*', 'file-assign', $media],
            ['*', 'file-list', $media],
            ['*', 'file-list-with-usages', $media],
            ['*', 'file-usages', $media],
            ['*', 'file-usage/**', $media],
        ];
    }
}
