<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$errors = [];
$success = '';

$old = [
    'nik' => '',
    'nama_pemohon' => ''
];


/*
|--------------------------------------------------------------------------
| KONFIGURASI UPLOAD
|--------------------------------------------------------------------------
*/

$uploadDir = __DIR__ . '/../uploads/images/';


/*
|--------------------------------------------------------------------------
| PASTIKAN FOLDER UPLOAD ADA
|--------------------------------------------------------------------------
*/

if (!is_dir($uploadDir)) {

    if (!mkdir($uploadDir, 0777, true)) {

        $errors[] =
            'Folder upload tidak dapat dibuat.';
    }
}


/*
|--------------------------------------------------------------------------
| PROSES FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nik = trim($_POST['nik'] ?? '');

    $nama_pemohon = trim(
        $_POST['nama_pemohon'] ?? ''
    );


    $old = [
        'nik' => $nik,
        'nama_pemohon' => $nama_pemohon
    ];


    /*
    |--------------------------------------------------------------------------
    | VALIDASI NIK
    |--------------------------------------------------------------------------
    */

    if (!validate_nik($nik)) {

        $errors[] =
            'NIK harus berupa angka dan tepat 16 digit.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI NAMA
    |--------------------------------------------------------------------------
    */

    if (!validate_nama($nama_pemohon)) {

        $errors[] =
            'Nama pemohon hanya boleh berisi huruf dan spasi, maksimal 30 karakter.';
    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL FILE
    |--------------------------------------------------------------------------
    */

    $dokumen = $_FILES['dokumen'] ?? null;

    $fotoDiri = $_FILES['foto_diri'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | VALIDASI DOKUMEN KTP
    |--------------------------------------------------------------------------
    */

    if (
        !$dokumen ||
        !isset($dokumen['error']) ||
        $dokumen['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $errors[] =
            'Foto KTP / Surat Kehilangan wajib diunggah.';

    } elseif ($dokumen['error'] !== UPLOAD_ERR_OK) {

        $errors[] =
            'Foto KTP / Surat Kehilangan gagal diunggah.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI FOTO DIRI
    |--------------------------------------------------------------------------
    */

    if (
        !$fotoDiri ||
        !isset($fotoDiri['error']) ||
        $fotoDiri['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $errors[] =
            'Foto diri wajib diunggah.';

    } elseif ($fotoDiri['error'] !== UPLOAD_ERR_OK) {

        $errors[] =
            'Foto diri gagal diunggah.';
    }


    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI FILE
    |--------------------------------------------------------------------------
    */

    $allowedExtensions = [
        'jpg',
        'jpeg',
        'png'
    ];

    $allowedMime = [
        'image/jpeg',
        'image/png'
    ];

    $maxSize = 5 * 1024 * 1024;


    /*
    |--------------------------------------------------------------------------
    | VALIDASI DOKUMEN KTP
    |--------------------------------------------------------------------------
    */

    $dokumenExtension = '';

    if (
        $dokumen &&
        isset($dokumen['error']) &&
        $dokumen['error'] === UPLOAD_ERR_OK
    ) {

        /*
        | Cek ukuran
        */

        if ($dokumen['size'] > $maxSize) {

            $errors[] =
                'Ukuran foto KTP maksimal 5 MB.';
        }


        /*
        | Cek ekstensi
        */

        $dokumenExtension = strtolower(
            pathinfo(
                $dokumen['name'],
                PATHINFO_EXTENSION
            )
        );


        if (
            !in_array(
                $dokumenExtension,
                $allowedExtensions,
                true
            )
        ) {

            $errors[] =
                'Foto KTP hanya boleh JPG, JPEG, atau PNG.';
        }


        /*
        | Cek MIME
        */

        $finfo = finfo_open(
            FILEINFO_MIME_TYPE
        );


        if ($finfo) {

            $dokumenMime = finfo_file(
                $finfo,
                $dokumen['tmp_name']
            );

            finfo_close($finfo);


            if (
                !in_array(
                    $dokumenMime,
                    $allowedMime,
                    true
                )
            ) {

                $errors[] =
                    'Format foto KTP tidak valid.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI FOTO DIRI
    |--------------------------------------------------------------------------
    */

    $fotoDiriExtension = '';

    if (
        $fotoDiri &&
        isset($fotoDiri['error']) &&
        $fotoDiri['error'] === UPLOAD_ERR_OK
    ) {

        /*
        | Cek ukuran
        */

        if ($fotoDiri['size'] > $maxSize) {

            $errors[] =
                'Ukuran foto diri maksimal 5 MB.';
        }


        /*
        | Cek ekstensi
        */

        $fotoDiriExtension = strtolower(
            pathinfo(
                $fotoDiri['name'],
                PATHINFO_EXTENSION
            )
        );


        if (
            !in_array(
                $fotoDiriExtension,
                $allowedExtensions,
                true
            )
        ) {

            $errors[] =
                'Foto diri hanya boleh JPG, JPEG, atau PNG.';
        }


        /*
        | Cek MIME
        */

        $finfo = finfo_open(
            FILEINFO_MIME_TYPE
        );


        if ($finfo) {

            $fotoDiriMime = finfo_file(
                $finfo,
                $fotoDiri['tmp_name']
            );

            finfo_close($finfo);


            if (
                !in_array(
                    $fotoDiriMime,
                    $allowedMime,
                    true
                )
            ) {

                $errors[] =
                    'Format foto diri tidak valid.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PROSES SIMPAN FILE
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        /*
        |--------------------------------------------------------------------------
        | ID USER ADMIN
        |--------------------------------------------------------------------------
        */

        $userId = (int) (
            $_SESSION['user_id'] ?? 0
        );


        if ($userId <= 0) {

            $errors[] =
                'User tidak valid. Silakan login kembali.';
        }


        /*
        |--------------------------------------------------------------------------
        | BUAT NAMA FILE UNIK
        |--------------------------------------------------------------------------
        */

        if (!$errors) {

            $waktu = date('YmdHis');

            $random = bin2hex(
                random_bytes(6)
            );


            /*
            |--------------------------------------------------------------------------
            | NAMA FILE KTP
            |--------------------------------------------------------------------------
            */

            $dokumenName =
                'ktp_' .
                $userId .
                '_' .
                $waktu .
                '_' .
                $random .
                '.' .
                $dokumenExtension;


            /*
            |--------------------------------------------------------------------------
            | NAMA FILE FOTO DIRI
            |--------------------------------------------------------------------------
            */

            $fotoDiriName =
                'foto_diri_' .
                $userId .
                '_' .
                $waktu .
                '_' .
                $random .
                '.' .
                $fotoDiriExtension;


            /*
            |--------------------------------------------------------------------------
            | PATH FISIK FILE
            |--------------------------------------------------------------------------
            */

            $dokumenPath =
                $uploadDir .
                $dokumenName;


            $fotoDiriPath =
                $uploadDir .
                $fotoDiriName;


            /*
            |--------------------------------------------------------------------------
            | UPLOAD FOTO KTP
            |--------------------------------------------------------------------------
            */

            $uploadDokumen =
                move_uploaded_file(
                    $dokumen['tmp_name'],
                    $dokumenPath
                );


            if (!$uploadDokumen) {

                $errors[] =
                    'Foto KTP gagal disimpan ke folder uploads/images/.';
            }


            /*
            |--------------------------------------------------------------------------
            | UPLOAD FOTO DIRI
            |--------------------------------------------------------------------------
            */

            if (!$errors) {

                $uploadFotoDiri =
                    move_uploaded_file(
                        $fotoDiri['tmp_name'],
                        $fotoDiriPath
                    );


                if (!$uploadFotoDiri) {

                    /*
                    | Hapus KTP jika foto diri gagal
                    */

                    if (
                        file_exists(
                            $dokumenPath
                        )
                    ) {

                        @unlink(
                            $dokumenPath
                        );
                    }


                    $errors[] =
                        'Foto diri gagal disimpan ke folder uploads/images/.';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATABASE
            |--------------------------------------------------------------------------
            */

            if (!$errors) {

                try {

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS AWAL
                    |--------------------------------------------------------------------------
                    */

                    $statusAwal = 'menunggu';


                    /*
                    |--------------------------------------------------------------------------
                    | INSERT DATABASE
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        INSERT INTO pengajuan_ktp
                        (
                            nik,
                            nama_pemohon,
                            gambar_path,
                            foto_diri_path,
                            status,
                            user_id
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        )
                    ");


                    $stmt->execute([
                        $nik,
                        $nama_pemohon,
                        $dokumenName,
                        $fotoDiriName,
                        $statusAwal,
                        $userId
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | BERHASIL
                    |--------------------------------------------------------------------------
                    */

                    $success =
                        'Pengajuan cetak KTP berhasil dikirim.';


                    /*
                    |--------------------------------------------------------------------------
                    | RESET FORM
                    |--------------------------------------------------------------------------
                    */

                    $old = [
                        'nik' => '',
                        'nama_pemohon' => ''
                    ];

                } catch (PDOException $e) {

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS FILE JIKA DATABASE GAGAL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        file_exists(
                            $dokumenPath
                        )
                    ) {

                        @unlink(
                            $dokumenPath
                        );
                    }


                    if (
                        file_exists(
                            $fotoDiriPath
                        )
                    ) {

                        @unlink(
                            $fotoDiriPath
                        );
                    }


                    $errors[] =
                        'Pengajuan gagal disimpan: ' .
                        $e->getMessage();
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

$pageTitle = 'Ajukan Cetak KTP';

require_once __DIR__ . '/../includes/header.php';

?>


<style>

/*
|--------------------------------------------------------------------------
| CARD UTAMA
|--------------------------------------------------------------------------
*/

.ktp-card {
    border: 1px solid #e8e6f2;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(32, 25, 70, 0.05);
    background: #ffffff;
}


/*
|--------------------------------------------------------------------------
| HEADER FORM
|--------------------------------------------------------------------------
*/

.ktp-icon {
    width: 46px;
    height: 46px;

    border-radius: 12px;

    background: linear-gradient(
        135deg,
        #312e81,
        #1d4ed8
    );

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;
}

.ktp-icon i {
    color: #ffffff;
    font-size: 19px;
}

.ktp-title {
    color: #17152f;
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}

.ktp-subtitle {
    color: #77748c;
    font-size: 12px;
}


/*
|--------------------------------------------------------------------------
| LABEL
|--------------------------------------------------------------------------
*/

.ktp-form .form-label {
    font-size: 13px;
    color: #3f3b56;
    margin-bottom: 6px;
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

.ktp-form .form-control {
    border-color: #ddd9eb;
    border-radius: 9px;
    font-size: 13px;
    min-height: 40px;
}

.ktp-form .form-control:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.10);
}

.ktp-form .form-text {
    font-size: 11px;
    color: #8b879d;
    margin-top: 5px;
}


/*
|--------------------------------------------------------------------------
| FILE INPUT
|--------------------------------------------------------------------------
*/

.ktp-form input[type="file"] {
    padding: 8px 10px;
}


/*
|--------------------------------------------------------------------------
| INFO FOTO
|--------------------------------------------------------------------------
*/

.photo-info {
    background: #f5f3ff;
    border: 1px solid #e5defc;
    border-radius: 10px;
    padding: 12px 14px;
    margin-top: 10px;
}

.photo-info-title {
    color: #5b21b6;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 6px;
}

.photo-info ul {
    margin: 0;
    padding-left: 18px;
}

.photo-info li {
    color: #6b6680;
    font-size: 11px;
    line-height: 1.6;
}


/*
|--------------------------------------------------------------------------
| PREVIEW
|--------------------------------------------------------------------------
*/

.preview-box {
    display: none;
    margin-top: 12px;
    padding: 12px;
    background: #faf9fe;
    border: 1px solid #e8e6f2;
    border-radius: 10px;
}

.preview-title {
    font-size: 12px;
    font-weight: 600;
    color: #4b4864;
    margin-bottom: 10px;
}

#previewFoto {
    max-width: 180px;
    max-height: 220px;
    object-fit: contain;
    border-radius: 9px;
    border: 1px solid #ddd9eb;
}


/*
|--------------------------------------------------------------------------
| BUTTON
|--------------------------------------------------------------------------
*/

.btn-kirim {
    border: none;
    border-radius: 9px;

    background: linear-gradient(
        135deg,
        #7c3aed,
        #5b21b6
    );

    color: #ffffff;

    font-size: 13px;
    font-weight: 600;

    padding: 10px 16px;

    transition: 0.2s ease;
}

.btn-kirim:hover {
    color: #ffffff;

    transform: translateY(-1px);

    box-shadow:
        0 5px 14px
        rgba(124, 58, 237, 0.20);
}


/*
|--------------------------------------------------------------------------
| ALERT
|--------------------------------------------------------------------------
*/

.ktp-alert {
    border-radius: 10px;
    font-size: 12px;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 576px) {

    .ktp-card {
        border-radius: 12px;
    }

    .ktp-card .card-body {
        padding: 20px !important;
    }

    .ktp-title {
        font-size: 15px;
    }

}

</style>


<div class="row justify-content-center">

    <div class="col-lg-7 col-md-9">

        <div class="card ktp-card">

            <div class="card-body p-4">


                <!-- =================================================
                     HEADER
                ================================================== -->

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div class="ktp-icon">

                        <i class="bi bi-file-earmark-plus-fill"></i>

                    </div>


                    <div>

                        <h6 class="ktp-title">
                            Form Pengajuan Cetak KTP
                        </h6>

                        <div class="ktp-subtitle">
                            Lengkapi data pengajuan dengan benar
                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SUCCESS
                ================================================== -->

                <?php if ($success): ?>

                    <div class="alert alert-success ktp-alert">

                        <i class="bi bi-check-circle-fill me-1"></i>

                        <?= e($success) ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if ($errors): ?>

                    <div class="alert alert-danger ktp-alert">

                        <div class="fw-semibold mb-2">

                            Pengajuan tidak dapat dikirim:

                        </div>


                        <ul class="mb-0 ps-3">

                            <?php foreach ($errors as $err): ?>

                                <li>
                                    <?= e($err) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     FORM
                ================================================== -->

                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="ktp-form"
                >


                    <!-- =================================================
                         NIK
                    ================================================== -->

                    <div class="mb-3">

                        <label
                            for="nik"
                            class="form-label fw-semibold"
                        >

                            NIK
                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            class="form-control"
                            maxlength="16"
                            minlength="16"
                            pattern="[0-9]{16}"
                            inputmode="numeric"
                            placeholder="Masukkan 16 digit NIK"
                            required
                            value="<?= e($old['nik']) ?>"
                        >


                        <div class="form-text">
                            NIK harus terdiri dari 16 digit angka.
                        </div>

                    </div>


                    <!-- =================================================
                         NAMA
                    ================================================== -->

                    <div class="mb-3">

                        <label
                            for="nama_pemohon"
                            class="form-label fw-semibold"
                        >

                            Nama Pemohon
                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="text"
                            name="nama_pemohon"
                            id="nama_pemohon"
                            class="form-control"
                            maxlength="30"
                            placeholder="Nama lengkap sesuai KTP"
                            required
                            value="<?= e($old['nama_pemohon']) ?>"
                        >

                    </div>


                    <!-- =================================================
                         FOTO KTP
                    ================================================== -->

                    <div class="mb-3">

                        <label
                            for="dokumen"
                            class="form-label fw-semibold"
                        >

                            <i class="bi bi-file-earmark-image me-1"></i>

                            Foto KTP / Surat Kehilangan

                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="file"
                            name="dokumen"
                            id="dokumen"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            required
                        >


                        <div class="form-text">
                            Format JPG/JPEG/PNG, maksimal 5 MB.
                        </div>

                    </div>


                    <!-- =================================================
                         FOTO DIRI
                    ================================================== -->

                    <div class="mb-4">

                        <label
                            for="foto_diri"
                            class="form-label fw-semibold"
                        >

                            <i class="bi bi-person-bounding-box me-1"></i>

                            Foto Diri Sambil Memegang KTP

                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="file"
                            name="foto_diri"
                            id="foto_diri"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            required
                        >


                        <div class="form-text">
                            Format JPG/JPEG/PNG, maksimal 5 MB.
                        </div>


                        <!-- KETENTUAN FOTO -->

                        <div class="photo-info">

                            <div class="photo-info-title">

                                <i class="bi bi-info-circle me-1"></i>

                                Ketentuan Foto Diri

                            </div>


                            <ul>

                                <li>
                                    Wajah pemohon harus terlihat jelas.
                                </li>

                                <li>
                                    Pemohon wajib memegang KTP asli.
                                </li>

                                <li>
                                    KTP harus terlihat jelas.
                                </li>

                                <li>
                                    Wajah dan KTP berada dalam satu foto.
                                </li>

                                <li>
                                    Foto tidak boleh buram atau terlalu gelap.
                                </li>

                            </ul>

                        </div>


                        <!-- PREVIEW FOTO -->

                        <div
                            id="previewContainer"
                            class="preview-box"
                        >

                            <div class="preview-title">

                                <i class="bi bi-eye me-1"></i>

                                Preview Foto Diri

                            </div>


                            <div class="text-center">

                                <img
                                    id="previewFoto"
                                    src=""
                                    alt="Preview Foto Diri"
                                >

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         BUTTON
                    ================================================== -->

                    <button
                        type="submit"
                        class="btn btn-kirim w-100"
                    >

                        <i class="bi bi-send-fill me-1"></i>

                        Kirim Pengajuan

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| PREVIEW FOTO DIRI
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const fotoInput =
            document.getElementById(
                'foto_diri'
            );

        const previewContainer =
            document.getElementById(
                'previewContainer'
            );

        const previewFoto =
            document.getElementById(
                'previewFoto'
            );


        if (
            fotoInput &&
            previewContainer &&
            previewFoto
        ) {

            fotoInput.addEventListener(
                'change',
                function () {

                    const file =
                        this.files[0];


                    if (!file) {

                        previewContainer.style.display =
                            'none';

                        previewFoto.src = '';

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CEK UKURAN
                    |--------------------------------------------------------------------------
                    */

                    if (
                        file.size >
                        5 * 1024 * 1024
                    ) {

                        alert(
                            'Ukuran foto diri maksimal 5 MB.'
                        );

                        this.value = '';

                        previewContainer.style.display =
                            'none';

                        previewFoto.src = '';

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CEK FORMAT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ![
                            'image/jpeg',
                            'image/png'
                        ].includes(
                            file.type
                        )
                    ) {

                        alert(
                            'Foto diri hanya boleh JPG, JPEG, atau PNG.'
                        );

                        this.value = '';

                        previewContainer.style.display =
                            'none';

                        previewFoto.src = '';

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TAMPILKAN PREVIEW
                    |--------------------------------------------------------------------------
                    */

                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {

                            previewFoto.src =
                                event.target.result;

                            previewContainer.style.display =
                                'block';

                        };


                    reader.readAsDataURL(
                        file
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | NIK HANYA ANGKA
        |--------------------------------------------------------------------------
        */

        const nik =
            document.getElementById(
                'nik'
            );


        if (nik) {

            nik.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value.replace(
                            /[^0-9]/g,
                            ''
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | NAMA HANYA HURUF
        |--------------------------------------------------------------------------
        */

        const nama =
            document.getElementById(
                'nama_pemohon'
            );


        if (nama) {

            nama.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value.replace(
                            /[^a-zA-ZÀ-ÿ\s]/g,
                            ''
                        );

                }
            );

        }

    }
);

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>