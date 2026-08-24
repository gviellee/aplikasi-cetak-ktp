<?php

// ===============================
// SESSION
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// INFORMASI APLIKASI
// ===============================
define('APP_NAME', 'SIAK-KTP | Sistem Pengajuan Cetak KTP');

// ===============================
// DATABASE
// ===============================
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'ktp_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// ===============================
// UPLOAD
// ===============================
define('UPLOAD_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

define('ALLOWED_EXT', [
    'jpg',
    'jpeg',
    'png'
]);

define('ALLOWED_MIME', [
    'image/jpeg',
    'image/png'
]);

// ===============================
// KONEKSI DATABASE
// ===============================
try {

    $dsn = 'mysql:host=' . DB_HOST .
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
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    die(
        '<div style="
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #f8f9fa;
        ">
            <h2 style="color:#dc3545;">
                Koneksi Database Gagal
            </h2>

            <p>
                Aplikasi tidak dapat terhubung ke database.
            </p>

            <p>
                <strong>Database:</strong> ' .
                htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') .
            '</p>

            <p>
                <strong>Host:</strong> ' .
                htmlspecialchars(DB_HOST, ENT_QUOTES, 'UTF-8') .
            '</p>

            <p>
                <strong>Port:</strong> ' .
                htmlspecialchars(DB_PORT, ENT_QUOTES, 'UTF-8') .
            '</p>

            <hr>

            <small>' .
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') .
            '</small>
        </div>'
    );
}