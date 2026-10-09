<?php

// Pastikan folder & file SQLite ada di /tmp/database.sqlite
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath);
}

// Ubah config database secara langsung
putenv("DB_DATABASE={$dbPath}");
$_ENV['DB_DATABASE'] = $dbPath;
$_SERVER['DB_DATABASE'] = $dbPath;

// Load Laravel bootstrap seperti biasa
require __DIR__ . '/../public/index.php';