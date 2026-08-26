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
| PROSES FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nik = trim($_POST['nik'] ?? '');
    $nama_pemohon = trim($_POST['nama_pemohon'] ?? '');

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
    | VALIDASI DOKUMEN KTP
    |--------------------------------------------------------------------------
    */

    $dokumen = $_FILES['dokumen'] ?? null;

    $dokumenCheck = validate_file_upload($dokumen);

    if (!$dokumenCheck['valid']) {

        $errors[] =
            'Dokumen KTP / Surat Kehilangan: ' .
            $dokumenCheck['message'];
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI FOTO DIRI
    |--------------------------------------------------------------------------
    */

    $fotoDiri = $_FILES['foto_diri'] ?? null;


    if (
        !$fotoDiri ||
        !isset($fotoDiri['error']) ||
        $fotoDiri['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $errors[] =
            'Foto diri wajib diunggah.';

    } elseif ($fotoDiri['error'] !== UPLOAD_ERR_OK) {

        $errors[] =
            'Foto diri gagal diunggah. Silakan coba kembali.';

    } else {

        /*
        | Cek ukuran
        */

        if ($fotoDiri['size'] > MAX_FILE_SIZE) {

            $errors[] =
                'Ukuran foto diri maksimal 5 MB.';
        }


        /*
        | Cek ekstensi
        */

        $extension = strtolower(
            pathinfo(
                $fotoDiri['name'],
                PATHINFO_EXTENSION
            )
        );


        if (!in_array($extension, ALLOWED_EXT, true)) {

            $errors[] =
                'Foto diri hanya boleh berformat JPG, JPEG, atau PNG.';
        }


        /*
        | Cek MIME
        */

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo) {

            $mime = finfo_file(
                $finfo,
                $fotoDiri['tmp_name']
            );

            finfo_close($finfo);


            if (!in_array($mime, ALLOWED_MIME, true)) {

                $errors[] =
                    'Format foto diri tidak valid.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN DATA
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        /*
        | Upload dokumen KTP
        */

        $fileName = process_file_upload($dokumen);


        if (!$fileName) {

            $errors[] =
                'Gagal menyimpan dokumen KTP / Surat Kehilangan.';

        } else {

            /*
            | Nama file foto diri
            */

            $extension = strtolower(
                pathinfo(
                    $fotoDiri['name'],
                    PATHINFO_EXTENSION
                )
            );


            $fotoDiriName =
                'foto_diri_admin_' .
                $_SESSION['user_id'] .
                '_' .
                time() .
                '_' .
                bin2hex(random_bytes(4)) .
                '.' .
                $extension;


            /*
            | Pastikan folder upload tersedia
            */

            if (!is_dir(UPLOAD_DIR)) {

                @mkdir(
                    UPLOAD_DIR,
                    0777,
                    true
                );
            }


            $fotoDiriPath =
                UPLOAD_DIR . $fotoDiriName;


            /*
            | Upload foto diri
            */

            if (
                !move_uploaded_file(
                    $fotoDiri['tmp_name'],
                    $fotoDiriPath
                )
            ) {

                /*
                | Hapus dokumen jika foto diri gagal
                */

                @unlink(
                    UPLOAD_DIR . $fileName
                );


                $errors[] =
                    'Gagal menyimpan foto diri. Silakan coba kembali.';

            } else {

                try {

                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN KE DATABASE
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
                            'pending',
                            ?
                        )
                    ");


                    $stmt->execute([
                        $nik,
                        $nama_pemohon,
                        $fileName,
                        $fotoDiriName,
                        $_SESSION['user_id']
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | BERHASIL
                    |--------------------------------------------------------------------------
                    */

                    $success =
                        'Pengajuan cetak KTP berhasil dibuat.';


                    /*
                    | Reset form
                    */

                    $old = [
                        'nik' => '',
                        'nama_pemohon' => ''
                    ];


                } catch (PDOException $e) {

                    /*
                    | Hapus file jika database gagal
                    */

                    @unlink(
                        UPLOAD_DIR . $fileName
                    );

                    @unlink(
                        UPLOAD_DIR . $fotoDiriName
                    );


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


<!-- =========================================================
     CONTAINER
========================================================= -->

<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="card p-4 p-md-5 shadow-sm">


            <!-- =================================================
                 HEADER FORM
            ================================================== -->

            <div class="d-flex align-items-center gap-3 mb-4">

                <div
                    style="
                        width:52px;
                        height:52px;
                        border-radius:14px;
                        background:linear-gradient(
                            135deg,
                            #312e81,
                            #1d4ed8
                        );
                        display:flex;
                        align-items:center;
                        justify-content:center;
                    "
                >

                    <i
                        class="bi bi-file-earmark-plus-fill text-white fs-4"
                    ></i>

                </div>


                <div>

                    <h5 class="fw-bold mb-1">

                        Ajukan Cetak KTP

                    </h5>

                    <span class="text-muted">

                        Lengkapi data pengajuan dengan benar.

                    </span>

                </div>

            </div>


            <!-- =================================================
                 SUCCESS MESSAGE
            ================================================== -->

            <?php if ($success): ?>

                <div
                    class="alert alert-success alert-dismissible fade show"
                    role="alert"
                >

                    <i
                        class="bi bi-check-circle-fill me-2"
                    ></i>

                    <?= e($success) ?>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 ERROR MESSAGE
            ================================================== -->

            <?php if ($errors): ?>

                <div
                    class="alert alert-danger"
                    role="alert"
                >

                    <div class="fw-semibold mb-2">

                        <i
                            class="bi bi-exclamation-triangle-fill me-1"
                        ></i>

                        Pengajuan tidak dapat dikirim.

                    </div>


                    <ul class="mb-0 ps-4">

                        <?php foreach ($errors as $error): ?>

                            <li>

                                <?= e($error) ?>

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
                id="formPengajuan"
            >


                <!-- =================================================
                     NIK
                ================================================== -->

                <div class="mb-4">

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
                        value="<?= e($old['nik']) ?>"
                        required
                    >


                    <div class="form-text">

                        <i
                            class="bi bi-info-circle me-1"
                        ></i>

                        NIK harus terdiri dari 16 digit angka.

                    </div>

                </div>


                <!-- =================================================
                     NAMA
                ================================================== -->

                <div class="mb-4">

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
                        placeholder="Masukkan nama lengkap sesuai KTP"
                        value="<?= e($old['nama_pemohon']) ?>"
                        required
                    >


                    <div class="form-text">

                        Gunakan nama lengkap sesuai dengan KTP.

                    </div>

                </div>


                <!-- =================================================
                     DOKUMEN KTP
                ================================================== -->

                <div class="mb-4">

                    <label
                        for="dokumen"
                        class="form-label fw-semibold"
                    >

                        <i
                            class="bi bi-file-earmark-image me-1"
                        ></i>

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

                        <i
                            class="bi bi-info-circle me-1"
                        ></i>

                        Format JPG/JPEG/PNG dengan ukuran maksimal 5 MB.

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

                        <i
                            class="bi bi-person-bounding-box me-1"
                        ></i>

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


                    <!-- =================================================
                         KETERANGAN
                    ================================================== -->

                    <div
                        class="alert alert-info mt-3 mb-0"
                    >

                        <div class="d-flex gap-3">

                            <div>

                                <i
                                    class="bi bi-info-circle-fill fs-5"
                                ></i>

                            </div>


                            <div>

                                <div class="fw-bold mb-2">

                                    Ketentuan Foto Diri

                                </div>


                                <ul class="mb-0 ps-3">

                                    <li class="mb-1">

                                        Foto harus menampilkan
                                        wajah pemohon dengan jelas.

                                    </li>


                                    <li class="mb-1">

                                        Pemohon wajib
                                        <strong>
                                            memegang KTP asli
                                        </strong>
                                        saat mengambil foto.

                                    </li>


                                    <li class="mb-1">

                                        KTP harus terlihat jelas
                                        dan tidak tertutup tangan.

                                    </li>


                                    <li class="mb-1">

                                        Wajah dan KTP harus terlihat
                                        dalam satu foto.

                                    </li>


                                    <li class="mb-1">

                                        Foto tidak boleh buram,
                                        terlalu gelap, atau terlalu jauh.

                                    </li>


                                    <li>

                                        Format JPG/JPEG/PNG,
                                        maksimal 5 MB.

                                    </li>

                                </ul>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         PREVIEW
                    ================================================== -->

                    <div
                        id="previewContainer"
                        class="mt-3"
                        style="display:none;"
                    >

                        <div
                            class="card border-0 bg-light"
                        >

                            <div
                                class="card-body text-center"
                            >

                                <div
                                    class="fw-semibold mb-3"
                                >

                                    <i
                                        class="bi bi-eye me-1"
                                    ></i>

                                    Preview Foto Diri

                                </div>


                                <img
                                    id="previewFoto"
                                    src=""
                                    alt="Preview Foto Diri"
                                    class="img-fluid rounded shadow-sm"
                                    style="
                                        max-width:250px;
                                        max-height:300px;
                                        object-fit:contain;
                                    "
                                >


                                <div
                                    class="small text-success mt-3"
                                >

                                    <i
                                        class="bi bi-check-circle-fill me-1"
                                    ></i>

                                    Pastikan wajah dan KTP
                                    terlihat dengan jelas.

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div class="d-flex gap-2 mt-4">

                    <a
                        href="dashboard.php"
                        class="btn btn-outline-secondary flex-fill"
                    >

                        <i
                            class="bi bi-arrow-left me-1"
                        ></i>

                        Kembali

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary flex-fill"
                    >

                        <i
                            class="bi bi-send-fill me-1"
                        ></i>

                        Kirim Pengajuan

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ELEMENT
        |--------------------------------------------------------------------------
        */

        const fotoInput =
            document.getElementById('foto_diri');

        const previewContainer =
            document.getElementById('previewContainer');

        const previewFoto =
            document.getElementById('previewFoto');


        /*
        |--------------------------------------------------------------------------
        | PREVIEW FOTO DIRI
        |--------------------------------------------------------------------------
        */

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


                    /*
                    | Jika tidak ada file
                    */

                    if (!file) {

                        previewContainer.style.display =
                            'none';

                        previewFoto.src = '';

                        return;
                    }


                    /*
                    | Validasi ukuran
                    */

                    const maxSize =
                        5 * 1024 * 1024;


                    if (file.size > maxSize) {

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
                    | Validasi tipe
                    */

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png'
                    ];


                    if (
                        !allowedTypes.includes(
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
                    | Baca gambar
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


                    reader.readAsDataURL(file);

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI NIK
        |--------------------------------------------------------------------------
        */

        const nikInput =
            document.getElementById('nik');


        if (nikInput) {

            nikInput.addEventListener(
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
        | VALIDASI NAMA
        |--------------------------------------------------------------------------
        */

        const namaInput =
            document.getElementById('nama_pemohon');


        if (namaInput) {

            namaInput.addEventListener(
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