<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Paksa semua request masuk dianggap HTTPS di Vercel
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

define('LARAVEL_START', microtime(true));

// Siapkan folder /tmp yang memiliki akses tulis di Vercel
$tmpDir = '/tmp/storage';
$directories = [
    'app',
    'framework/cache',
    'framework/sessions',
    'framework/views',
    'logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($tmpDir . '/' . $dir)) {
        mkdir($tmpDir . '/' . $dir, 0755, true);
    }
}

// Paksa Laravel menggunakan /tmp untuk semua file cache sistem
$_ENV['APP_SERVICES_CACHE'] = $tmpDir . '/bootstrap/cache/services.php';
$_ENV['APP_PACKAGES_CACHE'] = $tmpDir . '/bootstrap/cache/packages.php';
$_ENV['APP_CONFIG_CACHE'] = $tmpDir . '/bootstrap/cache/config.php';
$_ENV['APP_ROUTES_CACHE'] = $tmpDir . '/bootstrap/cache/routes.php';
$_ENV['APP_EVENTS_CACHE'] = $tmpDir . '/bootstrap/cache/events.php';
$_ENV['VIEW_COMPILED_PATH'] = $tmpDir . '/framework/views';

$_SERVER['APP_SERVICES_CACHE'] = $_ENV['APP_SERVICES_CACHE'];
$_SERVER['APP_PACKAGES_CACHE'] = $_ENV['APP_PACKAGES_CACHE'];
$_SERVER['APP_CONFIG_CACHE'] = $_ENV['APP_CONFIG_CACHE'];
$_SERVER['APP_ROUTES_CACHE'] = $_ENV['APP_ROUTES_CACHE'];
$_SERVER['APP_EVENTS_CACHE'] = $_ENV['APP_EVENTS_CACHE'];

// Muat Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Aplikasi Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);