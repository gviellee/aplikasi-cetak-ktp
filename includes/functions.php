<?php
function validate_nik($nik) {
    return (bool) preg_match('/^[0-9]{16}$/', $nik);
}

function validate_nama($nama) {
    $nama = trim($nama);
    $len = mb_strlen($nama);
    if ($len < 1 || $len > 30) return false;
    return (bool) preg_match('/^[A-Za-zÀ-ÿ\s]+$/u', $nama);
}

function validate_file_upload($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => false, 'message' => 'File wajib diupload.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'Terjadi kesalahan saat upload file.'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'message' => 'Ukuran file maksimal 5 MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        return ['valid' => false, 'message' => 'Format file harus JPG, JPEG, atau PNG.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_MIME, true)) {
        return ['valid' => false, 'message' => 'File bukan gambar JPG/PNG yang valid.'];
    }

    return ['valid' => true, 'message' => ''];
}

function process_file_upload($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newName = 'ktp_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) return false;
    return move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $newName) ? $newName : false;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_path() . 'login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: ' . base_path() . 'user/dashboard.php');
        exit;
    }
}

function require_user() {
    require_login();
    if (is_admin()) {
        header('Location: ' . base_path() . 'admin/dashboard.php');
        exit;
    }
}

function base_path() {
    return (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/user/') !== false) ? '../' : '';
}

function e($string) {
    return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
}

function status_badge($status) {
    $map = [
        'pending' => ['status-menunggu', 'bi-hourglass-split', 'Menunggu'],
        'proses' => ['status-diproses', 'bi-arrow-repeat', 'Diproses'],
        'selesai' => ['status-selesai', 'bi-check-circle-fill', 'Selesai'],
        'ditolak' => ['status-ditolak', 'bi-x-circle-fill', 'Ditolak'],
    ];
    [$class, $icon, $label] = $map[strtolower((string) $status)] ?? ['status-menunggu', 'bi-question-circle', 'Tidak diketahui'];
    return '<span class="status-badge ' . $class . '"><i class="bi ' . $icon . ' me-1"></i>' . e($label) . '</span>';
}

function status_options() {
    return [
        'pending' => 'Menunggu',
        'proses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];
}
