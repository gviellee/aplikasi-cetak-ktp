<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$successMsg = '';
$errorMsg = '';

/*
|--------------------------------------------------------------------------
| UPDATE STATUS PENGAJUAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {

    $id = (int) ($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $alasan = trim($_POST['alasan_penolakan'] ?? '');

    /*
     * Ambil status yang tersedia
     */
    $availableStatuses = status_options();

    /*
     * Validasi ID
     */
    if ($id <= 0) {

        $errorMsg = 'ID pengajuan tidak valid.';

    /*
     * Validasi status
     */
    } elseif (!array_key_exists($status, $availableStatuses)) {

        $errorMsg = 'Status pengajuan tidak valid.';

    /*
     * Jika ditolak, alasan wajib diisi
     */
    } elseif ($status === 'ditolak' && $alasan === '') {

        $errorMsg = 'Alasan penolakan wajib diisi jika pengajuan ditolak.';

    } else {

        /*
         * Jika status bukan ditolak,
         * hapus alasan penolakan sebelumnya.
         */
        if ($status !== 'ditolak') {
            $alasan = null;
        }

        try {

            $stmt = $pdo->prepare("
                UPDATE pengajuan_ktp
                SET
                    status = ?,
                    alasan_penolakan = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $status,
                $alasan,
                $id
            ]);

            $successMsg = 'Status pengajuan berhasil diperbarui.';

        } catch (PDOException $e) {

            $errorMsg =
                'Gagal memperbarui status pengajuan: ' .
                $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| FILTER DATA
|--------------------------------------------------------------------------
*/

$search = trim($_GET['q'] ?? '');
$statusFilter = strtolower(trim($_GET['status'] ?? ''));

$sql = "
    SELECT
        p.*,
        u.username
    FROM pengajuan_ktp p
    LEFT JOIN users u
        ON u.id = p.user_id
    WHERE 1=1
";

$params = [];


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= "
        AND (
            p.nik LIKE ?
            OR p.nama_pemohon LIKE ?
            OR u.username LIKE ?
        )
    ";

    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}


/*
|--------------------------------------------------------------------------
| FILTER STATUS
|--------------------------------------------------------------------------
*/

if (
    $statusFilter !== ''
    && array_key_exists($statusFilter, status_options())
) {

    $sql .= " AND p.status = ?";

    $params[] = $statusFilter;
}


