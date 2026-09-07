<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die('ID pengajuan tidak valid.');
}

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| PROSES UBAH STATUS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'ubah_status') {

        $status = strtolower(trim($_POST['status'] ?? ''));
        $alasan_penolakan = trim($_POST['alasan_penolakan'] ?? '');

        /*
         * STATUS YANG DIPERBOLEHKAN
         * Diproses sudah dihapus.
         */

        $statusValid = [
            'menunggu',
            'selesai',
            'ditolak'
        ];

        /*
         * Validasi status
         */

        if (!in_array($status, $statusValid, true)) {

            $error = 'Status pengajuan tidak valid.';

        /*
         * Jika ditolak wajib memberikan alasan
         */

        } elseif (
            $status === 'ditolak' &&
            $alasan_penolakan === ''
        ) {

            $error =
                'Alasan penolakan wajib diisi.';

        } else {

            /*
             * Jika status bukan ditolak,
             * alasan penolakan dikosongkan.
             */

            if ($status !== 'ditolak') {

                $alasan_penolakan = null;
            }


            try {

                $stmt = $pdo->prepare("
                    UPDATE pengajuan_ktp
                    SET
                        status = ?,
                        alasan_penolakan = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $status,
                    $alasan_penolakan,
                    $id
                ]);


                $success =
                    'Status pengajuan berhasil diperbarui.';


            } catch (PDOException $e) {

                $error =
                    'Gagal memperbarui status pengajuan.';
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PENGAJUAN
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        user_id,
        nik,
        nama_pemohon,
        gambar_path,
        foto_diri_path,
        status,
        alasan_penolakan,
        created_at
    FROM pengajuan_ktp
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$pengajuan =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$pengajuan) {

    die(
        'Data pengajuan tidak ditemukan.'
    );
}


/*
|--------------------------------------------------------------------------
| DATA TAMBAHAN
|--------------------------------------------------------------------------
*/

$statusSekarang = strtolower(
    trim(
        $pengajuan['status'] ?? 'menunggu'
    )
);


/*
 * Jika status lama masih "diproses",
 * tampilkan sebagai menunggu.
 */

if (
    $statusSekarang === '' ||
    $statusSekarang === 'diproses'
) {

    $statusSekarang = 'menunggu';
}


/*
|--------------------------------------------------------------------------
| URL LAMPIRAN
|--------------------------------------------------------------------------
*/

$gambarUrl = '';

if (!empty($pengajuan['gambar_path'])) {

    $gambarUrl =
        '/aplikasi-cetak-ktp/uploads/images/' .
        rawurlencode(
            basename(
                $pengajuan['gambar_path']
            )
        );
}


$fotoUrl = '';

if (!empty($pengajuan['foto_diri_path'])) {

    $fotoUrl =
        '/aplikasi-cetak-ktp/uploads/images/' .
        rawurlencode(
            basename(
                $pengajuan['foto_diri_path']
            )
        );
}


/*
|--------------------------------------------------------------------------
| CLASS STATUS
|--------------------------------------------------------------------------
*/

$statusClass = 'status-menunggu';

switch ($statusSekarang) {

    case 'selesai':

        $statusClass =
            'status-selesai';

        break;

    case 'ditolak':

        $statusClass =
            'status-ditolak';

        break;
}


/*
|--------------------------------------------------------------------------
| FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

$tanggalPengajuan = '-';

if (!empty($pengajuan['created_at'])) {

    $timestamp =
        strtotime(
            $pengajuan['created_at']
        );

    if ($timestamp !== false) {

        $tanggalPengajuan =
            date(
                'd/m/Y H:i',
                $timestamp
            );
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Proses Pengajuan KTP
</title>


<!-- FONT -->

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<!-- ICON -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

/* ============================================================
   RESET
============================================================ */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* ============================================================
   BODY
============================================================ */

body {

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    background: #f6f5fc;

    color: #1f1b3d;

    font-size: 15px;

    min-height: 100vh;
}


/* ============================================================
   CONTAINER
============================================================ */

.page-container {

    width: 100%;

    max-width: 1180px;

    margin: 0 auto;

    padding:
        30px
        28px
        50px;
}


/* ============================================================
   HEADER
============================================================ */

.page-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 24px;
}


