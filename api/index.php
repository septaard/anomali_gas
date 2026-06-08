<?php

// 1. Vercel bersifat Read-Only. Kita paksa Laravel melakukan kompilasi Blade View di folder /tmp
$viewPath = '/tmp/storage/framework/views';
if (!is_dir($viewPath)) {
    mkdir($viewPath, 0755, true);
}
putenv("VIEW_COMPILED_PATH={$viewPath}");

// 2. Otomatisasi pembuatan file database SQLite di folder /tmp jika belum ada
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath);
}

// 3. Panggil bootstrap utama asli milik Laravel
require __DIR__ . '/../public/index.php';