<?php

// ============================================================
// FUNCTIONS.PHP
// SIAK-KTP | Sistem Pengajuan Cetak KTP
// ============================================================

/*
|--------------------------------------------------------------------------
| HELPER ESCAPE HTML
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {

    function e($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}


/*
|--------------------------------------------------------------------------
| BASE PATH
|--------------------------------------------------------------------------
*/

if (!function_exists('base_path')) {

    function base_path()
    {
        return '/aplikasi-cetak-ktp/';
    }
}


/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (!function_exists('is_logged_in')) {

    function is_logged_in()
    {
        return isset($_SESSION['user_id'])
            && !empty($_SESSION['user_id']);
    }
}


/*
|--------------------------------------------------------------------------
| CEK ADMIN
|--------------------------------------------------------------------------
*/

if (!function_exists('is_admin')) {

    function is_admin()
    {
        return is_logged_in()
            && isset($_SESSION['role'])
            && $_SESSION['role'] === 'admin';
    }
}


/*
|--------------------------------------------------------------------------
| CEK USER / PEMOHON
|--------------------------------------------------------------------------
*/

if (!function_exists('is_user')) {

    function is_user()
    {
        return is_logged_in()
            && isset($_SESSION['role'])
            && $_SESSION['role'] === 'user';
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE LOGIN
|--------------------------------------------------------------------------
*/

if (!function_exists('require_login')) {

    function require_login()
    {
        if (!is_logged_in()) {

            header(
                'Location: ' . base_path() . 'index.php'
            );

            exit;
        }
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN
|--------------------------------------------------------------------------
*/

if (!function_exists('require_admin')) {

    function require_admin()
    {
        if (!is_logged_in()) {

            header(
                'Location: ' . base_path() . 'index.php'
            );

            exit;
        }

        if (!is_admin()) {

            header(
                'Location: ' . base_path() . 'user/dashboard.php'
            );

            exit;
        }
    }
}


/*
|--------------------------------------------------------------------------
| REQUIRE USER
|--------------------------------------------------------------------------
*/

if (!function_exists('require_user')) {

    function require_user()
    {
        if (!is_logged_in()) {

            header(
                'Location: ' . base_path() . 'index.php'
            );

            exit;
        }

        if (!is_user()) {

            header(
                'Location: ' . base_path() . 'admin/dashboard.php'
            );

            exit;
        }
    }
}


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {

    function redirect($url)
    {
        header(
            'Location: ' . $url
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| VALIDASI NIK
|--------------------------------------------------------------------------
*/

if (!function_exists('validate_nik')) {

    function validate_nik($nik)
    {
        return preg_match(
            '/^[0-9]{16}$/',
            $nik
        ) === 1;
    }
}


/*
|--------------------------------------------------------------------------
| VALIDASI NAMA
|--------------------------------------------------------------------------
*/

if (!function_exists('validate_nama')) {

    function validate_nama($nama)
    {
        $nama = trim($nama);

        if ($nama === '') {
            return false;
        }

        if (mb_strlen($nama) > 30) {
            return false;
        }

        return preg_match(
            '/^[a-zA-ZÀ-ÿ\s]+$/u',
            $nama
        ) === 1;
    }
}


/*
|--------------------------------------------------------------------------
| VALIDASI FILE UPLOAD
|--------------------------------------------------------------------------
*/

if (!function_exists('validate_file_upload')) {

    function validate_file_upload($file)
    {
        if (!$file || !isset($file['error'])) {

            return [
                'valid' => false,
                'message' => 'File tidak ditemukan.'
            ];
        }


        if ($file['error'] === UPLOAD_ERR_NO_FILE) {

            return [
                'valid' => false,
                'message' => 'File wajib diunggah.'
            ];
        }


        if ($file['error'] !== UPLOAD_ERR_OK) {

            return [
                'valid' => false,
                'message' => 'File gagal diunggah.'
            ];
        }


        if (
            defined('MAX_FILE_SIZE')
            && $file['size'] > MAX_FILE_SIZE
        ) {

            return [
                'valid' => false,
                'message' => 'Ukuran file maksimal 5 MB.'
            ];
        }


        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );


        if (
            defined('ALLOWED_EXT')
            && !in_array(
                $extension,
                ALLOWED_EXT,
                true
            )
        ) {

            return [
                'valid' => false,
                'message' => 'Format file tidak diperbolehkan.'
            ];
        }


        if (
            !empty($file['tmp_name'])
            && function_exists('finfo_open')
        ) {

            $finfo = finfo_open(
                FILEINFO_MIME_TYPE
            );


            if ($finfo) {

                $mime = finfo_file(
                    $finfo,
                    $file['tmp_name']
                );

                finfo_close($finfo);


                if (
                    defined('ALLOWED_MIME')
                    && !in_array(
                        $mime,
                        ALLOWED_MIME,
                        true
                    )
                ) {

                    return [
                        'valid' => false,
                        'message' => 'Tipe file tidak valid.'
                    ];
                }
            }
        }


        return [
            'valid' => true,
            'message' => 'File valid.'
        ];
    }
}


/*
|--------------------------------------------------------------------------
| PROSES UPLOAD FILE
|--------------------------------------------------------------------------
*/

if (!function_exists('process_file_upload')) {

    function process_file_upload($file)
    {
        if (
            !$file
            || !isset($file['error'])
            || $file['error'] !== UPLOAD_ERR_OK
        ) {

            return false;
        }


        if (!defined('UPLOAD_DIR')) {
            return false;
        }


        if (!is_dir(UPLOAD_DIR)) {

            if (!mkdir(
                UPLOAD_DIR,
                0777,
                true
            )) {

                return false;
            }
        }


        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );


        $allowedExtensions = defined('ALLOWED_EXT')
            ? ALLOWED_EXT
            : [
                'jpg',
                'jpeg',
                'png',
                'pdf'
            ];


        if (
            !in_array(
                $extension,
                $allowedExtensions,
                true
            )
        ) {

            return false;
        }


        $fileName =
            'dokumen_' .
            date('YmdHis') .
            '_' .
            bin2hex(
                random_bytes(6)
            ) .
            '.' .
            $extension;


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
}


/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/

if (!function_exists('status_badge')) {

    function status_badge($status)
    {
        $status = strtolower(
            trim(
                (string) $status
            )
        );


        switch ($status) {

            case 'pending':

            case 'menunggu':

                $class =
                    'status-menunggu';

                $label =
                    'Menunggu';

                break;


            case 'diproses':

            case 'process':

            case 'processing':

                $class =
                    'status-diproses';

                $label =
                    'Diproses';

                break;


            case 'selesai':

            case 'completed':

            case 'success':

                $class =
                    'status-selesai';

                $label =
                    'Selesai';

                break;


            case 'ditolak':

            case 'rejected':

            case 'tolak':

                $class =
                    'status-ditolak';

                $label =
                    'Ditolak';

                break;


            default:

                $class =
                    'status-menunggu';

                $label =
                    $status !== ''
                    ? ucfirst($status)
                    : 'Menunggu';

                break;
        }


        return
            '<span class="status-badge ' .
            e($class) .
            '">' .
            e($label) .
            '</span>';
    }
}


/*
|--------------------------------------------------------------------------
| FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

if (!function_exists('format_tanggal')) {

    function format_tanggal($tanggal)
    {
        if (
            empty($tanggal)
            || $tanggal === '0000-00-00'
            || $tanggal === '0000-00-00 00:00:00'
        ) {

            return '-';
        }


        $timestamp = strtotime(
            $tanggal
        );


        if ($timestamp === false) {
            return '-';
        }


        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];


        return date(
            'd',
            $timestamp
        ) .
        ' ' .
        $bulan[
            (int) date(
                'm',
                $timestamp
            )
        ] .
        ' ' .
        date(
            'Y',
            $timestamp
        );
    }
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

if (!function_exists('set_flash')) {

    function set_flash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL FLASH MESSAGE
|--------------------------------------------------------------------------
*/

if (!function_exists('get_flash')) {

    function get_flash()
    {
        if (
            !isset(
                $_SESSION['flash']
            )
        ) {

            return null;
        }


        $flash =
            $_SESSION['flash'];


        unset(
            $_SESSION['flash']
        );


        return $flash;
    }
}


/*
|--------------------------------------------------------------------------
| NAMA USER LOGIN
|--------------------------------------------------------------------------
*/

if (!function_exists('current_user_name')) {

    function current_user_name()
    {
        return $_SESSION['nama_lengkap']
            ?? $_SESSION['nama']
            ?? $_SESSION['username']
            ?? 'User';
    }
}


/*
|--------------------------------------------------------------------------
| ID USER LOGIN
|--------------------------------------------------------------------------
*/

if (!function_exists('current_user_id')) {

    function current_user_id()
    {
        return $_SESSION['user_id']
            ?? null;
    }
}


/*
|--------------------------------------------------------------------------
| ROLE USER LOGIN
|--------------------------------------------------------------------------
*/

if (!function_exists('current_user_role')) {

    function current_user_role()
    {
        return $_SESSION['role']
            ?? null;
    }
}


/*
|--------------------------------------------------------------------------
| HAPUS FILE UPLOAD
|--------------------------------------------------------------------------
*/

if (!function_exists('delete_uploaded_file')) {

    function delete_uploaded_file($fileName)
    {
        if (
            empty($fileName)
            || !defined('UPLOAD_DIR')
        ) {

            return false;
        }


        $filePath =
            UPLOAD_DIR .
            basename($fileName);


        if (
            is_file($filePath)
        ) {

            return unlink(
                $filePath
            );
        }


        return false;
    }
}


/*
|--------------------------------------------------------------------------
| AKHIR FUNCTIONS.PHP
|--------------------------------------------------------------------------
*/