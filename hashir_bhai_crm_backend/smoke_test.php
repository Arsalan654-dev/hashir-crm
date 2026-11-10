<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$paths = [
    '/',
    '/dashboard-sales',
    '/layouts-horizontal',
    '/apps-calendar',
    '/apps-chat',
    '/apps-file-manager',
    '/apps-todo',
    '/contacts-list',
    '/contacts-grid',
    '/contacts-profile',
    '/ecommerce-products',
    '/ecommerce-product-detail',
    '/ecommerce-shops',
    '/ecommerce-add-product',
    '/ecommerce-orders',
    '/ecommerce-customers',
    '/ecommerce-cart',
    '/ecommerce-checkout',
    '/invoices-list',
    '/invoices-detail',
    '/email-inbox',
    '/email-read',
    '/auth-login',
    '/auth-recoverpw',
    '/auth-reset-password?email=admin%40example.com&token=invalid',
    '/auth-lock-screen',
    '/auth-two-step-verification',
    '/auth-confirm-mail',
    '/auth-email-verification',
    '/auth-logout',
    '/users',
    '/charts-apex',
    '/charts-chartjs',
    '/charts-tui',
    '/form-advanced',
    '/form-editors',
    '/form-elements',
    '/form-layouts',
    '/form-mask',
    '/form-uploads',
    '/form-validation',
    '/form-wizard',
    '/icons-boxicons',
    '/icons-evaicons',
    '/icons-fontawesome',
    '/icons-materialdesign',
    '/maps-google',
    '/maps-leaflet',
    '/maps-vector',
    '/pages-404',
    '/pages-500',
    '/pages-comingsoon',
    '/pages-faqs',
    '/pages-maintenance',
    '/pages-pricing',
    '/pages-starter',
    '/pages-timeline',
    '/tables-advanced',
    '/tables-basic',
    '/ui-alerts',
    '/ui-buttons',
    '/ui-cards',
    '/ui-carousel',
    '/ui-colors',
    '/ui-dropdowns',
    '/ui-general',
    '/ui-grid',
    '/ui-images',
    '/ui-lightbox',
    '/ui-modals',
    '/ui-notifications',
    '/ui-offcanvas',
    '/ui-progressbars',
    '/ui-rangeslider',
    '/ui-rating',
    '/ui-sweet-alert',
    '/ui-tabs-accordions',
    '/ui-typography',
    '/ui-video',
    '/api/notifications',
];

$ok = 0;
$fail = 0;

foreach ($paths as $path) {
    $request = Request::create($path, 'GET');
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 400) {
            $ok++;
            echo "OK  {$status} {$path}\n";
        } else {
            $fail++;
            echo "FAIL {$status} {$path}\n";
        }
        $kernel->terminate($request, $response);
    } catch (Throwable $e) {
        $fail++;
        echo 'ERR  '.$path.' :: '.$e->getMessage()."\n";
    }
}

echo "\nOK: {$ok} / ".($ok + $fail)."\n";
exit($fail > 0 ? 1 : 0);
