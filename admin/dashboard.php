<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$filter = $_GET['filter'] ?? 'all';

$filterAllowed = [
    'all',
    'pending',
    'selesai',
    'ditolak'
];

if (!in_array($filter, $filterAllowed, true)) {
    $filter = 'all';
}


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$total = $pdo
    ->query("
        SELECT COUNT(*)
        FROM pengajuan_ktp
    ")
    ->fetchColumn();


$menunggu = $pdo
    ->query("
        SELECT COUNT(*)
        FROM pengajuan_ktp
        WHERE status = 'pending'
    ")
    ->fetchColumn();


$selesai = $pdo
    ->query("
        SELECT COUNT(*)
        FROM pengajuan_ktp
        WHERE status = 'selesai'
    ")
    ->fetchColumn();


$ditolak = $pdo
    ->query("
        SELECT COUNT(*)
        FROM pengajuan_ktp
        WHERE status = 'ditolak'
    ")
    ->fetchColumn();


$totalUser = $pdo
    ->query("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'user'
    ")
    ->fetchColumn();


/*
|--------------------------------------------------------------------------
| DATA PENGAJUAN SESUAI FILTER
|--------------------------------------------------------------------------
*/

if ($filter === 'all') {

    $stmt = $pdo->query("
        SELECT
            p.*,
            u.username
        FROM pengajuan_ktp p
        LEFT JOIN users u
            ON u.id = p.user_id
        ORDER BY p.created_at DESC
    ");

} else {

    $stmt = $pdo->prepare("
        SELECT
            p.*,
            u.username
        FROM pengajuan_ktp p
        LEFT JOIN users u
            ON u.id = p.user_id
        WHERE p.status = ?
        ORDER BY p.created_at DESC
    ");

    $stmt->execute([
        $filter
    ]);
}


$pengajuan = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| JUDUL TABEL SESUAI FILTER
|--------------------------------------------------------------------------
*/

switch ($filter) {

    case 'pending':

        $judulPengajuan = 'Pengajuan Menunggu';

        $deskripsiPengajuan =
            'Daftar pengajuan yang sedang menunggu diproses.';

        $iconPengajuan = 'hourglass-split';

        break;


    case 'selesai':

        $judulPengajuan = 'Pengajuan Selesai';

        $deskripsiPengajuan =
            'Daftar pengajuan KTP yang telah selesai.';

        $iconPengajuan = 'check-circle';

        break;


    case 'ditolak':

        $judulPengajuan = 'Pengajuan Ditolak';

        $deskripsiPengajuan =
            'Daftar pengajuan KTP yang ditolak.';

        $iconPengajuan = 'x-circle';

        break;


    default:

        $judulPengajuan = 'Semua Pengajuan';

        $deskripsiPengajuan =
            'Daftar seluruh pengajuan cetak KTP.';

        $iconPengajuan = 'folder2-open';

        break;
}


/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Dashboard Admin';

require_once __DIR__ . '/../includes/header.php';

?>


<style>

/* =========================================================
   STATISTIK
========================================================= */

.dashboard-stats {

    width: 100%;

    display: flex;

    justify-content: center;

    align-items: stretch;

    gap: 18px;

    margin: 0 auto 28px auto;

    flex-wrap: wrap;
}


.dashboard-stat-item {

    width: 180px;

    flex: 0 0 180px;
}


/* =========================================================
   LINK KOTAK
========================================================= */

.stat-link {

    display: block;

    text-decoration: none;

    color: inherit;

    height: 100%;
}


/* =========================================================
   STAT CARD
========================================================= */

.stat-card {

    width: 100%;

    min-height: 160px;

    background: #ffffff;

    border: 1px solid #e4e3f4;

    border-radius: 16px;

    padding: 22px 18px;

    display: flex;

    flex-direction: column;

    align-items: flex-start;

    justify-content: center;

    box-shadow:
        0 4px 14px rgba(31, 41, 55, 0.04);

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;
}


.stat-card:hover {

    transform: translateY(-4px);

    box-shadow:
        0 10px 25px rgba(31, 41, 55, 0.10);

}


.stat-card.active {

    border: 2px solid #7c3aed;

    box-shadow:
        0 8px 25px rgba(124, 58, 237, 0.15);

    transform: translateY(-3px);

}


/* =========================================================
   ICON
========================================================= */

.stat-icon {

    width: 46px;

    height: 46px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #ffffff;

    font-size: 21px;

    margin-bottom: 14px;
}


/* =========================================================
   ANGKA
========================================================= */

.stat-value {

    font-size: 28px;

    line-height: 1;

    font-weight: 700;

    color: #111827;

    margin-bottom: 7px;
}


/* =========================================================
   LABEL
========================================================= */

.stat-label {

    font-size: 14px;

    color: #64748b;

    font-weight: 500;
}


/* =========================================================
   CARD UMUM
========================================================= */

.dashboard-box {

    background: #ffffff;

    border: 1px solid #eceaf7;

    border-radius: 16px;

    box-shadow:
        0 4px 15px rgba(31, 41, 55, 0.04);
}


/* =========================================================
   HEADER TABEL
========================================================= */

.pengajuan-title {

    display: flex;

    align-items: center;

    gap: 12px;
}


.pengajuan-title-icon {

    width: 42px;

    height: 42px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #312e81,
            #7c3aed
        );

    font-size: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.search-box {

    position: relative;

    width: 100%;

    margin-bottom: 18px;
}


.search-box-icon {

    position: absolute;

    left: 16px;

    top: 50%;

    transform: translateY(-50%);

    color: #7c3aed;

    font-size: 18px;

    pointer-events: none;

    z-index: 2;
}


.search-input {

    width: 100%;

    height: 48px;

    border: 1px solid #ddd6fe;

    border-radius: 12px;

    padding:
        10px
        45px
        10px
        46px;

    font-size: 14px;

    color: #1f2937;

    background: #ffffff;

    outline: none;

    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}


.search-input::placeholder {

    color: #94a3b8;
}


.search-input:focus {

    border-color: #7c3aed;

    box-shadow:
        0 0 0 3px rgba(124, 58, 237, 0.10);
}


.search-clear {

    position: absolute;

    right: 12px;

    top: 50%;

    transform: translateY(-50%);

    width: 28px;

    height: 28px;

    border: none;

    border-radius: 50%;

    background: #f1f5f9;

    color: #64748b;

    display: none;

    align-items: center;

    justify-content: center;

    cursor: pointer;

    transition: .2s ease;
}


.search-clear:hover {

    background: #ede9fe;

    color: #7c3aed;
}


.search-clear.show {

    display: flex;
}


/* =========================================================
   SEARCH RESULT INFO
========================================================= */

.search-result-info {

    display: none;

    padding: 10px 14px;

    margin-bottom: 15px;

    border-radius: 10px;

    background: #f5f3ff;

    color: #6d28d9;

    font-size: 13px;
}


.search-result-info.show {

    display: block;
}


.search-result-info strong {

    font-weight: 700;
}


/* =========================================================
   TABLE
========================================================= */

.dashboard-table {

    overflow: hidden;

    border-radius: 12px;
}


.dashboard-table thead th {

    background: #f7f6fc;

    color: #111827;

    font-size: 13px;

    font-weight: 700;

    padding: 15px;

    white-space: nowrap;

    border-bottom: 1px solid #e5e7eb;
}


.dashboard-table tbody td {

    padding: 15px;

    font-size: 14px;

    border-bottom: 1px solid #f1f1f5;
}


.dashboard-table tbody tr:last-child td {

    border-bottom: none;
}


/* =========================================================
   ALASAN DITOLAK
========================================================= */

.alasan-ditolak {

    max-width: 280px;

    color: #dc2626;

    font-size: 13px;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    padding: 55px 20px;

    text-align: center;

    color: #64748b;
}


.empty-state-icon {

    width: 65px;

    height: 65px;

    margin: 0 auto 15px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f5f3ff;

    color: #7c3aed;

    font-size: 28px;
}


/* =========================================================
   NO SEARCH RESULT
========================================================= */

.search-empty-row {

    display: none;
}


.search-empty-row.show {

    display: table-row;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .dashboard-stat-item {

        width: 170px;

        flex-basis: 170px;
    }

}


@media (max-width: 768px) {

    .dashboard-stats {

        gap: 14px;
    }


    .dashboard-stat-item {

        width: calc(50% - 7px);

        flex-basis: calc(50% - 7px);
    }

}


@media (max-width: 480px) {

    .dashboard-stat-item {

        width: 100%;

        flex-basis: 100%;
    }

}

</style>


<!-- =========================================================
     HERO
========================================================= -->

<div class="card hero-banner p-4 p-md-5 mb-4">

    <div style="position:relative; z-index:1;">

        <div
            class="d-flex
                   flex-column
                   flex-md-row
                   align-items-md-center
                   justify-content-between
                   gap-3"
        >

            <div>

                <h5 class="fw-bold text-white mb-1">

                    <i
                        class="bi bi-speedometer2 me-2"
                    ></i>

                    Dashboard Administrator

                </h5>


                <p class="text-white-50 mb-0 small">

                    Pantau seluruh aktivitas
                    pengajuan cetak KTP Ludow.

                </p>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     STATISTIK
========================================================= -->

<div class="dashboard-stats">


    <!-- TOTAL PENGAJUAN -->

    <div class="dashboard-stat-item">

        <a
            href="dashboard.php?filter=all"
            class="stat-link"
        >

            <div
                class="stat-card
                <?= $filter === 'all' ? 'active' : '' ?>"
            >

                <div
                    class="stat-icon"
                    style="
                        background:
                        linear-gradient(
                            135deg,
                            #312e81,
                            #1d4ed8
                        );
                    "
                >

                    <i class="bi bi-folder2-open"></i>

                </div>


                <div class="stat-value">

                    <?= (int) $total ?>

                </div>


                <div class="stat-label">

                    Total Pengajuan

                </div>

            </div>

        </a>

    </div>



    <!-- MENUNGGU -->

    <div class="dashboard-stat-item">

        <a
            href="dashboard.php?filter=pending"
            class="stat-link"
        >

            <div
                class="stat-card
                <?= $filter === 'pending' ? 'active' : '' ?>"
            >

                <div
                    class="stat-icon"
                    style="
                        background:
                        linear-gradient(
                            135deg,
                            #f59e0b,
                            #fbbf24
                        );
                    "
                >

                    <i class="bi bi-hourglass-split"></i>

                </div>


                <div class="stat-value">

                    <?= (int) $menunggu ?>

                </div>


                <div class="stat-label">

                    Menunggu

                </div>

            </div>

        </a>

    </div>



    <!-- SELESAI -->

    <div class="dashboard-stat-item">

        <a
            href="dashboard.php?filter=selesai"
            class="stat-link"
        >

            <div
                class="stat-card
                <?= $filter === 'selesai' ? 'active' : '' ?>"
            >

                <div
                    class="stat-icon"
                    style="
                        background:
                        linear-gradient(
                            135deg,
                            #10b981,
                            #34d399
                        );
                    "
                >

                    <i class="bi bi-check-circle"></i>

                </div>


                <div class="stat-value">

                    <?= (int) $selesai ?>

                </div>


                <div class="stat-label">

                    Selesai

                </div>

            </div>

        </a>

    </div>



    <!-- DITOLAK -->

    <div class="dashboard-stat-item">

        <a
            href="dashboard.php?filter=ditolak"
            class="stat-link"
        >

            <div
                class="stat-card
                <?= $filter === 'ditolak' ? 'active' : '' ?>"
            >

                <div
                    class="stat-icon"
                    style="
                        background:
                        linear-gradient(
                            135deg,
                            #ef4444,
                            #f87171
                        );
                    "
                >

                    <i class="bi bi-x-circle"></i>

                </div>


                <div class="stat-value">

                    <?= (int) $ditolak ?>

                </div>


                <div class="stat-label">

                    Ditolak

                </div>

            </div>

        </a>

    </div>



    <!-- TOTAL PEMOHON -->

    <div class="dashboard-stat-item">

        <a
            href="pemohon.php"
            class="stat-link"
        >

            <div class="stat-card">

                <div
                    class="stat-icon"
                    style="
                        background:
                        linear-gradient(
                            135deg,
                            #1a1847,
                            #312e81
                        );
                    "
                >

                    <i class="bi bi-people"></i>

                </div>


                <div class="stat-value">

                    <?= (int) $totalUser ?>

                </div>


                <div class="stat-label">

                    Total Pemohon

                </div>

            </div>

        </a>

    </div>


</div>



<!-- =========================================================
     AKSI CEPAT
========================================================= -->

<div class="card dashboard-box p-4 mb-4">

    <div
        class="d-flex
               align-items-center
               justify-content-between
               flex-wrap
               gap-3"
    >

        <div>

            <h6 class="fw-bold mb-1">

                <i
                    class="bi bi-lightning-charge-fill me-1"
                    style="color:#f59e0b;"
                ></i>

                Aksi Cepat

            </h6>


            <p class="text-muted small mb-0">

                Kelola pengajuan KTP dengan cepat.

            </p>

        </div>


        <div class="d-flex gap-2 flex-wrap">

            <a
                href="form-pengajuan.php"
                class="btn btn-primary"
            >

                <i
                    class="bi bi-file-earmark-plus-fill me-1"
                ></i>

                Ajukan Cetak KTP

            </a>


            <a
                href="pemohon.php"
                class="btn btn-outline-primary"
            >

                <i
                    class="bi bi-people-fill me-1"
                ></i>

                Daftar Pemohon

            </a>

        </div>

    </div>

</div>



<!-- =========================================================
     DATA PENGAJUAN
========================================================= -->

<div class="card dashboard-box p-4">


    <!-- HEADER -->

    <div
        class="d-flex
               align-items-center
               justify-content-between
               flex-wrap
               gap-3
               mb-4"
    >


        <div class="pengajuan-title">

            <div class="pengajuan-title-icon">

                <i
                    class="bi bi-<?= e($iconPengajuan) ?>"
                ></i>

            </div>


            <div>

                <h6 class="fw-bold mb-1">

                    <?= e($judulPengajuan) ?>

                </h6>


                <p class="text-muted small mb-0">

                    <?= e($deskripsiPengajuan) ?>

                </p>

            </div>

        </div>


        <div>

            <span
                class="badge rounded-pill
                       text-bg-light
                       border
                       px-3
                       py-2"
                id="dataCount"
            >

                <?= count($pengajuan) ?> Data

            </span>

        </div>

    </div>



    <!-- =====================================================
         SEARCH REALTIME
    ====================================================== -->

    <div class="search-box">

        <i class="bi bi-search search-box-icon"></i>


        <input
            type="text"
            id="searchInput"
            class="search-input"
            placeholder="Cari nama pemohon atau NIK..."
            autocomplete="off"
        >


        <button
            type="button"
            id="clearSearch"
            class="search-clear"
            title="Hapus pencarian"
        >

            <i class="bi bi-x"></i>

        </button>

    </div>



    <!-- =====================================================
         INFORMASI HASIL SEARCH
    ====================================================== -->

    <div
        id="searchResultInfo"
        class="search-result-info"
    >

        <i class="bi bi-search me-1"></i>

        Menampilkan

        <strong id="searchResultCount">
            0
        </strong>

        data untuk pencarian:

        <strong id="searchKeyword">
            -
        </strong>

    </div>



    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="table-responsive dashboard-table">

        <table class="table table-hover align-middle mb-0">

            <thead>

                <tr>

                    <th>NO</th>

                    <th>NIK</th>

                    <th>NAMA PEMOHON</th>

                    <th>AKUN</th>

                    <th>STATUS</th>


                    <?php if ($filter === 'ditolak'): ?>

                        <th>ALASAN PENOLAKAN</th>

                    <?php endif; ?>


                    <th>TANGGAL</th>

                </tr>

            </thead>


            <tbody id="pengajuanTableBody">


            <?php if (!$pengajuan): ?>


                <tr id="initialEmptyRow">

                    <td
                        colspan="<?= $filter === 'ditolak'
                            ? '7'
                            : '6'
                        ?>"
                    >

                        <div class="empty-state">

                            <div class="empty-state-icon">

                                <i class="bi bi-inbox"></i>

                            </div>


                            <h6 class="fw-bold">

                                Belum Ada Data

                            </h6>


                            <p class="small mb-0">

                                Tidak ada pengajuan pada
                                kategori ini.

                            </p>

                        </div>

                    </td>

                </tr>


            <?php else: ?>


                <?php

                $no = 1;

                foreach ($pengajuan as $row):

                    /*
                    |--------------------------------------------------------------------------
                    | DATA UNTUK SEARCH JAVASCRIPT
                    |--------------------------------------------------------------------------
                    */

                    $nikSearch =
                        strtolower(
                            trim(
                                (string)
                                ($row['nik'] ?? '')
                            )
                        );

                    $namaSearch =
                        strtolower(
                            trim(
                                (string)
                                ($row['nama_pemohon'] ?? '')
                            )
                        );

                    $usernameSearch =
                        strtolower(
                            trim(
                                (string)
                                ($row['username'] ?? '')
                            )
                        );

                ?>


                    <tr
                        class="pengajuan-row"
                        data-search="<?= e(
                            $nikSearch
                            . ' '
                            . $namaSearch
                            . ' '
                            . $usernameSearch
                        ) ?>"
                    >


                        <!-- NO -->

                        <td class="nomor-data">

                            <?= $no++ ?>

                        </td>



                        <!-- NIK -->

                        <td class="fw-semibold">

                            <?= e(
                                $row['nik']
                            ) ?>

                        </td>



                        <!-- NAMA -->

                        <td>

                            <?= e(
                                $row['nama_pemohon']
                            ) ?>

                        </td>



                        <!-- AKUN -->

                        <td>

                            <?= e(
                                $row['username'] ?? '-'
                            ) ?>

                        </td>



                        <!-- STATUS -->

                        <td>

                            <?= status_badge(
                                $row['status']
                            ) ?>

                        </td>



                        <!-- ALASAN PENOLAKAN -->

                        <?php if ($filter === 'ditolak'): ?>

                            <td>

                                <?php

                                $alasan =
                                    $row['alasan_penolakan']
                                    ?? '';

                                ?>


                                <?php if (
                                    trim($alasan) !== ''
                                ): ?>

                                    <div
                                        class="alasan-ditolak"
                                    >

                                        <i
                                            class="bi
                                                   bi-exclamation-circle
                                                   me-1"
                                        ></i>

                                        <?= e($alasan) ?>

                                    </div>

                                <?php else: ?>

                                    <span class="text-muted">

                                        Tidak ada alasan.

                                    </span>

                                <?php endif; ?>

                            </td>

                        <?php endif; ?>



                        <!-- TANGGAL -->

                        <td class="text-muted small">

                            <?= e(
                                date(
                                    'd M Y, H:i',
                                    strtotime(
                                        $row['created_at']
                                    )
                                )
                            ) ?>

                        </td>


                    </tr>


                <?php endforeach; ?>


                <!-- =================================================
                     BARIS JIKA SEARCH TIDAK MENEMUKAN DATA
                ================================================== -->

                <tr
                    id="searchEmptyRow"
                    class="search-empty-row"
                >

                    <td
                        colspan="<?= $filter === 'ditolak'
                            ? '7'
                            : '6'
                        ?>"
                    >

                        <div class="empty-state">

                            <div class="empty-state-icon">

                                <i class="bi bi-search"></i>

                            </div>


                            <h6 class="fw-bold">

                                Data Tidak Ditemukan

                            </h6>


                            <p class="small mb-0">

                                Tidak ada nama pemohon,
                                NIK, atau akun yang sesuai
                                dengan pencarian.

                            </p>

                        </div>

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>

        </table>

    </div>


</div>



<!-- =========================================================
     REALTIME SEARCH JAVASCRIPT
========================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput =
        document.getElementById('searchInput');

    const clearSearch =
        document.getElementById('clearSearch');

    const resultInfo =
        document.getElementById('searchResultInfo');

    const resultCount =
        document.getElementById('searchResultCount');

    const searchKeyword =
        document.getElementById('searchKeyword');

    const dataCount =
        document.getElementById('dataCount');

    const searchEmptyRow =
        document.getElementById('searchEmptyRow');

    const rows =
        document.querySelectorAll('.pengajuan-row');


    /*
    |--------------------------------------------------------------------------
    | FUNGSI SEARCH
    |--------------------------------------------------------------------------
    */

    function doSearch() {

        const keyword =
            searchInput.value
                .toLowerCase()
                .trim();


        let visibleCount = 0;


        /*
        |--------------------------------------------------------------------------
        | JIKA SEARCH KOSONG
        |--------------------------------------------------------------------------
        */

        if (keyword === '') {

            rows.forEach(function (row) {

                row.style.display = '';

            });


            /*
            | Nomor kembali normal
            */

            let nomor = 1;

            rows.forEach(function (row) {

                const nomorCell =
                    row.querySelector('.nomor-data');

                if (nomorCell) {

                    nomorCell.textContent =
                        nomor++;

                }

            });


            if (searchEmptyRow) {

                searchEmptyRow.classList.remove(
                    'show'
                );

            }


            resultInfo.classList.remove(
                'show'
            );


            clearSearch.classList.remove(
                'show'
            );


            dataCount.textContent =
                rows.length + ' Data';


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH AKTIF
        |--------------------------------------------------------------------------
        */

        clearSearch.classList.add(
            'show'
        );


        rows.forEach(function (row) {

            const searchableData =
                (
                    row.dataset.search || ''
                ).toLowerCase();


            if (
                searchableData.includes(
                    keyword
                )
            ) {

                row.style.display = '';

                visibleCount++;

            } else {

                row.style.display = 'none';

            }

        });


        /*
        |--------------------------------------------------------------------------
        | UPDATE NOMOR
        |--------------------------------------------------------------------------
        */

        let nomor = 1;

        rows.forEach(function (row) {

            if (
                row.style.display !== 'none'
            ) {

                const nomorCell =
                    row.querySelector(
                        '.nomor-data'
                    );

                if (nomorCell) {

                    nomorCell.textContent =
                        nomor++;

                }

            }

        });


        /*
        |--------------------------------------------------------------------------
        | UPDATE JUMLAH DATA
        |--------------------------------------------------------------------------
        */

        dataCount.textContent =
            visibleCount + ' Data';


        /*
        |--------------------------------------------------------------------------
        | UPDATE INFO SEARCH
        |--------------------------------------------------------------------------
        */

        resultCount.textContent =
            visibleCount;


        searchKeyword.textContent =
            '"' + searchInput.value + '"';


        resultInfo.classList.add(
            'show'
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA HASIL
        |--------------------------------------------------------------------------
        */

        if (searchEmptyRow) {

            if (visibleCount === 0) {

                searchEmptyRow.classList.add(
                    'show'
                );

            } else {

                searchEmptyRow.classList.remove(
                    'show'
                );

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | EVENT SAAT MENGETIK
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener(
        'input',
        doSearch
    );


    /*
    |--------------------------------------------------------------------------
    | TOMBOL CLEAR
    |--------------------------------------------------------------------------
    */

    clearSearch.addEventListener(
        'click',
        function () {

            searchInput.value = '';

            doSearch();

            searchInput.focus();

        }
    );

});

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>