.header-left {

    display: flex;

    align-items: center;

    gap: 14px;
}


.back-button {

    width: 44px;

    height: 44px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    text-decoration: none;

    color: #5b4bb7;

    background: #ffffff;

    border: 1px solid #e8e6f2;

    box-shadow:
        0 3px 12px rgba(31, 27, 61, 0.05);

    transition: .2s;
}


.back-button:hover {

    background: #f1efff;

    transform: translateX(-2px);
}


.back-button i {

    font-size: 20px;
}


.page-title {

    font-size: 24px;

    font-weight: 800;

    color: #171334;

    line-height: 1.2;
}


.page-subtitle {

    margin-top: 5px;

    font-size: 14px;

    color: #6b7280;
}


/* ============================================================
   ALERT
============================================================ */

.alert {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    padding: 14px 16px;

    border-radius: 12px;

    margin-bottom: 18px;

    font-size: 14px;

    font-weight: 600;
}


.alert i {

    font-size: 18px;

    flex-shrink: 0;
}


.alert-success {

    background: #ecfdf5;

    color: #047857;

    border: 1px solid #a7f3d0;
}


.alert-error {

    background: #fef2f2;

    color: #b91c1c;

    border: 1px solid #fecaca;
}


/* ============================================================
   MAIN GRID
============================================================ */

.content-grid {

    display: grid;

    grid-template-columns:
        minmax(0, 1.15fr)
        minmax(340px, .85fr);

    gap: 20px;

    align-items: start;
}


/* ============================================================
   CARD
============================================================ */

.card {

    background: #ffffff;

    border: 1px solid #e8e6f2;

    border-radius: 16px;

    box-shadow:
        0 8px 25px rgba(31, 27, 61, 0.06);

    overflow: hidden;
}


.card-header {

    padding: 20px 22px;

    border-bottom: 1px solid #eeeeF5;

    display: flex;

    align-items: center;

    gap: 12px;
}


.card-header-icon {

    width: 40px;

    height: 40px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eeeafd;

    color: #7c3aed;

    flex-shrink: 0;
}


.card-header-icon i {

    font-size: 19px;
}


.card-title {

    font-size: 19px;

    font-weight: 800;

    color: #1f1b3d;
}


.card-subtitle {

    font-size: 13px;

    color: #8a879a;

    margin-top: 3px;
}


.card-body {

    padding: 20px 22px;
}


/* ============================================================
   DATA PEMOHON
============================================================ */

.data-grid {

    display: grid;

    grid-template-columns: 1fr;

    gap: 0;
}


.data-item {

    padding: 17px 0;

    border-bottom: 1px solid #eeeeF5;
}


.data-item:first-child {

    padding-top: 2px;
}


.data-item:last-child {

    border-bottom: none;

    padding-bottom: 2px;
}


.data-label {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 14px;

    font-weight: 700;

    color: #6b7280;

    margin-bottom: 7px;
}


.data-label i {

    color: #8b5cf6;

    font-size: 16px;
}


.data-value {

    font-size: 17px;

    font-weight: 700;

    color: #1f1b3d;

    line-height: 1.5;

    word-break: break-word;
}


/* ============================================================
   STATUS BADGE
============================================================ */

.status-badge {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 8px 13px;

    border-radius: 999px;

    font-size: 14px;

    font-weight: 700;

    text-transform: capitalize;
}


.status-badge i {

    font-size: 9px;
}


.status-menunggu {

    color: #b45309;

    background: #fff7ed;
}


.status-selesai {

    color: #047857;

    background: #ecfdf5;
}


.status-ditolak {

    color: #b91c1c;

    background: #fef2f2;
}


/* ============================================================
   LAMPIRAN
============================================================ */

.attachment-list {

    display: flex;

    flex-direction: column;

    gap: 12px;
}


