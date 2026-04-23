<?php

declare(strict_types=1);

// TODO: API route loader for Foundation modules.

require __DIR__ . '/auth/route_auth.php';
require __DIR__ . '/admin/route_admins.php';
require __DIR__ . '/admin/route_blogs.php';
require __DIR__ . '/admin/route_banners.php';

require __DIR__ . '/admin/route_settings.php';
require __DIR__ . '/admin/route_customers.php';
require __DIR__ . '/admin/route_page.php';
require __DIR__ . '/admin/route_cart.php';
require __DIR__ . '/admin/route_faqs.php';

require __DIR__ . '/admin/route_products.php';
require __DIR__ . '/admin/route_payment_methods.php';
require __DIR__ . '/admin/route_file.php';
require __DIR__ . '/admin/route_developer.php';
require __DIR__ . '/admin/route_campaign.php';

require __DIR__ . '/admin/route_rbac.php';
require __DIR__ . '/admin/route_emi_requests.php';
require __DIR__ . '/admin/route_emi_banks.php';
require __DIR__ . '/admin/route_emi_users.php';
require __DIR__ . '/admin/route_orders.php';

