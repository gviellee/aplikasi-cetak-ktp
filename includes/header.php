<?php
// =========================================================
// HEADER.PHP
// =========================================================
// Pastikan file yang memanggil header.php sudah:
// require_once config.php
// require_once includes/functions.php
// =========================================================

$bp = base_path();
$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Ambil nama user dari session
$namaUser = $_SESSION['nama_lengkap']
    ?? $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'User';

$roleUser = $_SESSION['role'] ?? '';
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
        <?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>
        <?= e(APP_NAME) ?>
    </title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --navy-950: #0b0a1f;
            --navy-900: #131233;
            --navy-800: #1a1847;
            --navy-700: #241f5c;

            --purple-300: #c4b5fd;
            --purple-400: #a78bfa;
            --purple-500: #8b5cf6;
            --purple-600: #7c3aed;

            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #22d3ee;

            --muted: #6b7280;
            --border: #e6e4f5;
            --bg: #f6f5fc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            background: var(--bg);
            color: #1e1b3a;
            margin: 0;
        }

        a {
            text-decoration: none;
        }

        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            background:
                linear-gradient(
                    180deg,
                    var(--navy-950) 0%,
                    var(--navy-900) 55%,
                    var(--navy-800) 100%
                );

            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 265px;
            color: white;
            overflow-y: auto;
            z-index: 999;

            display: flex;
            flex-direction: column;

            box-shadow:
                4px 0 30px rgba(11,10,31,.25);
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(196,181,253,.25);
            border-radius: 3px;
        }

        /* =====================================================
           BRAND
        ===================================================== */

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 26px 22px;

            border-bottom:
                1px solid rgba(196,181,253,.12);

            position: relative;
        }

        .sidebar-brand::after {
            content: '';

            position: absolute;
            bottom: -1px;
            left: 22px;
            right: 22px;
            height: 1px;

            background:
                linear-gradient(
                    90deg,
                    transparent,
                    var(--purple-400),
                    transparent
                );
        }

        .sidebar-brand .icon-box {
            width: 42px;
            height: 42px;
            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    var(--purple-500),
                    var(--purple-300)
                );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 1.2rem;
            flex-shrink: 0;

            box-shadow:
                0 6px 18px rgba(139,92,246,.45);
        }

        .sidebar-brand .brand-text {
            font-weight: 800;
            font-size: 1.02rem;
            line-height: 1.25;

            background:
                linear-gradient(
                    90deg,
                    #fff,
                    var(--purple-300)
                );

            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-brand .brand-text small {
            display: block;
            font-weight: 400;
            font-size: .71rem;

            color: rgba(255,255,255,.5);
            -webkit-text-fill-color: rgba(255,255,255,.5);

            margin-top: 2px;
        }

        /* =====================================================
           USER
        ===================================================== */

        .sidebar-user {
            padding: 16px 22px;

            border-bottom:
                1px solid rgba(196,181,253,.12);

            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    rgba(139,92,246,.35),
                    rgba(196,181,253,.15)
                );

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 700;
            font-size: .85rem;
            color: #fff;

            border:
                1.5px solid rgba(196,181,253,.4);

            flex-shrink: 0;
        }

        .sidebar-user .u-name {
            font-size: .86rem;
            font-weight: 700;
            color: #fff;
        }

        .sidebar-user .u-role {
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--purple-300);
            font-weight: 700;
        }

        /* =====================================================
           MENU
        ===================================================== */

        .sidebar-nav {
            padding: 16px 12px;
            flex-grow: 1;
        }

        .sidebar-nav .nav-section-title {
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .9px;

            color: rgba(255,255,255,.32);

            padding: 8px 12px;

            font-weight: 700;
        }

        .sidebar-nav a {
            color: rgba(255,255,255,.72);

            display: flex;
            align-items: center;
            gap: 13px;

            padding: 12px 14px;

            border-radius: 12px;

            font-size: .89rem;
            font-weight: 500;

            margin-bottom: 4px;

            transition: all .25s ease;
        }

        .sidebar-nav a i {
            font-size: 1.08rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav a:hover {
            background: rgba(196,181,253,.08);
            color: #fff;
            padding-left: 18px;
        }

        .sidebar-nav a.active {
            background:
                linear-gradient(
                    135deg,
                    var(--purple-600),
                    var(--purple-400)
                );

            color: #fff;

            box-shadow:
                0 6px 18px rgba(139,92,246,.4);

            font-weight: 700;
        }

        /* =====================================================
           LOGOUT
        ===================================================== */

        .sidebar-logout {
            padding: 16px 12px;

            border-top:
                1px solid rgba(196,181,253,.12);
        }

        .sidebar-logout a {
            color: #fca5a5;

            display: flex;
            align-items: center;
            gap: 13px;

            padding: 12px 14px;

            border-radius: 12px;

            font-size: .89rem;
            font-weight: 600;

            transition: all .25s ease;
        }

        .sidebar-logout a:hover {
            background: rgba(239,68,68,.12);
            color: #f87171;
        }

        /* =====================================================
           MAIN
        ===================================================== */

        .main-wrapper {
            margin-left: 265px;
            min-height: 100vh;
        }

        .topbar {
            background: rgba(255,255,255,.85);

            backdrop-filter: blur(10px);

            border-bottom:
                1px solid var(--border);

            padding: 18px 34px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            position: sticky;
            top: 0;
            z-index: 900;
        }

        .topbar .page-title {
            font-weight: 800;
            font-size: 1.1rem;
            margin: 0;

            color: var(--navy-900);
        }

        .topbar .page-subtitle {
            font-size: .78rem;
            color: var(--muted);
        }

        .main-content {
            padding: 30px 34px;
        }

        /* =====================================================
           AUTH
        ===================================================== */

        .auth-bg {
            min-height: 100vh;

            background:
                radial-gradient(
                    circle at 15% 15%,
                    rgba(139,92,246,.55),
                    transparent 42%
                ),

                radial-gradient(
                    circle at 88% 25%,
                    rgba(196,181,253,.35),
                    transparent 38%
                ),

                radial-gradient(
                    circle at 50% 100%,
                    rgba(124,58,237,.4),
                    transparent 50%
                ),

                linear-gradient(
                    160deg,
                    var(--navy-950),
                    var(--navy-800)
                );

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 20px;

            position: relative;
            overflow: hidden;
        }

        /* =====================================================
           CARD
        ===================================================== */

        .card {
            border:
                1px solid rgba(139,92,246,.08);

            box-shadow:
                0 4px 20px rgba(30,27,58,.06);

            border-radius: 16px;

            transition:
                box-shadow .25s ease;
        }

        .card:hover {
            box-shadow:
                0 10px 32px rgba(30,27,58,.1);
        }

        /* =====================================================
           STAT
        ===================================================== */

        .stat-card {
            border-radius: 16px;
            padding: 22px 24px;

            border:
                1px solid var(--border);

            background: #fff;

            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 1.25rem;
            color: #fff;

            margin-bottom: 14px;
        }

        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 800;

            color: var(--navy-900);

            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: .79rem;
            color: var(--muted);

            font-weight: 600;

            margin-top: 7px;
        }

        /* =====================================================
           FORM
        ===================================================== */

        .form-label {
            font-weight: 700;
            color: var(--navy-900);
            font-size: .88rem;
        }

        .form-control,
        .form-select {
            border:
                1.5px solid var(--border);

            border-radius: 11px;

            padding: 11px 15px;

            font-size: .92rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--purple-500);

            box-shadow:
                0 0 0 4px rgba(139,92,246,.12);
        }

        .form-text {
            font-size: .78rem;
        }

        /* =====================================================
           BUTTON
        ===================================================== */

        .btn-primary {
            background:
                linear-gradient(
                    135deg,
                    var(--purple-600),
                    var(--purple-400)
                );

            border: none;

            font-weight: 700;

            padding: 11px 24px;

            border-radius: 11px;

            box-shadow:
                0 6px 18px rgba(139,92,246,.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            color: var(--purple-600);
            border-color: var(--purple-400);

            border-radius: 11px;
            font-weight: 600;
        }

        .btn-outline-primary:hover {
            background: var(--purple-600);
            border-color: var(--purple-600);
        }

        /* =====================================================
           ALERT
        ===================================================== */

        .alert {
            border-radius: 13px;
            border: none;

            padding: 15px 19px;

            font-weight: 500;
            font-size: .9rem;
        }

        .alert-success {
            background: rgba(16,185,129,.1);
            color: #047857;
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: rgba(239,68,68,.1);
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        .alert-warning {
            background: rgba(245,158,11,.1);
            color: #92400e;
            border-left: 4px solid var(--warning);
        }

        /* =====================================================
           STATUS
        ===================================================== */

        .status-badge {
            padding: 7px 14px;
            border-radius: 20px;

            font-weight: 700;
            font-size: .74rem;

            display: inline-block;
            letter-spacing: .3px;
        }

        .status-menunggu {
            background: rgba(245,158,11,.12);
            color: #92400e;
            border: 1px solid rgba(245,158,11,.3);
        }

        .status-diproses {
            background: rgba(139,92,246,.12);
            color: #6d28d9;
            border: 1px solid rgba(139,92,246,.3);
        }

        .status-selesai {
            background: rgba(16,185,129,.12);
            color: #047857;
            border: 1px solid rgba(16,185,129,.3);
        }

        .status-ditolak {
            background: rgba(239,68,68,.12);
            color: #991b1b;
            border: 1px solid rgba(239,68,68,.3);
        }

        /* =====================================================
           TABLE
        ===================================================== */

        .table {
            font-size: .89rem;
        }

        .table thead th {
            background: #f7f6fd;
            color: var(--navy-900);

            font-weight: 700;
            font-size: .77rem;

            text-transform: uppercase;
            letter-spacing: .4px;

            border-bottom:
                1px solid var(--border);

            padding: 13px 15px;
        }

        .table tbody td {
            padding: 13px 15px;
            vertical-align: middle;
        }

        /* =====================================================
           MODAL
        ===================================================== */

        .modal-content {
            border: none;
            border-radius: 18px;
            overflow: hidden;
        }

        .modal-header {
            background: #f7f6fd;
            border-bottom:
                1px solid var(--border);
        }

        .modal-title {
            font-weight: 700;
            color: var(--navy-900);
        }

        /* =====================================================
           HERO
        ===================================================== */

        .hero-banner {
            background:
                linear-gradient(
                    120deg,
                    var(--navy-900),
                    var(--purple-600),
                    var(--purple-400)
                );

            border: none;

            position: relative;
            overflow: hidden;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .sidebar {
                width: 225px;
            }

            .main-wrapper {
                margin-left: 225px;
            }
        }

        @media (max-width: 768px) {

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .main-content {
                padding: 20px;
            }

            .topbar {
                padding: 15px 20px;
            }
        }

    </style>

</head>


<body>

<?php if (is_logged_in()): ?>

<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">

    <!-- BRAND -->
    <div class="sidebar-brand">

        <div class="icon-box">
            <i class="bi bi-person-vcard-fill"></i>
        </div>

        <div class="brand-text">

            KTP Ludow

            <small>
                Pengajuan Cetak KTP
            </small>

        </div>

    </div>


    <!-- USER -->
    <div class="sidebar-user">

        <div class="avatar">

            <?= strtoupper(
                substr($namaUser, 0, 1)
            ) ?>

        </div>

        <div>

            <div class="u-name">
                <?= e($namaUser) ?>
            </div>

            <div class="u-role">

                <?= is_admin()
                    ? 'Administrator'
                    : 'Pemohon'
                ?>

            </div>

        </div>

    </div>


    <!-- MENU -->
    <nav class="sidebar-nav">

        <div class="nav-section-title">
            Menu Utama
        </div>


        <?php if (is_admin()): ?>

            <!-- DASHBOARD -->
            <a
                href="<?= $bp ?>admin/dashboard.php"
                class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>


            <!-- DAFTAR PEMOHON -->
            <a
                href="<?= $bp ?>admin/pemohon.php"
                class="<?= $currentPage === 'pemohon.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-people-fill"></i>
                <span>Daftar Pemohon</span>
            </a>


            <!-- AJUKAN CETAK KTP ADMIN -->
            <a
                href="<?= $bp ?>admin/form-pengajuan.php"
                class="<?= $currentPage === 'form-pengajuan.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-file-earmark-plus-fill"></i>
                <span>Ajukan Cetak KTP</span>
            </a>


            <!-- TAMBAH USER -->
            <a
                href="<?= $bp ?>admin/tambah_user.php"
                class="<?= $currentPage === 'tambah_user.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-person-plus-fill"></i>
                <span>Tambah User</span>
            </a>


        <?php else: ?>

            <!-- DASHBOARD PEMOHON -->
            <a
                href="<?= $bp ?>user/dashboard.php"
                class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>


            <!-- AJUKAN CETAK KTP -->
            <a
                href="<?= $bp ?>user/pengajuan.php"
                class="<?= $currentPage === 'pengajuan.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-file-earmark-plus-fill"></i>
                <span>Ajukan Cetak KTP</span>
            </a>


            <!-- CEK STATUS -->
            <a
                href="<?= $bp ?>user/status.php"
                class="<?= $currentPage === 'status.php' ? 'active' : '' ?>"
            >
                <i class="bi bi-list-check"></i>
                <span>Cek Status</span>
            </a>

        <?php endif; ?>

    </nav>


    <!-- LOGOUT -->
    <div class="sidebar-logout">

        <a href="<?= $bp ?>logout.php">

            <i class="bi bi-box-arrow-right"></i>

            <span>Logout</span>

        </a>

    </div>

</aside>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="main-wrapper">

    <div class="topbar">

        <div>

            <p class="page-title mb-0">

                <?= isset($pageTitle)
                    ? e($pageTitle)
                    : 'Dashboard'
                ?>

            </p>

            <span class="page-subtitle">
                Sistem Pengajuan Cetak KTP Ludow
            </span>

        </div>

        <div class="d-flex align-items-center gap-2 d-md-none">

            <a
                href="<?= $bp ?>logout.php"
                class="btn btn-sm btn-outline-danger"
            >
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>

        </div>

    </div>


    <div class="main-content">


<?php else: ?>

<!-- =========================================================
     LOGIN
========================================================= -->

<div class="auth-bg">

<?php endif; ?>