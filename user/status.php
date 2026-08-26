<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_user();

/*
|--------------------------------------------------------------------------
| AMBIL DATA PENGAJUAN MILIK USER YANG LOGIN
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM pengajuan_ktp
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$daftar = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| JUDUL HALAMAN
|--------------------------------------------------------------------------
*/

$pageTitle = 'Cek Status Pengajuan';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="mb-4">

    <h5 class="fw-bold mb-1">
        <i class="bi bi-list-check me-2"></i>
        Status Pengajuan
    </h5>

    <p class="text-muted mb-0">
        Pantau perkembangan pengajuan cetak KTP Anda.
    </p>

</div>


<?php if (!$daftar): ?>

    <!-- =========================================================
         BELUM ADA PENGAJUAN
    ========================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body text-center py-5">

            <div
                class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                style="
                    width:70px;
                    height:70px;
                    border-radius:50%;
                    background:#f1f5f9;
                "
            >

                <i
                    class="bi bi-file-earmark-text"
                    style="font-size:32px;color:#64748b;"
                ></i>

            </div>

            <h5 class="fw-bold">
                Belum Ada Pengajuan
            </h5>

            <p class="text-muted mb-4">
                Anda belum memiliki pengajuan cetak KTP.
            </p>

            <a
                href="pengajuan.php"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-circle me-1"></i>

                Ajukan Cetak KTP

            </a>

        </div>

    </div>


<?php else: ?>


    <!-- =========================================================
         DAFTAR PENGAJUAN
    ========================================================== -->

    <?php foreach ($daftar as $row): ?>

        <?php

        /*
         * Ambil status
         */
        $status = strtolower(trim($row['status'] ?? ''));

        /*
         * Tentukan label status
         */
        switch ($status) {

            case 'pending':
            case 'menunggu':

                $statusLabel = 'Menunggu';
                $statusClass = 'warning';
                $statusIcon = 'bi-hourglass-split';

                break;


            case 'proses':
            case 'diproses':

                $statusLabel = 'Diproses';
                $statusClass = 'primary';
                $statusIcon = 'bi-arrow-repeat';

                break;


            case 'selesai':

                $statusLabel = 'Selesai';
                $statusClass = 'success';
                $statusIcon = 'bi-check-circle-fill';

                break;


            case 'ditolak':

                $statusLabel = 'Ditolak';
                $statusClass = 'danger';
                $statusIcon = 'bi-x-circle-fill';

                break;


            default:

                $statusLabel = ucfirst($status);
                $statusClass = 'secondary';
                $statusIcon = 'bi-question-circle';

                break;
        }

        ?>


        <!-- =====================================================
             CARD PENGAJUAN
        ====================================================== -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body p-4">


                <!-- HEADER -->

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <h6 class="fw-bold mb-1">

                            <i class="bi bi-file-earmark-text me-2"></i>

                            Pengajuan Cetak KTP

                        </h6>

                        <small class="text-muted">

                            ID Pengajuan:
                            #<?= (int) $row['id'] ?>

                        </small>

                    </div>


                    <!-- STATUS BADGE -->

                    <span class="badge bg-<?= $statusClass ?> px-3 py-2">

                        <i class="bi <?= $statusIcon ?> me-1"></i>

                        <?= e($statusLabel) ?>

                    </span>

                </div>


                <!-- =================================================
                     INFORMASI PEMOHON
                ================================================== -->

                <div class="row g-3 mb-4">

                    <div class="col-md-6">

                        <div class="p-3 bg-light rounded">

                            <small class="text-muted d-block mb-1">
                                NIK
                            </small>

                            <strong>
                                <?= e($row['nik']) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="p-3 bg-light rounded">

                            <small class="text-muted d-block mb-1">
                                Nama Pemohon
                            </small>

                            <strong>
                                <?= e($row['nama_pemohon']) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="p-3 bg-light rounded">

                            <small class="text-muted d-block mb-1">
                                Tanggal Pengajuan
                            </small>

                            <strong>

                                <?= e(
                                    date(
                                        'd M Y, H:i',
                                        strtotime($row['created_at'])
                                    )
                                ) ?>

                            </strong>

                        </div>

                    </div>


                    <?php if (!empty($row['updated_at'])): ?>

                        <div class="col-md-6">

                            <div class="p-3 bg-light rounded">

                                <small class="text-muted d-block mb-1">
                                    Terakhir Diperbarui
                                </small>

                                <strong>

                                    <?= e(
                                        date(
                                            'd M Y, H:i',
                                            strtotime($row['updated_at'])
                                        )
                                    ) ?>

                                </strong>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- =================================================
                     ALASAN PENOLAKAN
                ================================================== -->

                <?php if (
                    $status === 'ditolak'
                    && !empty($row['alasan_penolakan'])
                ): ?>

                    <div class="alert alert-danger mb-0">

                        <div class="d-flex">

                            <div class="me-3">

                                <i
                                    class="bi bi-exclamation-triangle-fill"
                                    style="font-size:24px;"
                                ></i>

                            </div>


                            <div>

                                <h6 class="fw-bold mb-2">

                                    Alasan Penolakan

                                </h6>

                                <p class="mb-0">

                                    <?= nl2br(
                                        e($row['alasan_penolakan'])
                                    ) ?>

                                </p>

                            </div>

                        </div>

                    </div>

                <?php elseif ($status === 'ditolak'): ?>

                    <!-- Jika ditolak tetapi alasan belum tersedia -->

                    <div class="alert alert-danger mb-0">

                        <i class="bi bi-exclamation-circle-fill me-2"></i>

                        Pengajuan Anda ditolak.
                        Silakan hubungi petugas untuk informasi lebih lanjut.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     STATUS INFORMATION
                ================================================== -->

                <?php if ($status === 'pending' || $status === 'menunggu'): ?>

                    <div class="alert alert-warning mt-3 mb-0">

                        <i class="bi bi-clock me-2"></i>

                        Pengajuan Anda sedang menunggu pemeriksaan
                        oleh petugas.

                    </div>

                <?php elseif ($status === 'proses' || $status === 'diproses'): ?>

                    <div class="alert alert-primary mt-3 mb-0">

                        <i class="bi bi-gear-fill me-2"></i>

                        Pengajuan Anda sedang diproses oleh petugas.

                    </div>

                <?php elseif ($status === 'selesai'): ?>

                    <div class="alert alert-success mt-3 mb-0">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        Pengajuan Anda telah selesai diproses.

                    </div>

                <?php endif; ?>


            </div>

        </div>


    <?php endforeach; ?>


<?php endif; ?>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>