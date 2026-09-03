<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$errors = [];
$success = '';

/*
|--------------------------------------------------------------------------
| HAPUS USER
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['hapus_user'])
) {

    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId <= 0) {

        $errors[] = 'ID user tidak valid.';

    } else {

        try {

            $stmt = $pdo->prepare("
                SELECT id, username, role
                FROM users
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$userId]);

            $user = $stmt->fetch();

            if (!$user) {

                $errors[] = 'User tidak ditemukan.';

            } elseif ($user['role'] === 'admin') {

                $errors[] =
                    'User admin tidak dapat dihapus dari halaman ini.';

            } else {

                /*
                | Ambil file pengajuan terlebih dahulu
                */

                $stmt = $pdo->prepare("
                    SELECT gambar_path, foto_diri_path
                    FROM pengajuan_ktp
                    WHERE user_id = ?
                ");

                $stmt->execute([$userId]);

                $files = $stmt->fetchAll();


                /*
                | Hapus pengajuan
                */

                $stmt = $pdo->prepare("
                    DELETE FROM pengajuan_ktp
                    WHERE user_id = ?
                ");

                $stmt->execute([$userId]);


                /*
                | Hapus user
                */

                $stmt = $pdo->prepare("
                    DELETE FROM users
                    WHERE id = ?
                    AND role = 'user'
                ");

                $stmt->execute([$userId]);


                /*
                | Hapus file
                */

                foreach ($files as $file) {

                    if (!empty($file['gambar_path'])) {

                        $path =
                            UPLOAD_DIR .
                            basename($file['gambar_path']);

                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }


                    if (!empty($file['foto_diri_path'])) {

                        $path =
                            UPLOAD_DIR .
                            basename($file['foto_diri_path']);

                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }
                }


                $success =
                    "User '" .
                    $user['username'] .
                    "' berhasil dihapus.";
            }

        } catch (PDOException $e) {

            $errors[] =
                'Gagal menghapus user: ' .
                $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PEMOHON
|--------------------------------------------------------------------------
|
| Setiap user hanya ditampilkan satu kali.
| Pengajuan yang ditampilkan adalah pengajuan terbaru.
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            u.id,
            u.username,
            u.role,
            u.created_at,

            p.id AS pengajuan_id,
            p.nik,
            p.nama_pemohon,
            p.gambar_path,
            p.foto_diri_path,
            p.status,
            p.created_at AS pengajuan_created_at

        FROM users u

        LEFT JOIN pengajuan_ktp p
            ON p.id = (
                SELECT p2.id
                FROM pengajuan_ktp p2
                WHERE p2.user_id = u.id
                ORDER BY p2.created_at DESC, p2.id DESC
                LIMIT 1
            )

        WHERE u.role = 'user'

        ORDER BY u.created_at DESC
    ";

    $stmt = $pdo->query($sql);

    $pemohon = $stmt->fetchAll();

} catch (PDOException $e) {

    $pemohon = [];

    $errors[] =
        'Gagal mengambil data pemohon: ' .
        $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| HITUNG STATUS
|--------------------------------------------------------------------------
*/

$totalPemohon = count($pemohon);

$totalMenunggu = 0;
$totalDiproses = 0;
$totalSelesai = 0;
$totalDitolak = 0;
$totalBelum = 0;


foreach ($pemohon as $p) {

    $status = strtolower(
        trim(
            $p['status'] ?? ''
        )
    );


    if (
        $status === 'pending' ||
        $status === 'menunggu'
    ) {

        $totalMenunggu++;

    } elseif ($status === 'diproses') {

        $totalDiproses++;

    } elseif ($status === 'selesai') {

        $totalSelesai++;

    } elseif ($status === 'ditolak') {

        $totalDitolak++;

    } else {

        $totalBelum++;
    }
}


$pageTitle = 'Daftar Pemohon';

require_once __DIR__ . '/../includes/header.php';

?>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h5 class="fw-bold mb-1">

            <i
                class="bi bi-people-fill me-2"
                style="color:#7c3aed;"
            ></i>

            Daftar Pemohon

        </h5>

        <p class="text-muted small mb-0">

            Kelola data pemohon dan pengajuan cetak KTP.

        </p>

    </div>


    <a
        href="<?= $bp ?>admin/tambah_user.php"
        class="btn btn-primary"
    >

        <i class="bi bi-person-plus-fill me-1"></i>

        Tambah User

    </a>

</div>


<!-- =========================================================
     ALERT
========================================================= -->

<?php if ($success): ?>

    <div class="alert alert-success">

        <i class="bi bi-check-circle-fill me-1"></i>

        <?= e($success) ?>

    </div>

<?php endif; ?>


<?php if ($errors): ?>

    <div class="alert alert-danger">

        <ul class="mb-0 ps-3">

            <?php foreach ($errors as $error): ?>

                <li>
                    <?= e($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>


<!-- =========================================================
     STATISTIK
========================================================= -->

<div class="row g-3 mb-4">


    <div class="col-xl-2 col-md-4 col-6">

        <div class="stat-card">

            <div
                class="stat-icon"
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa);"
            >

                <i class="bi bi-people-fill"></i>

            </div>

            <div class="stat-value">
                <?= $totalPemohon ?>
            </div>

            <div class="stat-label">
                Total Pemohon
            </div>

        </div>

    </div>


    <div class="col-xl-2 col-md-4 col-6">

        <div class="stat-card">

            <div
                class="stat-icon"
                style="background:linear-gradient(135deg,#f59e0b,#fbbf24);"
            >

                <i class="bi bi-hourglass-split"></i>

            </div>

            <div class="stat-value">
                <?= $totalMenunggu ?>
            </div>

            <div class="stat-label">
                Menunggu
            </div>

        </div>

    </div>


    <div class="col-xl-2 col-md-4 col-6">

        <div class="stat-card">

            <div
                class="stat-icon"
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa);"
            >

                <i class="bi bi-arrow-repeat"></i>

            </div>

            <div class="stat-value">
                <?= $totalDiproses ?>
            </div>

            <div class="stat-label">
                Diproses
            </div>

        </div>

    </div>


    <div class="col-xl-2 col-md-4 col-6">

        <div class="stat-card">

            <div
                class="stat-icon"
                style="background:linear-gradient(135deg,#059669,#10b981);"
            >

                <i class="bi bi-check-circle-fill"></i>

            </div>

            <div class="stat-value">
                <?= $totalSelesai ?>
            </div>

            <div class="stat-label">
                Selesai
            </div>

        </div>

    </div>


    <div class="col-xl-2 col-md-4 col-6">

        <div class="stat-card">

            <div
                class="stat-icon"
                style="background:linear-gradient(135deg,#dc2626,#ef4444);"
            >

                <i class="bi bi-x-circle-fill"></i>

            </div>

            <div class="stat-value">
                <?= $totalDitolak ?>
            </div>

            <div class="stat-label">
                Ditolak
            </div>

        </div>

    </div>


    <div class="col-xl-2 col-md-4 col-6">

        <div class="stat-card">

            <div
                class="stat-icon"
                style="background:linear-gradient(135deg,#64748b,#94a3b8);"
            >

                <i class="bi bi-dash-circle-fill"></i>

            </div>

            <div class="stat-value">
                <?= $totalBelum ?>
            </div>

            <div class="stat-label">
                Belum Mengajukan
            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     TABEL
========================================================= -->

<div class="card">

    <div class="card-body p-0">


        <div
            class="d-flex justify-content-between align-items-center p-4"
        >

            <div>

                <h6 class="fw-bold mb-1">

                    <i
                        class="bi bi-list-ul me-1"
                        style="color:#7c3aed;"
                    ></i>

                    Data Pemohon

                </h6>

                <span class="text-muted small">

                    Status berdasarkan pengajuan terbaru.

                </span>

            </div>


            <span class="badge bg-primary">

                <?= $totalPemohon ?> Pemohon

            </span>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th width="50">
                            #
                        </th>

                        <th>
                            Pemohon
                        </th>

                        <th>
                            NIK
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Tanggal Pengajuan
                        </th>

                        <th class="text-center">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!$pemohon): ?>

                    <tr>

                        <td
                            colspan="6"
                            class="text-center py-5"
                        >

                            <i
                                class="bi bi-people fs-1 text-muted"
                            ></i>

                            <div class="fw-semibold mt-2">

                                Belum ada pemohon

                            </div>

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($pemohon as $index => $p): ?>

                        <?php

                        $status = strtolower(
                            trim(
                                $p['status'] ?? ''
                            )
                        );

                        ?>


                        <tr>


                            <!-- NOMOR -->

                            <td>
                                <?= $index + 1 ?>
                            </td>


                            <!-- PEMOHON -->

                            <td>

                                <div
                                    class="d-flex align-items-center gap-2"
                                >

                                    <div
                                        style="
                                            width:42px;
                                            height:42px;
                                            border-radius:50%;
                                            background:linear-gradient(
                                                135deg,
                                                #7c3aed,
                                                #a78bfa
                                            );
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            color:white;
                                            font-weight:700;
                                        "
                                    >

                                        <?= strtoupper(
                                            substr(
                                                $p['username'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>


                                    <div>

                                        <div class="fw-bold">

                                            <?= e(
                                                $p['username']
                                            ) ?>

                                        </div>


                                        <?php if (
                                            !empty(
                                                $p['nama_pemohon']
                                            )
                                        ): ?>

                                            <small class="text-muted">

                                                <?= e(
                                                    $p['nama_pemohon']
                                                ) ?>

                                            </small>

                                        <?php else: ?>

                                            <small class="text-muted">

                                                Belum mengajukan

                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </td>


                            <!-- NIK -->

                            <td>

                                <?php if (
                                    !empty($p['nik'])
                                ): ?>

                                    <?= e($p['nik']) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if (
                                    $status === 'pending' ||
                                    $status === 'menunggu'
                                ): ?>

                                    <span
                                        class="status-badge status-menunggu"
                                    >

                                        <i class="bi bi-hourglass-split me-1"></i>

                                        Menunggu

                                    </span>


                                <?php elseif (
                                    $status === 'diproses'
                                ): ?>

                                    <span
                                        class="status-badge status-diproses"
                                    >

                                        <i class="bi bi-arrow-repeat me-1"></i>

                                        Diproses

                                    </span>


                                <?php elseif (
                                    $status === 'selesai'
                                ): ?>

                                    <span
                                        class="status-badge status-selesai"
                                    >

                                        <i class="bi bi-check-circle-fill me-1"></i>

                                        Selesai

                                    </span>


                                <?php elseif (
                                    $status === 'ditolak'
                                ): ?>

                                    <span
                                        class="status-badge status-ditolak"
                                    >

                                        <i class="bi bi-x-circle-fill me-1"></i>

                                        Ditolak

                                    </span>


                                <?php else: ?>

                                    <span class="badge bg-secondary">

                                        Belum Mengajukan

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <?php if (
                                    !empty(
                                        $p['pengajuan_created_at']
                                    )
                                ): ?>

                                    <?= e(
                                        date(
                                            'd M Y H:i',
                                            strtotime(
                                                $p['pengajuan_created_at']
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- AKSI -->

                            <td class="text-center">

                                <div
                                    class="d-flex justify-content-center gap-1"
                                >


                                    <!-- LIHAT -->

                                    <?php if (
                                        !empty(
                                            $p['pengajuan_id']
                                        )
                                    ): ?>

                                        <a
                                            href="<?= $bp ?>admin/detail_pengajuan.php?id=<?= (int) $p['pengajuan_id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Lihat Detail"
                                        >

                                            <i
                                                class="bi bi-eye-fill"
                                            ></i>

                                        </a>

                                    <?php endif; ?>


                                    <!-- PROSES -->

                                    <?php if (
                                        !empty(
                                            $p['pengajuan_id']
                                        )
                                    ): ?>

                                        <a
                                            href="<?= $bp ?>admin/proses_pengajuan.php?id=<?= (int) $p['pengajuan_id'] ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Proses Pengajuan"
                                        >

                                            <i
                                                class="bi bi-pencil-square"
                                            ></i>

                                        </a>

                                    <?php endif; ?>


                                    <!-- HAPUS -->

                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="
                                            return confirm(
                                                'Yakin ingin menghapus user ini? Semua pengajuan KTP juga akan dihapus.'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int) $p['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="hapus_user"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Hapus User"
                                        >

                                            <i
                                                class="bi bi-trash-fill"
                                            ></i>

                                        </button>

                                    </form>


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