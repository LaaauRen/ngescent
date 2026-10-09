<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/../bootstrap/app.php';

// Set path database ke /tmp
$dbPath = '/tmp/database.sqlite';
$isNewDb = !file_exists($dbPath);

if ($isNewDb) {
    touch($dbPath);
}

putenv("DB_DATABASE={$dbPath}");
$_ENV['DB_DATABASE'] = $dbPath;
$_SERVER['DB_DATABASE'] = $dbPath;

// Jalankan migration jika database baru dibuat
if ($isNewDb) {
    try {
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true,
        ]);
    } catch (\Throwable $e) {
        // Ignore jika sudah ter-migrate
    }
}

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);