.attachment-item {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 15px 16px;

    border: 1px solid #e8e6f2;

    border-radius: 12px;

    background: #faf9fe;
}


.attachment-info {

    display: flex;

    align-items: center;

    gap: 12px;

    min-width: 0;
}


.attachment-icon {

    width: 42px;

    height: 42px;

    flex-shrink: 0;

    border-radius: 10px;

    background: #eeeafd;

    color: #7c3aed;

    display: flex;

    align-items: center;

    justify-content: center;
}


.attachment-icon i {

    font-size: 19px;
}


.attachment-name {

    font-size: 15px;

    font-weight: 700;

    color: #302b50;

    line-height: 1.4;
}


.attachment-desc {

    font-size: 12px;

    color: #8a879a;

    margin-top: 3px;
}


.attachment-btn {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 9px 13px;

    border-radius: 9px;

    background: #7c3aed;

    color: #ffffff;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;

    white-space: nowrap;

    transition: .2s;
}


.attachment-btn:hover {

    background: #6d28d9;

    transform: translateY(-1px);
}


.attachment-empty {

    padding: 15px;

    border-radius: 11px;

    background: #f9fafb;

    border: 1px dashed #d9d6e7;

    color: #8a879a;

    text-align: center;

    font-size: 14px;
}


/* ============================================================
   FORM
============================================================ */

.form-group {

    margin-bottom: 20px;
}


.form-label {

    display: block;

    font-size: 15px;

    font-weight: 700;

    color: #302b50;

    margin-bottom: 8px;
}


.form-select,
.form-control {

    width: 100%;

    border: 1px solid #dcd9e9;

    border-radius: 11px;

    background: #ffffff;

    color: #25203f;

    font-family: inherit;

    font-size: 16px;

    padding: 13px 14px;

    outline: none;

    transition: .2s;
}


.form-select:focus,
.form-control:focus {

    border-color: #8b5cf6;

    box-shadow:
        0 0 0 3px rgba(139, 92, 246, .12);
}


.form-control {

    min-height: 125px;

    resize: vertical;

    line-height: 1.6;
}


/* ============================================================
   ALASAN PENOLAKAN
============================================================ */

.reason-box {

    display: none;

    margin-top: -3px;

    margin-bottom: 20px;

    padding: 15px;

    border-radius: 12px;

    background: #fff7f7;

    border: 1px solid #fecaca;
}


.reason-box.show {

    display: block;
}


.reason-title {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 14px;

    font-weight: 700;

    color: #b91c1c;

    margin-bottom: 9px;
}


.reason-title i {

    font-size: 17px;
}


.reason-help {

    font-size: 12px;

    color: #8a5c5c;

    line-height: 1.5;

    margin-top: 7px;
}


/* ============================================================
   TOMBOL
============================================================ */

.action-buttons {

    display: grid;

    grid-template-columns: 1fr;

    gap: 10px;

    margin-top: 5px;
}


.btn {

    width: 100%;

    border: none;

    border-radius: 11px;

    padding: 13px 18px;

    font-family: inherit;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    text-decoration: none;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    transition: .2s;
}


.btn-primary {

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #8b5cf6,
            #6d28d9
        );

    box-shadow:
        0 5px 14px rgba(124, 58, 237, .22);
}


.btn-primary:hover {

    transform: translateY(-1px);

    box-shadow:
        0 7px 17px rgba(124, 58, 237, .28);
}


.btn-secondary {

    color: #5b5573;

    background: #f5f3fa;

    border: 1px solid #e4e1ee;
}


.btn-secondary:hover {

    background: #ece9f7;
}


/* ============================================================
   INFORMASI PENOLAKAN
============================================================ */

.rejection-card {

    margin-top: 20px;

    border: 1px solid #fecaca;

    background: #fffafa;

    border-radius: 14px;

    overflow: hidden;
}


.rejection-header {

    padding: 14px 16px;

    display: flex;

    align-items: center;

    gap: 9px;

    color: #b91c1c;

    font-size: 14px;

    font-weight: 800;

    border-bottom: 1px solid #fee2e2;
}


