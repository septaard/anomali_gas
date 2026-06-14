<?php

// 1. Paksa semua jalur penyimpanan sementara Laravel ke /tmp (satu-satunya folder yang bisa ditulis di Vercel)
$tmp = '/tmp';
putenv("VIEW_COMPILED_PATH={$tmp}");
putenv("APP_CONFIG_CACHE={$tmp}/config.php");
putenv("APP_EVENTS_CACHE={$tmp}/events.php");
putenv("APP_PACKAGES_CACHE={$tmp}/packages.php");
putenv("APP_ROUTES_CACHE={$tmp}/routes.php");
putenv("APP_SERVICES_CACHE={$tmp}/services.php");

// 2. Timpa pengaturan driver agar sangat ramah terhadap arsitektur Serverless
putenv("CACHE_STORE=array");     // Hindari penggunaan file untuk cache
putenv("SESSION_DRIVER=cookie"); // Gunakan cookie agar tidak perlu tabel database untuk sesi
putenv("LOG_CHANNEL=stderr");    // Tembakkan log langsung ke terminal Vercel

// 3. Lanjutkan ke pintu masuk utama aplikasi Laravel
require __DIR__ . '/../public/index.php';