<?php

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


function base_path()
{
    return '/aplikasi-cetak-ktp/';
}


function is_logged_in()
{
    return isset($_SESSION['user_id']);
}


function is_admin()
{
    return is_logged_in()
        && ($_SESSION['role'] ?? '') === 'admin';
}


function is_pemohon()
{
    return is_logged_in()
        && ($_SESSION['role'] ?? '') === 'pemohon';
}


function require_login()
{
    if (!is_logged_in()) {
        header(
            'Location: ' .
            base_path() .
            'index.php'
        );
        exit;
    }
}


function require_admin()
{
    require_login();

    if (!is_admin()) {
        header(
            'Location: ' .
            base_path() .
            'index.php'
        );
        exit;
    }
}


function require_user()
{
    require_login();

    if (!is_pemohon()) {
        header(
            'Location: ' .
            base_path() .
            'index.php'
        );
        exit;
    }
}


function validate_nik($nik)
{
    return preg_match('/^[0-9]{16}$/', $nik);
}


function validate_nama($nama)
{
    return preg_match(
        '/^[a-zA-Z\s]{1,100}$/',
        $nama
    );
}


function status_options()
{
    return [
        'pending' => 'Menunggu',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak'
    ];
}


function status_badge($status)
{
    $labels = status_options();

    $label = $labels[$status] ?? $status;

    $class = match ($status) {

        'pending' =>
            'status-badge status-menunggu',

        'diproses' =>
            'status-badge status-diproses',

        'selesai' =>
            'status-badge status-selesai',

        'ditolak' =>
            'status-badge status-ditolak',

        default =>
            'status-badge'
    };

    return '<span class="' .
        $class .
        '">' .
        e($label) .
        '</span>';
}


function validate_file_upload($file)
{
    if (
        !$file ||
        !isset($file['error'])
    ) {
        return [
            'valid' => false,
            'message' => 'File belum dipilih.'
        ];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'valid' => false,
            'message' => 'File gagal diupload.'
        ];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'valid' => false,
            'message' => 'Ukuran file maksimal 5 MB.'
        ];
    }

    $ext = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    if (!in_array($ext, ALLOWED_EXT, true)) {
        return [
            'valid' => false,
            'message' => 'Format file harus JPG, JPEG, atau PNG.'
        ];
    }

    $mime = mime_content_type(
        $file['tmp_name']
    );

    if (!in_array($mime, ALLOWED_MIME, true)) {
        return [
            'valid' => false,
            'message' => 'Jenis file tidak valid.'
        ];
    }

    return [
        'valid' => true,
        'message' => ''
    ];
}


function process_file_upload($file)
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(
            UPLOAD_DIR,
            0777,
            true
        );
    }

    $ext = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    $fileName =
        uniqid('ktp_', true) .
        '.' .
        $ext;

    $destination =
        UPLOAD_DIR .
        $fileName;

    if (
        move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {
        return $fileName;
    }

    return false;
}