.rejection-body {

    padding: 16px;

    color: #6b3b3b;

    font-size: 15px;

    line-height: 1.7;

    white-space: pre-line;
}


/* ============================================================
   INFO STATUS
============================================================ */

.current-status {

    padding: 14px 15px;

    background: #f8f7fc;

    border-radius: 11px;

    margin-bottom: 20px;

    border: 1px solid #ebe9f3;
}


.current-status-label {

    font-size: 13px;

    color: #7c788d;

    font-weight: 600;

    margin-bottom: 8px;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 900px) {

    .content-grid {

        grid-template-columns: 1fr;
    }

}


@media (max-width: 600px) {

    .page-container {

        padding:
            20px
            15px
            35px;
    }


    .page-header {

        align-items: flex-start;
    }


    .page-title {

        font-size: 21px;
    }


    .page-subtitle {

        font-size: 13px;
    }


    .card-header {

        padding: 17px;
    }


    .card-body {

        padding: 17px;
    }


    .card-title {

        font-size: 17px;
    }


    .data-value {

        font-size: 16px;
    }


    .attachment-item {

        align-items: flex-start;

        flex-direction: column;
    }


    .attachment-btn {

        width: 100%;

        justify-content: center;
    }

}

</style>

</head>


<body>


<div class="page-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="page-header">

        <div class="header-left">

            <a
                href="../dashboard.php"
                class="back-button"
                title="Kembali"
            >

                <i class="bi bi-arrow-left"></i>

            </a>


            <div>

                <h1 class="page-title">
                    Proses Pengajuan KTP
                </h1>

                <div class="page-subtitle">
                    Periksa data pemohon dan perbarui status pengajuan.
                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         ALERT SUCCESS
    ====================================================== -->

    <?php if ($success): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle-fill"></i>

            <div>

                <?= htmlspecialchars($success) ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         ALERT ERROR
    ====================================================== -->

    <?php if ($error): ?>

        <div class="alert alert-error">

            <i class="bi bi-exclamation-circle-fill"></i>

            <div>

                <?= htmlspecialchars($error) ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <div class="content-grid">


        <!-- =================================================
             DATA PEMOHON
        ================================================== -->

        <div>

            <div class="card">

                <div class="card-header">

                    <div class="card-header-icon">

                        <i class="bi bi-person-vcard-fill"></i>

                    </div>


                    <div>

                        <div class="card-title">
                            Data Pemohon
                        </div>

                        <div class="card-subtitle">
                            Informasi pengajuan cetak KTP
                        </div>

                    </div>

                </div>


                <div class="card-body">

                    <div class="data-grid">


                        <!-- NIK -->

                        <div class="data-item">

                            <div class="data-label">

                                <i class="bi bi-credit-card-2-front"></i>

                                NIK

                            </div>


                            <div class="data-value">

                                <?= htmlspecialchars(
                                    $pengajuan['nik'] ?? '-'
                                ) ?>

                            </div>

                        </div>


                        <!-- NAMA -->

                        <div class="data-item">

                            <div class="data-label">

                                <i class="bi bi-person"></i>

                                Nama Pemohon

                            </div>


                            <div class="data-value">

                                <?= htmlspecialchars(
                                    $pengajuan['nama_pemohon'] ?? '-'
                                ) ?>

                            </div>

                        </div>


                        <!-- STATUS -->

                        <div class="data-item">

                            <div class="data-label">

                                <i class="bi bi-activity"></i>

                                Status Pengajuan

                            </div>


                            <div class="data-value">

                                <span
                                    class="status-badge
                                    <?= htmlspecialchars(
                                        $statusClass
                                    ) ?>"
                                >

                                    <i
                                        class="bi bi-circle-fill"
                                    ></i>


                                    <?= htmlspecialchars(
                                        ucfirst(
                                            $statusSekarang
                                        )
                                    ) ?>

                                </span>

                            </div>

                        </div>


                        <!-- TANGGAL -->

                        <div class="data-item">

                            <div class="data-label">

                                <i class="bi bi-calendar3"></i>

                                Tanggal Pengajuan

                            </div>


                            <div class="data-value">

                                <?= htmlspecialchars(
                                    $tanggalPengajuan
                                ) ?>

                            </div>

                        </div>


                    </div>


                    <!-- =====================================
                         ALASAN PENOLAKAN
                    ====================================== -->

                    <?php if (
                        $statusSekarang === 'ditolak' &&
                        !empty(
                            $pengajuan['alasan_penolakan']
                        )
                    ): ?>

                        <div class="rejection-card">

                            <div class="rejection-header">

                                <i
                                    class="bi bi-x-circle-fill"
                                ></i>

                                Alasan Penolakan

                            </div>


                            <div class="rejection-body">

                                <?= htmlspecialchars(
                                    $pengajuan[
                                        'alasan_penolakan'
                                    ]
                                ) ?>

                            </div>

                        </div>

                    <?php endif; ?>


                </div>

            </div>


            <!-- =============================================
                 LAMPIRAN
            ============================================== -->

            <div
                class="card"
                style="margin-top:20px;"
            >

                <div class="card-header">

                    <div class="card-header-icon">

                        <i class="bi bi-paperclip"></i>

                    </div>


                    <div>

                        <div class="card-title">
                            Lampiran Dokumen
                        </div>

                        <div class="card-subtitle">
                            Dokumen yang dikirim oleh pemohon
                        </div>

                    </div>

                </div>


                <div class="card-body">

                    <div class="attachment-list">


                        <!-- FOTO KTP -->

                        <?php if ($gambarUrl): ?>

                            <div class="attachment-item">

                                <div class="attachment-info">

                                    <div class="attachment-icon">

                                        <i
                                            class="bi bi-card-image"
                                        ></i>

                                    </div>


                                    <div>

                                        <div class="attachment-name">
                                            Foto / Dokumen KTP
                                        </div>

                                        <div class="attachment-desc">
                                            Lampiran dokumen KTP pemohon
                                        </div>

                                    </div>

                                </div>


                                <a
                                    href="<?= htmlspecialchars(
                                        $gambarUrl
                                    ) ?>"
                                    target="_blank"
                                    class="attachment-btn"
                                >

                                    <i class="bi bi-eye"></i>

                                    Lihat

                                </a>

                            </div>

                        <?php endif; ?>


                        <!-- FOTO DIRI -->

                        <?php if ($fotoUrl): ?>

                            <div class="attachment-item">

                                <div class="attachment-info">

                                    <div class="attachment-icon">

                                        <i
                                            class="bi bi-person-bounding-box"
                                        ></i>

                                    </div>


                                    <div>

                                        <div class="attachment-name">
                                            Foto Diri Memegang KTP
                                        </div>

                                        <div class="attachment-desc">
                                            Foto pemohon sedang memegang KTP
                                        </div>

                                    </div>

                                </div>


                                <a
                                    href="<?= htmlspecialchars(
                                        $fotoUrl
                                    ) ?>"
                                    target="_blank"
                                    class="attachment-btn"
                                >

                                    <i class="bi bi-eye"></i>

                                    Lihat

                                </a>

                            </div>

                        <?php endif; ?>


                        <!-- TIDAK ADA LAMPIRAN -->

                        <?php if (
                            !$gambarUrl &&
                            !$fotoUrl
                        ): ?>

                            <div class="attachment-empty">

                                <i
                                    class="bi bi-file-earmark-x"
                                ></i>

                                Tidak ada lampiran dokumen.

                            </div>

                        <?php endif; ?>


                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             PERBARUI STATUS
        ================================================== -->

        <div class="card">

            <div class="card-header">

                <div class="card-header-icon">

                    <i class="bi bi-arrow-repeat"></i>

                </div>


                <div>

                    <div class="card-title">
                        Perbarui Status
                    </div>

                    <div class="card-subtitle">
                        Tentukan status pengajuan KTP
                    </div>

                </div>

            </div>


            <div class="card-body">


                <!-- STATUS SAAT INI -->

                <div class="current-status">

                    <div class="current-status-label">
                        Status saat ini
                    </div>


                    <span
                        class="status-badge
                        <?= htmlspecialchars(
                            $statusClass
                        ) ?>"
                    >

                        <i
                            class="bi bi-circle-fill"
                        ></i>


                        <?= htmlspecialchars(
                            ucfirst(
                                $statusSekarang
                            )
                        ) ?>

                    </span>

                </div>


                <form
                    method="POST"
                    onsubmit="return validasiForm();"
                >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $pengajuan['id'] ?>"
                    >


                    <input
                        type="hidden"
                        name="action"
                        value="ubah_status"
                    >


                    <!-- STATUS -->

                    <div class="form-group">

                        <label
                            for="status"
                            class="form-label"
                        >
                            Status Pengajuan
                        </label>


                        <select
                            name="status"
                            id="status"
                            class="form-select"
                            onchange="tampilkanAlasan();"
                            required
                        >

                            <option
                                value="menunggu"
                                <?= $statusSekarang === 'menunggu'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Menunggu
                            </option>


                            <option
                                value="selesai"
                                <?= $statusSekarang === 'selesai'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Selesai
                            </option>


                            <option
                                value="ditolak"
                                <?= $statusSekarang === 'ditolak'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Ditolak
                            </option>

                        </select>

                    </div>


                    <!-- ALASAN PENOLAKAN -->

                    <div
                        id="reasonBox"
                        class="reason-box"
                    >

                        <div class="reason-title">

                            <i
                                class="bi bi-exclamation-triangle-fill"
                            ></i>

                            Alasan Penolakan

                        </div>


                        <textarea
                            name="alasan_penolakan"
                            id="alasan_penolakan"
                            class="form-control"
                            placeholder="Tuliskan alasan mengapa pengajuan KTP ditolak..."
                        ><?= htmlspecialchars(
                            $pengajuan[
                                'alasan_penolakan'
                            ] ?? ''
                        ) ?></textarea>


                        <div class="reason-help">

                            Tuliskan alasan penolakan dengan jelas agar
                            pemohon mengetahui bagian yang perlu diperbaiki.

                        </div>

                    </div>


                    <!-- BUTTON -->

                    <div class="action-buttons">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-check-lg"></i>

                            Simpan Perubahan

                        </button>


                        <a
                            href="../dashboard.php"
                            class="btn btn-secondary"
                        >

                            <i class="bi bi-arrow-left"></i>

                            Kembali

                        </a>

                    </div>


                </form>

            </div>

        </div>


    </div>

