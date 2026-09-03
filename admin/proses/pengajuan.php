<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$errors = [];
$success = '';

/*
|--------------------------------------------------------------------------
| AMBIL ID PENGAJUAN
|--------------------------------------------------------------------------
*/

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ../pemohon.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| CEK KOLOM ALASAN PENOLAKAN
|--------------------------------------------------------------------------
*/

$hasAlasanPenolakan = false;

try {

    $stmt = $pdo->query("
        SHOW COLUMNS
        FROM pengajuan_ktp
        LIKE 'alasan_penolakan'
    ");

    $hasAlasanPenolakan =
        $stmt->fetch() !== false;

} catch (PDOException $e) {

    $hasAlasanPenolakan = false;
}


/*
|--------------------------------------------------------------------------
| FUNGSI AMBIL DATA PENGAJUAN
|--------------------------------------------------------------------------
*/

function getPengajuan($pdo, $id)
{
    $stmt = $pdo->prepare("
        SELECT
            p.*,
            u.username
        FROM pengajuan_ktp p

        LEFT JOIN users u
            ON u.id = p.user_id

        WHERE p.id = ?

        LIMIT 1
    ");

    $stmt->execute([$id]);

    return $stmt->fetch();
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA
|--------------------------------------------------------------------------
*/

try {

    $pengajuan = getPengajuan(
        $pdo,
        $id
    );

} catch (PDOException $e) {

    die(
        'Gagal mengambil data pengajuan: ' .
        htmlspecialchars(
            $e->getMessage()
        )
    );
}


/*
|--------------------------------------------------------------------------
| CEK DATA
|--------------------------------------------------------------------------
*/

if (!$pengajuan) {

    die(
        'Pengajuan dengan ID ' .
        $id .
        ' tidak ditemukan.'
    );
}


/*
|--------------------------------------------------------------------------
| PROSES FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = trim(
        $_POST['status'] ?? ''
    );

    $alasanPenolakan = trim(
        $_POST['alasan_penolakan'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | STATUS YANG DIPERBOLEHKAN
    |--------------------------------------------------------------------------
    */

    $allowedStatus = [
        'pending',
        'diproses',
        'selesai',
        'ditolak'
    ];


    /*
    |--------------------------------------------------------------------------
    | VALIDASI STATUS
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $status,
            $allowedStatus,
            true
        )
    ) {

        $errors[] =
            'Silakan pilih status pengajuan.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI ALASAN DITOLAK
    |--------------------------------------------------------------------------
    */

    if (
        $status === 'ditolak' &&
        $alasanPenolakan === ''
    ) {

        $errors[] =
            'Alasan penolakan wajib diisi.';
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN KE DATABASE
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        try {

            /*
            |--------------------------------------------------------------------------
            | JIKA KOLOM ALASAN TERSEDIA
            |--------------------------------------------------------------------------
            */

            if ($hasAlasanPenolakan) {

                /*
                | Jika status bukan ditolak,
                | hapus alasan sebelumnya.
                */

                if ($status !== 'ditolak') {

                    $alasanPenolakan = null;
                }


                $stmt = $pdo->prepare("
                    UPDATE pengajuan_ktp

                    SET
                        status = ?,
                        alasan_penolakan = ?

                    WHERE id = ?
                ");


                $stmt->execute([
                    $status,
                    $alasanPenolakan,
                    $id
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | JIKA KOLOM BELUM ADA
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    UPDATE pengajuan_ktp

                    SET status = ?

                    WHERE id = ?
                ");


                $stmt->execute([
                    $status,
                    $id
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | PESAN BERHASIL
            |--------------------------------------------------------------------------
            */

            $success =
                'Status pengajuan berhasil diperbarui.';


            /*
            |--------------------------------------------------------------------------
            | AMBIL DATA TERBARU
            |--------------------------------------------------------------------------
            */

            $pengajuan =
                getPengajuan(
                    $pdo,
                    $id
                );

        } catch (PDOException $e) {

            $errors[] =
                'Gagal menyimpan perubahan: ' .
                $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| STATUS SEKARANG
|--------------------------------------------------------------------------
*/

$currentStatus = strtolower(
    trim(
        $pengajuan['status'] ?? ''
    )
);


/*
|--------------------------------------------------------------------------
| ALASAN SEKARANG
|--------------------------------------------------------------------------
*/

$currentAlasan = '';

if ($hasAlasanPenolakan) {

    $currentAlasan =
        $pengajuan[
            'alasan_penolakan'
        ] ?? '';
}


/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
    'Proses Pengajuan KTP';


require_once
    __DIR__ . '/../../includes/header.php';

?>


<!-- =========================================================
     HEADER
========================================================= -->

<div
    class="
        d-flex
        justify-content-between
        align-items-center
        mb-4
    "
>

    <div>

        <h5 class="fw-bold mb-1">

            <i
                class="
                    bi
                    bi-pencil-square
                    me-2
                "
                style="color:#f59e0b;"
            ></i>

            Proses Pengajuan KTP

        </h5>

        <p
            class="
                text-muted
                small
                mb-0
            "
        >

            Kelola status pengajuan pemohon.

        </p>

    </div>


    <a
        href="../pemohon.php"
        class="
            btn
            btn-outline-secondary
        "
    >

        <i
            class="
                bi
                bi-arrow-left
                me-1
            "
        ></i>

        Kembali

    </a>

</div>


<!-- =========================================================
     PESAN SUKSES
========================================================= -->

<?php if ($success): ?>

    <div
        class="
            alert
            alert-success
            alert-dismissible
            fade
            show
        "
    >

        <i
            class="
                bi
                bi-check-circle-fill
                me-2
            "
        ></i>

        <?= e($success) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

<?php endif; ?>


<!-- =========================================================
     PESAN ERROR
========================================================= -->

<?php if ($errors): ?>

    <div class="alert alert-danger">

        <div class="fw-bold mb-2">

            <i
                class="
                    bi
                    bi-exclamation-triangle-fill
                    me-1
                "
            ></i>

            Terjadi Kesalahan

        </div>

        <ul class="mb-0">

            <?php foreach (
                $errors
                as $error
            ): ?>

                <li>
                    <?= e($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>


<!-- =========================================================
     DETAIL PENGAJUAN
========================================================= -->

<div class="card shadow-sm mb-4">

    <div class="card-body">

        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-4
            "
        >

            <h6 class="fw-bold mb-0">

                <i
                    class="
                        bi
                        bi-person-vcard-fill
                        me-1
                    "
                    style="color:#7c3aed;"
                ></i>

                Detail Pengajuan

            </h6>


            <span
                class="
                    badge
                    bg-secondary
                "
            >

                ID #<?= (int) $pengajuan['id'] ?>

            </span>

        </div>


        <div class="row g-4">


            <!-- USERNAME -->

            <div class="col-md-6">

                <label
                    class="
                        form-label
                        text-muted
                        small
                    "
                >

                    Username

                </label>

                <div class="fw-semibold">

                    <?= e(
                        $pengajuan['username']
                        ?? '-'
                    ) ?>

                </div>

            </div>


            <!-- NIK -->

            <div class="col-md-6">

                <label
                    class="
                        form-label
                        text-muted
                        small
                    "
                >

                    NIK

                </label>

                <div class="fw-semibold">

                    <?= e(
                        $pengajuan['nik']
                        ?? '-'
                    ) ?>

                </div>

            </div>


            <!-- NAMA -->

            <div class="col-md-6">

                <label
                    class="
                        form-label
                        text-muted
                        small
                    "
                >

                    Nama Pemohon

                </label>

                <div class="fw-semibold">

                    <?= e(
                        $pengajuan['nama_pemohon']
                        ?? '-'
                    ) ?>

                </div>

            </div>


            <!-- TANGGAL -->

            <div class="col-md-6">

                <label
                    class="
                        form-label
                        text-muted
                        small
                    "
                >

                    Tanggal Pengajuan

                </label>

                <div class="fw-semibold">

                    <?php if (
                        !empty(
                            $pengajuan['created_at']
                        )
                    ): ?>

                        <?= e(
                            date(
                                'd M Y H:i',
                                strtotime(
                                    $pengajuan['created_at']
                                )
                            )
                        ) ?>

                    <?php else: ?>

                        -

                    <?php endif; ?>

                </div>

            </div>


            <!-- STATUS SEKARANG -->

            <div class="col-12">

                <label
                    class="
                        form-label
                        text-muted
                        small
                    "
                >

                    Status Saat Ini

                </label>

                <div>

                    <?php if (
                        $currentStatus === 'pending' ||
                        $currentStatus === 'menunggu'
                    ): ?>

                        <span
                            class="
                                badge
                                bg-warning
                                text-dark
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-hourglass-split
                                    me-1
                                "
                            ></i>

                            Menunggu

                        </span>


                    <?php elseif (
                        $currentStatus === 'diproses'
                    ): ?>

                        <span
                            class="
                                badge
                                bg-primary
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-arrow-repeat
                                    me-1
                                "
                            ></i>

                            Diproses

                        </span>


                    <?php elseif (
                        $currentStatus === 'selesai'
                    ): ?>

                        <span
                            class="
                                badge
                                bg-success
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-check-circle-fill
                                    me-1
                                "
                            ></i>

                            Selesai

                        </span>


                    <?php elseif (
                        $currentStatus === 'ditolak'
                    ): ?>

                        <span
                            class="
                                badge
                                bg-danger
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-x-circle-fill
                                    me-1
                                "
                            ></i>

                            Ditolak

                        </span>


                    <?php else: ?>

                        <span
                            class="
                                badge
                                bg-secondary
                            "
                        >

                            Belum Ada Status

                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     FORM UBAH STATUS
========================================================= -->

<div class="card shadow-sm">

    <div class="card-body">

        <h6 class="fw-bold mb-4">

            <i
                class="
                    bi
                    bi-arrow-repeat
                    me-1
                "
                style="color:#7c3aed;"
            ></i>

            Ubah Status Pengajuan

        </h6>


        <form
            method="POST"
            action=""
            id="formStatus"
        >


            <!-- =================================================
                 STATUS
            ================================================== -->

            <div class="mb-4">

                <label
                    for="status"
                    class="
                        form-label
                        fw-semibold
                    "
                >

                    Status Pengajuan

                    <span class="text-danger">
                        *
                    </span>

                </label>


                <select
                    name="status"
                    id="status"
                    class="form-select"
                    required
                >

                    <option value="">
                        -- Pilih Status --
                    </option>


                    <option
                        value="pending"
                        <?= (
                            $currentStatus === 'pending' ||
                            $currentStatus === 'menunggu'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        Menunggu

                    </option>


                    <option
                        value="diproses"
                        <?= (
                            $currentStatus === 'diproses'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        Diproses

                    </option>


                    <option
                        value="selesai"
                        <?= (
                            $currentStatus === 'selesai'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        Selesai

                    </option>


                    <option
                        value="ditolak"
                        <?= (
                            $currentStatus === 'ditolak'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        Ditolak

                    </option>

                </select>

            </div>


            <!-- =================================================
                 ALASAN PENOLAKAN
            ================================================== -->

            <div
                id="alasanBox"
                class="mb-4"
                style="
                    display:
                    <?= (
                        $currentStatus === 'ditolak'
                    )
                        ? 'block'
                        : 'none'
                    ?>;
                "
            >

                <label
                    for="alasan_penolakan"
                    class="
                        form-label
                        fw-semibold
                    "
                >

                    Alasan Penolakan

                    <span class="text-danger">
                        *
                    </span>

                </label>


                <!--
                | ADMIN MENGETIK ALASAN SENDIRI
                -->

                <textarea
                    name="alasan_penolakan"
                    id="alasan_penolakan"
                    class="form-control"
                    rows="5"
                    placeholder="Tuliskan alasan penolakan di sini..."
                ><?= e($currentAlasan) ?></textarea>


                <div
                    class="
                        form-text
                    "
                >

                    Contoh:
                    Dokumen KTP yang diunggah tidak jelas.
                    Silakan unggah ulang dokumen dengan kualitas
                    yang lebih jelas.

                </div>


                <?php if (
                    !$hasAlasanPenolakan
                ): ?>

                    <div
                        class="
                            alert
                            alert-warning
                            mt-3
                            mb-0
                        "
                    >

                        <i
                            class="
                                bi
                                bi-exclamation-triangle-fill
                                me-1
                            "
                        ></i>

                        Kolom
                        <strong>
                            alasan_penolakan
                        </strong>
                        belum tersedia di database.

                        Jalankan SQL berikut di phpMyAdmin:

                        <br><br>

                        <code>
                            ALTER TABLE pengajuan_ktp
                            ADD COLUMN alasan_penolakan TEXT NULL;
                        </code>

                    </div>

                <?php endif; ?>

            </div>


            <!-- =================================================
                 BUTTON
            ================================================== -->

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i
                        class="
                            bi
                            bi-save-fill
                            me-1
                        "
                    ></i>

                    Simpan Status

                </button>


                <a
                    href="../pemohon.php"
                    class="
                        btn
                        btn-outline-secondary
                    "
                >

                    <i
                        class="
                            bi
                            bi-x-lg
                            me-1
                        "
                    ></i>

                    Batal

                </a>

            </div>


        </form>

    </div>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const status =
            document.getElementById(
                'status'
            );

        const alasanBox =
            document.getElementById(
                'alasanBox'
            );

        const alasan =
            document.getElementById(
                'alasan_penolakan'
            );


        function updateAlasan() {

            if (
                status.value === 'ditolak'
            ) {

                alasanBox.style.display =
                    'block';

                alasan.required = true;

            } else {

                alasanBox.style.display =
                    'none';

                alasan.required = false;

            }

        }


        status.addEventListener(
            'change',
            updateAlasan
        );


        updateAlasan();

    }
);

</script>


<?php

require_once
    __DIR__ . '/../../includes/footer.php';

?>