<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();


/*
|--------------------------------------------------------------------------
| HAPUS DATA PEMOHON
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'hapus') {

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId > 0) {

            try {

                /*
                | Ambil semua file pengajuan milik user
                */

                $stmtFile = $pdo->prepare("
                    SELECT
                        gambar_path,
                        foto_diri_path
                    FROM pengajuan_ktp
                    WHERE user_id = ?
                ");

                $stmtFile->execute([$userId]);

                $files = $stmtFile->fetchAll(PDO::FETCH_ASSOC);


                /*
                | Hapus data pengajuan
                */

                $stmtDeletePengajuan = $pdo->prepare("
                    DELETE FROM pengajuan_ktp
                    WHERE user_id = ?
                ");

                $stmtDeletePengajuan->execute([$userId]);


                /*
                | Hapus file fisik
                */

                $uploadDir =
                    __DIR__ .
                    '/../uploads/images/';


                foreach ($files as $file) {

                    if (!empty($file['gambar_path'])) {

                        $fileName =
                            basename(
                                $file['gambar_path']
                            );

                        $filePath =
                            $uploadDir .
                            $fileName;

                        if (is_file($filePath)) {
                            @unlink($filePath);
                        }
                    }


                    if (!empty($file['foto_diri_path'])) {

                        $fileName =
                            basename(
                                $file['foto_diri_path']
                            );

                        $filePath =
                            $uploadDir .
                            $fileName;

                        if (is_file($filePath)) {
                            @unlink($filePath);
                        }
                    }
                }


                /*
                | Hapus user
                */

                $stmtDeleteUser = $pdo->prepare("
                    DELETE FROM users
                    WHERE id = ?
                    AND role = 'user'
                ");

                $stmtDeleteUser->execute([$userId]);


                header(
                    'Location: pemohon.php?success=deleted'
                );

                exit;

            } catch (PDOException $e) {

                header(
                    'Location: pemohon.php?error=delete'
                );

                exit;
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| PESAN
|--------------------------------------------------------------------------
*/

$success = '';
$error = '';

if (
    isset($_GET['success']) &&
    $_GET['success'] === 'deleted'
) {

    $success =
        'Data pemohon berhasil dihapus.';
}

if (
    isset($_GET['error']) &&
    $_GET['error'] === 'delete'
) {

    $error =
        'Data pemohon gagal dihapus.';
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PENGAJUAN
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        p.id,
        p.user_id,
        p.nik,
        p.nama_pemohon,
        p.gambar_path,
        p.foto_diri_path,
        p.status,
        p.alasan_penolakan,
        p.created_at,
        u.username AS user_pengaju
    FROM pengajuan_ktp p
    LEFT JOIN users u
        ON u.id = p.user_id
    ORDER BY p.created_at DESC
");

$pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

$pageTitle = 'Daftar Pemohon';

require_once __DIR__ . '/../includes/header.php';

?>


<style>

/*
|--------------------------------------------------------------------------
| HEADER HALAMAN
|--------------------------------------------------------------------------
*/

.page-header-box {

    background: linear-gradient(
        135deg,
        #312e81,
        #1d4ed8
    );

    border-radius: 18px;

    padding: 28px;

    color: white;

    margin-bottom: 24px;

    box-shadow:
        0 10px 30px
        rgba(30, 64, 175, .15);
}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.pengajuan-table {
    min-width: 1000px;
}

.pengajuan-table thead th {

    background: #f5f3ff;

    color: #312e81;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

    padding: 15px;
}

.pengajuan-table tbody td {

    padding: 15px;

    vertical-align: middle;

    font-size: 14px;
}


/*
|--------------------------------------------------------------------------
| USER PENGAJU
|--------------------------------------------------------------------------
*/

.user-pengaju {

    display: flex;

    align-items: center;

    gap: 10px;
}

.user-icon {

    width: 36px;

    height: 36px;

    border-radius: 50%;

    background: #eef2ff;

    color: #312e81;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

.status-badge {

    display: inline-flex;

    align-items: center;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;
}

.status-menunggu {

    background: #fff7ed;

    color: #c2410c;

    border: 1px solid #fed7aa;
}

.status-selesai {

    background: #ecfdf5;

    color: #047857;

    border: 1px solid #a7f3d0;
}

.status-ditolak {

    background: #fef2f2;

    color: #dc2626;

    border: 1px solid #fecaca;
}


/*
|--------------------------------------------------------------------------
| AKSI
|--------------------------------------------------------------------------
*/

.aksi-wrapper {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;
}

.btn-lampiran {

    background: #eef2ff;

    color: #3730a3;

    border: 1px solid #c7d2fe;

    border-radius: 8px;

    padding: 8px 11px;

    font-size: 12px;

    font-weight: 600;

    text-decoration: none;

    white-space: nowrap;
}

.btn-lampiran:hover {

    background: #e0e7ff;

    color: #312e81;
}


/*
|--------------------------------------------------------------------------
| EMPTY
|--------------------------------------------------------------------------
*/

.empty-box {

    text-align: center;

    padding: 60px 20px;

    color: #6b7280;
}

.empty-box i {

    font-size: 45px;

    display: block;

    margin-bottom: 12px;

    color: #9ca3af;
}

</style>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="page-header-box">

    <div
        class="d-flex
               justify-content-between
               align-items-center
               flex-wrap
               gap-3"
    >

        <div>

            <h4 class="fw-bold mb-1">

                <i
                    class="bi bi-people-fill me-2"
                ></i>

                Daftar Pengajuan KTP

            </h4>

            <p class="mb-0 opacity-75">

                Kelola data pemohon dan pengajuan
                cetak KTP.

            </p>

        </div>


        <div>

            <span
                class="badge bg-white text-primary px-3 py-2"
            >

                <?= count($pengajuan) ?> Pengajuan

            </span>

        </div>

    </div>

</div>


<!-- =========================================================
     PESAN
========================================================= -->

<?php if ($success !== ''): ?>

    <div
        class="alert alert-success
               alert-dismissible fade show"
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


<?php if ($error !== ''): ?>

    <div
        class="alert alert-danger
               alert-dismissible fade show"
    >

        <i
            class="bi bi-exclamation-triangle-fill me-2"
        ></i>

        <?= e($error) ?>


        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

<?php endif; ?>


<!-- =========================================================
     TABLE
========================================================= -->

<div class="card border-0 shadow-sm">

    <div class="card-body p-0">

        <div class="table-responsive">

            <table
                class="table table-hover
                       align-middle
                       mb-0
                       pengajuan-table"
            >

                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            NIK
                        </th>

                        <th>
                            Nama Pemohon
                        </th>

                        <th>
                            User Pengaju
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Tanggal Pengajuan
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if (!$pengajuan): ?>

                    <tr>

                        <td
                            colspan="7"
                            class="p-0"
                        >

                            <div class="empty-box">

                                <i
                                    class="bi bi-inbox"
                                ></i>

                                <strong>
                                    Belum ada pengajuan
                                </strong>

                                <div class="small mt-1">
                                    Data pengajuan akan muncul di sini.
                                </div>

                            </div>

                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach (
                        $pengajuan as $index => $row
                    ): ?>

                        <?php

                        $status =
                            strtolower(
                                trim(
                                    $row['status'] ?? ''
                                )
                            );


                        if (
                            $status === 'pending' ||
                            $status === 'menunggu' ||
                            $status === 'diproses'
                        ) {

                            $statusText =
                                'Menunggu';

                            $statusClass =
                                'status-menunggu';

                        } elseif (
                            $status === 'selesai'
                        ) {

                            $statusText =
                                'Selesai';

                            $statusClass =
                                'status-selesai';

                        } elseif (
                            $status === 'ditolak'
                        ) {

                            $statusText =
                                'Ditolak';

                            $statusClass =
                                'status-ditolak';

                        } else {

                            $statusText =
                                ucfirst($status);

                            $statusClass =
                                'status-menunggu';
                        }

                        ?>


                        <tr>


                            <!-- NO -->

                            <td class="fw-semibold">

                                <?= $index + 1 ?>

                            </td>


                            <!-- NIK -->

                            <td>

                                <span
                                    class="fw-semibold"
                                >

                                    <?= e(
                                        $row['nik']
                                    ) ?>

                                </span>

                            </td>


                            <!-- NAMA -->

                            <td>

                                <?= e(
                                    $row['nama_pemohon']
                                ) ?>

                            </td>


                            <!-- USER PENGAJU -->

                            <td>

                                <div class="user-pengaju">

                                    <div class="user-icon">

                                        <i
                                            class="bi bi-person-fill"
                                        ></i>

                                    </div>


                                    <div>

                                        <div
                                            class="fw-semibold"
                                        >

                                            <?= e(
                                                $row['user_pengaju']
                                                ?? '-'
                                            ) ?>

                                        </div>

                                        <small
                                            class="text-muted"
                                        >

                                            User yang mengajukan

                                        </small>

                                    </div>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="
                                        status-badge
                                        <?= $statusClass ?>
                                    "
                                >

                                    <?= e(
                                        $statusText
                                    ) ?>

                                </span>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div
                                    class="fw-semibold"
                                >

                                    <?= e(
                                        date(
                                            'd M Y',
                                            strtotime(
                                                $row['created_at']
                                            )
                                        )
                                    ) ?>

                                </div>

                                <small
                                    class="text-muted"
                                >

                                    <?= e(
                                        date(
                                            'H:i',
                                            strtotime(
                                                $row['created_at']
                                            )
                                        )
                                    ) ?>

                                </small>

                            </td>


                            <!-- =================================================
                                 AKSI
                                 STATUS BUTTON SUDAH DIHAPUS
                            ================================================== -->

                            <td>

                                <div
                                    class="aksi-wrapper"
                                >

                                    <a
                                        href="
                                            proses/pengajuan.php?id=
                                            <?= (int)
                                                $row['id'] ?>
                                        "
                                        class="btn-lampiran"
                                        title="Lihat lampiran"
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-paperclip
                                                me-1
                                            "
                                        ></i>

                                        Lihat Lampiran

                                    </a>

                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>