</div>


<script>

/* ============================================================
   TAMPILKAN ALASAN PENOLAKAN
============================================================ */

function tampilkanAlasan() {

    const status =
        document.getElementById(
            'status'
        ).value;

    const reasonBox =
        document.getElementById(
            'reasonBox'
        );

    const alasan =
        document.getElementById(
            'alasan_penolakan'
        );


    if (status === 'ditolak') {

        reasonBox.classList.add(
            'show'
        );

        alasan.required = true;

    } else {

        reasonBox.classList.remove(
            'show'
        );

        alasan.required = false;

        /*
         * Kosongkan alasan jika
         * status bukan ditolak.
         */

        alasan.value = '';
    }
}


/* ============================================================
   VALIDASI FORM
============================================================ */

function validasiForm() {

    const status =
        document.getElementById(
            'status'
        ).value;

    const alasan =
        document.getElementById(
            'alasan_penolakan'
        );


    if (
        status === 'ditolak' &&
        alasan.value.trim() === ''
    ) {

        alert(
            'Silakan tuliskan alasan penolakan terlebih dahulu.'
        );

        alasan.focus();

        return false;
    }


    return true;
}


/* ============================================================
   JALANKAN SAAT HALAMAN DIBUKA
============================================================ */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        tampilkanAlasan();

    }
);

</script>


</body>

</html>