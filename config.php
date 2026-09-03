<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'SIAK-KTP | Sistem Pengajuan Cetak KTP');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'ktp_management');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_PATH', __DIR__);

define(
    'UPLOAD_DIR',
    BASE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR
);

define('MAX_FILE_SIZE', 5 * 1024 * 1024);

define(
    'ALLOWED_EXT',
    ['jpg', 'jpeg', 'png', 'pdf']
);

define(
    'ALLOWED_MIME',
    [
        'image/jpeg',
        'image/png',
        'application/pdf'
    ]
);

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

try {

    $dsn =
        'mysql:host=' . DB_HOST .
        ';port=' . DB_PORT .
        ';dbname=' . DB_NAME .
        ';charset=utf8mb4';

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die(
        'Koneksi database gagal: ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
    );
}