<?php

// Tangkap semua error agar tidak melempar BindingResolutionException view
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('LARAVEL_START', microtime(true));

// Pastikan direktori /tmp untuk storage aman
$tmpStorage = '/tmp/storage';
foreach (['app', 'framework/cache', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
    if (!is_dir($tmpStorage . '/' . $dir)) {
        mkdir($tmpStorage . '/' . $dir, 0755, true);
    }
}

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