/*
|--------------------------------------------------------------------------
| URUTKAN DATA
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY p.created_at DESC";


/*
|--------------------------------------------------------------------------
| AMBIL DATA
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $daftar = $stmt->fetchAll();

} catch (PDOException $e) {

    $daftar = [];

    $errorMsg =
        'Gagal mengambil data pengajuan: ' .
        $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

$pageTitle = 'Daftar Pemohon';

require_once __DIR__ . '/../includes/header.php';

?>


<!-- =========================================================
     ALERT SUCCESS
========================================================= -->

<?php if ($successMsg): ?>

    <div
        class="alert alert-success alert-dismissible fade show"
        role="alert"
    >

        <i class="bi bi-check-circle-fill me-2"></i>

        <?= e($successMsg) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

<?php endif; ?>


<!-- =========================================================
     ALERT ERROR
========================================================= -->

<?php if ($errorMsg): ?>

    <div
        class="alert alert-danger alert-dismissible fade show"
        role="alert"
    >

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        <?= e($errorMsg) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

<?php endif; ?>


<!-- =========================================================
     FILTER
========================================================= -->

<div class="card p-3 mb-3">

    <form method="GET">

        <div class="row g-2">

            <!-- SEARCH -->

            <div class="col-md-5">

                <div class="input-group">

                    <span
                        class="input-group-text bg-white border-end-0"
                    >

                        <i class="bi bi-search text-muted"></i>

                    </span>

                    <input
                        type="text"
                        name="q"
                        class="form-control border-start-0 ps-0"
                        placeholder="Cari NIK / Nama / Username"
                        value="<?= e($search) ?>"
                    >

                </div>

            </div>


            <!-- STATUS -->

            <div class="col-md-4">

                <select
                    name="status"
                    class="form-select"
                >

                    <option value="">
                        Semua Status
                    </option>

                    <?php foreach (status_options() as $key => $label): ?>

                        <option
                            value="<?= e($key) ?>"
                            <?= $statusFilter === $key ? 'selected' : '' ?>
                        >

                            <?= e($label) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- BUTTON -->

            <div class="col-md-3 d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary flex-fill"
                >

                    <i class="bi bi-funnel-fill me-1"></i>

                    Filter

                </button>


                <a
                    href="pemohon.php"
                    class="btn btn-outline-secondary"
                >

                    Reset

                </a>

            </div>

        </div>

    </form>

</div>


<!-- =========================================================
     DATA PEMOHON
========================================================= -->

<div class="card p-4">

    <div
        class="d-flex align-items-center justify-content-between mb-3"
    >

        <h6 class="fw-bold mb-0">

            <i
                class="bi bi-people-fill me-1"
                style="color:#1d4ed8;"
            ></i>

            Daftar Pemohon & Pengajuan

        </h6>

        <span class="text-muted small">

            <?= count($daftar) ?> data

        </span>

    </div>


    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead>

                <tr>

                    <th>NIK</th>

                    <th>Nama</th>

                    <th>Akun</th>

                    <th>Lampiran</th>

                    <th>Status</th>

                    <th>Tanggal</th>

                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>


                <?php if (!$daftar): ?>

                    <tr>

                        <td
                            colspan="7"
                            class="text-center text-muted py-4"
                        >

                            <i
                                class="bi bi-inbox fs-3 d-block mb-2"
                            ></i>

                            Tidak ada data pengajuan.

                        </td>

                    </tr>

                <?php endif; ?>


                <?php foreach ($daftar as $row): ?>

                    <tr>


                        <!-- =================================================
                             NIK
                        ================================================== -->

                        <td class="fw-semibold">

                            <?= e($row['nik']) ?>

                        </td>


                        <!-- =================================================
                             NAMA
                        ================================================== -->

                        <td>

                            <?= e($row['nama_pemohon']) ?>

                        </td>


                        <!-- =================================================
                             USERNAME
                        ================================================== -->

                        <td>

                            <?= e($row['username'] ?? '-') ?>

                        </td>


                        <!-- =================================================
                             LAMPIRAN
                        ================================================== -->

                        <td>

                            <div class="d-flex flex-column gap-2">

                                <!-- FOTO KTP / SURAT KEHILANGAN -->

                                <?php if (!empty($row['gambar_path'])): ?>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalGambar<?= (int) $row['id'] ?>"
                                    >

                                        <i
                                            class="bi bi-file-earmark-image me-1"
                                        ></i>

                                        Lihat KTP

                                    </button>

                                <?php else: ?>

                                    <span class="text-muted small">

                                        KTP tidak ada

                                    </span>

                                <?php endif; ?>


                                <!-- FOTO DIRI -->

                                <?php if (!empty($row['foto_diri_path'])): ?>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalFotoDiri<?= (int) $row['id'] ?>"
                                    >

                                        <i
                                            class="bi bi-person-bounding-box me-1"
                                        ></i>

                                        Lihat Foto Diri

                                    </button>

                                <?php else: ?>

                                    <span class="text-muted small">

                                        Foto diri tidak ada

                                    </span>

                                <?php endif; ?>

                            </div>

                        </td>


                        <!-- =================================================
                             STATUS
                        ================================================== -->

                        <td>

                            <?= status_badge($row['status']) ?>


                            <?php if (
                                $row['status'] === 'ditolak'
                                && !empty($row['alasan_penolakan'])
                            ): ?>

                                <div class="mt-1">

                                    <small class="text-danger">

                                        <i
                                            class="bi bi-info-circle me-1"
                                        ></i>

                                        Ada alasan penolakan

                                    </small>

                                </div>

                            <?php endif; ?>

                        </td>


                        <!-- =================================================
                             TANGGAL
                        ================================================== -->

                        <td class="text-muted small">

                            <?= e(
                                date(
                                    'd M Y, H:i',
                                    strtotime($row['created_at'])
                                )
                            ) ?>

                        </td>


                        <!-- =================================================
                             AKSI
                        ================================================== -->

                        <td>

                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalStatus<?= (int) $row['id'] ?>"
                            >

                                <i
                                    class="bi bi-pencil-square me-1"
                                ></i>

                                Ubah

                            </button>

                        </td>

                    </tr>


                    <!-- =================================================
                         MODAL FOTO KTP
                    ================================================== -->

                    <?php if (!empty($row['gambar_path'])): ?>

                        <div
                            class="modal fade"
                            id="modalGambar<?= (int) $row['id'] ?>"
                            tabindex="-1"
                            aria-hidden="true"
                        >

                            <div
                                class="modal-dialog modal-dialog-centered modal-lg"
                            >

                                <div class="modal-content">


                                    <div class="modal-header">

                                        <h5 class="modal-title">

                                            <i
                                                class="bi bi-file-earmark-image me-2"
                                            ></i>

                                            KTP / Surat Kehilangan

                                        </h5>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                        ></button>

                                    </div>


                                    <div
                                        class="modal-body text-center p-4"
                                    >

                                        <img
                                            src="../uploads/images/<?= e($row['gambar_path']) ?>"
                                            alt="KTP / Surat Kehilangan"
                                            class="img-fluid rounded shadow-sm"
                                            style="
                                                max-height:70vh;
                                                object-fit:contain;
                                            "
                                        >

                                    </div>


                                    <div class="modal-footer">

                                        <a
                                            href="../uploads/images/<?= e($row['gambar_path']) ?>"
                                            target="_blank"
                                            class="btn btn-primary"
                                        >

                                            <i
                                                class="bi bi-box-arrow-up-right me-1"
                                            ></i>

                                            Buka Gambar

                                        </a>


                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal"
                                        >

                                            Tutup

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         MODAL FOTO DIRI
                    ================================================== -->

                    <?php if (!empty($row['foto_diri_path'])): ?>

                        <div
                            class="modal fade"
                            id="modalFotoDiri<?= (int) $row['id'] ?>"
                            tabindex="-1"
                            aria-hidden="true"
                        >

                            <div
                                class="modal-dialog modal-dialog-centered"
                            >

                                <div class="modal-content">


                                    <div class="modal-header">

                                        <h5 class="modal-title">

                                            <i
                                                class="bi bi-person-bounding-box me-2"
                                            ></i>

                                            Foto Diri Pemohon

                                        </h5>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                        ></button>

                                    </div>


                                    <div
                                        class="modal-body text-center p-4"
                                    >

                                        <img
                                            src="../uploads/<?= e($row['foto_diri_path']) ?>"
                                            alt="Foto Diri Pemohon"
                                            class="img-fluid rounded shadow-sm"
                                            style="
                                                max-height:65vh;
                                                object-fit:contain;
                                            "
                                        >

                                        <div class="mt-3">

                                            <small class="text-muted">

                                                <?= e($row['nama_pemohon']) ?>

                                            </small>

                                        </div>

                                    </div>


                                    <div class="modal-footer">

                                        <a
                                            href="../uploads/<?= e($row['foto_diri_path']) ?>"
                                            target="_blank"
                                            class="btn btn-success"
                                        >

                                            <i
                                                class="bi bi-box-arrow-up-right me-1"
                                            ></i>

                                            Buka Gambar

                                        </a>


                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal"
                                        >

                                            Tutup

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         MODAL UBAH STATUS
                    ================================================== -->

                    <div
                        class="modal fade"
                        id="modalStatus<?= (int) $row['id'] ?>"
                        tabindex="-1"
                        aria-hidden="true"
                    >

                        <div
                            class="modal-dialog modal-dialog-centered"
                        >

                            <div class="modal-content">


                                <form method="POST">


                                    <!-- MODAL HEADER -->

                                    <div class="modal-header">

                                        <h6 class="modal-title fw-bold">

                                            <i
                                                class="bi bi-pencil-square me-2"
                                            ></i>

                                            Ubah Status Pengajuan

                                        </h6>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                        ></button>

                                    </div>


                                    <!-- MODAL BODY -->

                                    <div class="modal-body">


                                        <!-- ID -->

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $row['id'] ?>"
                                        >


                                        <!-- NAMA PEMOHON -->

                                        <div class="mb-3">

                                            <label
                                                class="form-label fw-semibold"
                                            >

                                                Nama Pemohon

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= e($row['nama_pemohon']) ?>"
                                                readonly
                                            >

                                        </div>


                                        <!-- STATUS -->

                                        <div class="mb-3">

                                            <label
                                                class="form-label fw-semibold"
                                            >

                                                Status Pengajuan

                                                <span
                                                    class="text-danger"
                                                >
                                                    *
                                                </span>

                                            </label>


                                            <select
                                                name="status"
                                                class="form-select status-select"
                                                required
                                                onchange="toggleAlasan(this, <?= (int) $row['id'] ?>)"
                                            >

                                                <?php foreach (
                                                    status_options()
                                                    as $key => $label
                                                ): ?>

                                                    <option
                                                        value="<?= e($key) ?>"
                                                        <?= $row['status'] === $key ? 'selected' : '' ?>
                                                    >

                                                        <?= e($label) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>


                                        <!-- =================================================
                                             ALASAN PENOLAKAN
                                        ================================================== -->

                                        <div
                                            class="mb-3"
                                            id="alasanBox<?= (int) $row['id'] ?>"
                                            style="<?= $row['status'] === 'ditolak' ? '' : 'display:none;' ?>"
                                        >

                                            <label
                                                class="form-label fw-semibold text-danger"
                                            >

                                                <i
                                                    class="bi bi-exclamation-circle me-1"
                                                ></i>

                                                Alasan Penolakan

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>


                                            <textarea
                                                name="alasan_penolakan"
                                                id="alasan<?= (int) $row['id'] ?>"
                                                class="form-control"
                                                rows="4"
                                                maxlength="500"
                                                placeholder="Tuliskan alasan mengapa pengajuan ditolak..."
                                                <?= $row['status'] === 'ditolak' ? 'required' : '' ?>
                                            ><?= e($row['alasan_penolakan'] ?? '') ?></textarea>


                                            <div class="form-text">

                                                Maksimal 500 karakter.
                                                Alasan ini akan dilihat oleh pemohon.

                                            </div>

                                        </div>


                                        <!-- INFO JIKA SUDAH DITOLAK -->

                                        <?php if (
                                            $row['status'] === 'ditolak'
                                            && !empty($row['alasan_penolakan'])
                                        ): ?>

                                            <div
                                                class="alert alert-danger py-2"
                                            >

                                                <strong>

                                                    Alasan sebelumnya:

                                                </strong>

                                                <br>

                                                <?= nl2br(
                                                    e($row['alasan_penolakan'])
                                                ) ?>

                                            </div>

                                        <?php endif; ?>


                                    </div>


                                    <!-- MODAL FOOTER -->

                                    <div class="modal-footer">

                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal"
                                        >

                                            Batal

                                        </button>


                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="btn btn-primary"
                                        >

                                            <i
                                                class="bi bi-check-lg me-1"
                                            ></i>

                                            Simpan Perubahan

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

function toggleAlasan(select, id) {

    const alasanBox =
        document.getElementById('alasanBox' + id);

    const alasanTextarea =
        document.getElementById('alasan' + id);


    if (!alasanBox || !alasanTextarea) {
        return;
    }


    if (select.value === 'ditolak') {

        /*
         * Tampilkan alasan
         */

        alasanBox.style.display = 'block';


        /*
         * Wajib diisi
         */

        alasanTextarea.required = true;


    } else {

        /*
         * Sembunyikan alasan
         */

        alasanBox.style.display = 'none';


        /*
         * Tidak wajib
         */

        alasanTextarea.required = false;


        /*
         * Kosongkan alasan
         */

        alasanTextarea.value = '';

    }

}


/*
|--------------------------------------------------------------------------
| Jalankan saat halaman dibuka
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const selects =
            document.querySelectorAll('.status-select');


        selects.forEach(
            function (select) {

                const onchange =
                    select.getAttribute('onchange');


                if (!onchange) {
                    return;
                }


                const match =
                    onchange.match(/\d+/);


                if (match) {

                    toggleAlasan(
                        select,
                        match[0]
                    );

                }

            }
        );

    }
);

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>