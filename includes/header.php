<?php

// =========================================================
// HEADER.PHP
// SIAK-KTP | Sistem Pengajuan Cetak KTP
// Sidebar bisa Hide / Show
// =========================================================

// Pastikan file yang memanggil header.php sudah:
// require_once config.php
// require_once includes/functions.php
// =========================================================

$bp = rtrim(base_path(), '/') . '/';
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


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         GOOGLE FONT
    ====================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <style>

        /* =====================================================
           ROOT
        ===================================================== */

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

            --sidebar-width: 265px;
            --sidebar-collapsed-width: 78px;

            --transition: .3s ease;
        }


        /* =====================================================
           GLOBAL
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        body {

            font-family:
                'Plus Jakarta Sans',
                'Segoe UI',
                sans-serif;

            background: var(--bg);

            color: #1e1b3a;

            margin: 0;

            overflow-x: hidden;
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

            width: var(--sidebar-width);

            color: white;

            overflow-y: auto;
            overflow-x: hidden;

            z-index: 1100;

            display: flex;
            flex-direction: column;

            box-shadow:
                4px 0 20px rgba(11,10,31,.25);

            transition:
                width var(--transition),
                transform var(--transition);
        }


        .sidebar::-webkit-scrollbar {
            width: 5px;
        }


        .sidebar::-webkit-scrollbar-thumb {

            background:
                rgba(196,181,253,.25);

            border-radius: 3px;
        }


        /* =====================================================
           SIDEBAR COLLAPSED
        ===================================================== */

        body.sidebar-collapsed .sidebar {

            width: var(--sidebar-collapsed-width);
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

            min-height: 94px;

            white-space: nowrap;

            transition:
                padding var(--transition);
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

            opacity: 1;

            transition:
                opacity .2s ease;
        }


        .sidebar-brand .brand-text small {

            display: block;

            font-weight: 400;

            font-size: .71rem;

            color:
                rgba(255,255,255,.5);

            -webkit-text-fill-color:
                rgba(255,255,255,.5);

            margin-top: 2px;
        }


        body.sidebar-collapsed
        .sidebar-brand {

            padding-left: 18px;
            padding-right: 18px;

            justify-content: center;
        }


        body.sidebar-collapsed
        .sidebar-brand .brand-text {

            opacity: 0;

            width: 0;

            overflow: hidden;
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

            white-space: nowrap;

            min-height: 71px;

            transition:
                padding var(--transition);
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

            max-width: 165px;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .sidebar-user .u-role {

            font-size: .66rem;

            text-transform: uppercase;

            letter-spacing: .6px;

            color: var(--purple-300);

            font-weight: 700;
        }


        body.sidebar-collapsed
        .sidebar-user {

            justify-content: center;

            padding-left: 10px;
            padding-right: 10px;
        }


        body.sidebar-collapsed
        .sidebar-user > div:last-child {

            display: none;
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

            color:
                rgba(255,255,255,.32);

            padding: 8px 12px;

            font-weight: 700;

            white-space: nowrap;

            transition:
                opacity .2s ease;
        }


        .sidebar-nav a {

            color:
                rgba(255,255,255,.72);

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 12px 14px;

            border-radius: 12px;

            font-size: .89rem;

            font-weight: 500;

            margin-bottom: 4px;

            transition:
                all .25s ease;

            white-space: nowrap;

            position: relative;
        }


        .sidebar-nav a i {

            font-size: 1.08rem;

            width: 20px;

            min-width: 20px;

            text-align: center;
        }


        .sidebar-nav a span {

            transition:
                opacity .2s ease;
        }


        .sidebar-nav a:hover {

            background:
                rgba(196,181,253,.08);

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


        body.sidebar-collapsed
        .sidebar-nav .nav-section-title {

            opacity: 0;

            height: 0;

            padding-top: 0;
            padding-bottom: 0;

            overflow: hidden;
        }


        body.sidebar-collapsed
        .sidebar-nav a {

            justify-content: center;

            padding-left: 0;
            padding-right: 0;

            gap: 0;
        }


        body.sidebar-collapsed
        .sidebar-nav a:hover {

            padding-left: 0;
        }


        body.sidebar-collapsed
        .sidebar-nav a span {

            opacity: 0;

            width: 0;

            overflow: hidden;
        }


        /* =====================================================
           TOOLTIP SAAT SIDEBAR COLLAPSED
        ===================================================== */

        body.sidebar-collapsed
        .sidebar-nav a::after {

            content: attr(data-title);

            position: absolute;

            left: 68px;

            top: 50%;

            transform:
                translateY(-50%)
                translateX(-5px);

            background:
                var(--navy-950);

            color: #fff;

            padding: 8px 12px;

            border-radius: 8px;

            font-size: .76rem;

            font-weight: 600;

            white-space: nowrap;

            opacity: 0;

            pointer-events: none;

            transition:
                opacity .2s ease,
                transform .2s ease;

            box-shadow:
                0 8px 20px rgba(0,0,0,.25);

            z-index: 1500;
        }


        body.sidebar-collapsed
        .sidebar-nav a:hover::after {

            opacity: 1;

            transform:
                translateY(-50%)
                translateX(0);
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

            transition:
                all .25s ease;

            white-space: nowrap;
        }


        .sidebar-logout a:hover {

            background:
                rgba(239,68,68,.12);

            color: #f87171;
        }


        .sidebar-logout a i {

            font-size: 1.08rem;

            width: 20px;

            min-width: 20px;

            text-align: center;
        }


        body.sidebar-collapsed
        .sidebar-logout a {

            justify-content: center;

            gap: 0;

            padding-left: 0;
            padding-right: 0;
        }


        body.sidebar-collapsed
        .sidebar-logout a span {

            display: none;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main-wrapper {

            margin-left: var(--sidebar-width);

            min-height: 100vh;

            transition:
                margin-left var(--transition);
        }


        body.sidebar-collapsed
        .main-wrapper {

            margin-left:
                var(--sidebar-collapsed-width);
        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {

            background:
                rgba(255,255,255,.85);

            backdrop-filter:
                blur(10px);

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


        /* =====================================================
           SIDEBAR TOGGLE BUTTON
        ===================================================== */

        .sidebar-toggle {

            width: 42px;

            height: 42px;

            border: none;

            border-radius: 11px;

            background:
                rgba(139,92,246,.08);

            color:
                var(--purple-600);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 1.25rem;

            cursor: pointer;

            transition:
                all .25s ease;
        }


        .sidebar-toggle:hover {

            background:
                var(--purple-600);

            color: #fff;

            transform:
                translateY(-1px);

            box-shadow:
                0 5px 15px
                rgba(124,58,237,.25);
        }


        /* =====================================================
           MOBILE OVERLAY
        ===================================================== */

        .sidebar-overlay {

            display: none;

            position: fixed;

            inset: 0;

            background:
                rgba(11,10,31,.55);

            backdrop-filter:
                blur(2px);

            z-index: 1050;

            opacity: 0;

            transition:
                opacity .25s ease;
        }


        /* =====================================================
           MAIN CONTENT
        ===================================================== */

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

            border-color:
                var(--purple-500);

            box-shadow:
                0 0 0 4px
                rgba(139,92,246,.12);
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
                0 6px 18px
                rgba(139,92,246,.35);
        }


        .btn-primary:hover {

            transform:
                translateY(-2px);
        }


        .btn-outline-primary {

            color:
                var(--purple-600);

            border-color:
                var(--purple-400);

            border-radius: 11px;

            font-weight: 600;
        }


        .btn-outline-primary:hover {

            background:
                var(--purple-600);

            border-color:
                var(--purple-600);
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

            background:
                rgba(16,185,129,.1);

            color: #047857;

            border-left:
                4px solid var(--success);
        }


        .alert-danger {

            background:
                rgba(239,68,68,.1);

            color: #991b1b;

            border-left:
                4px solid var(--danger);
        }


        .alert-warning {

            background:
                rgba(245,158,11,.1);

            color: #92400e;

            border-left:
                4px solid var(--warning);
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

            background:
                rgba(245,158,11,.12);

            color: #92400e;

            border:
                1px solid rgba(245,158,11,.3);
        }


        .status-diproses {

            background:
                rgba(139,92,246,.12);

            color: #6d28d9;

            border:
                1px solid rgba(139,92,246,.3);
        }


        .status-selesai {

            background:
                rgba(16,185,129,.12);

            color: #047857;

            border:
                1px solid rgba(16,185,129,.3);
        }


        .status-ditolak {

            background:
                rgba(239,68,68,.12);

            color: #991b1b;

            border:
                1px solid rgba(239,68,68,.3);
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
           TABLET
        ===================================================== */

        @media (max-width: 900px) {

            :root {

                --sidebar-width: 225px;
            }


            .main-content {

                padding: 25px;
            }


            .topbar {

                padding: 16px 25px;
            }

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .sidebar {

                width: 265px;

                transform:
                    translateX(-100%);

                box-shadow:
                    8px 0 30px
                    rgba(11,10,31,.35);
            }


            .main-wrapper {

                margin-left: 0 !important;
            }


            body.sidebar-mobile-open
            .sidebar {

                transform:
                    translateX(0);
            }


            body.sidebar-mobile-open
            .sidebar-overlay {

                display: block;

                opacity: 1;
            }


            body.sidebar-collapsed
            .sidebar {

                width: 265px;

                transform:
                    translateX(-100%);
            }


            body.sidebar-collapsed
            .sidebar-brand {

                padding:
                    26px 22px;

                justify-content: flex-start;
            }


            body.sidebar-collapsed
            .sidebar-brand .brand-text {

                opacity: 1;

                width: auto;
            }


            body.sidebar-collapsed
            .sidebar-user {

                justify-content: flex-start;

                padding:
                    16px 22px;
            }


            body.sidebar-collapsed
            .sidebar-user > div:last-child {

                display: block;
            }


            body.sidebar-collapsed
            .sidebar-nav .nav-section-title {

                opacity: 1;

                height: auto;

                padding:
                    8px 12px;
            }


            body.sidebar-collapsed
            .sidebar-nav a {

                justify-content: flex-start;

                padding:
                    12px 14px;

                gap: 13px;
            }


            body.sidebar-collapsed
            .sidebar-nav a span {

                opacity: 1;

                width: auto;
            }


            body.sidebar-collapsed
            .sidebar-nav a:hover {

                padding-left: 18px;
            }


            body.sidebar-collapsed
            .sidebar-logout a {

                justify-content: flex-start;

                gap: 13px;

                padding:
                    12px 14px;
            }


            body.sidebar-collapsed
            .sidebar-logout a span {

                display: inline;
            }


            body.sidebar-collapsed
            .sidebar-nav a::after {

                display: none;
            }


            .main-content {

                padding: 20px;
            }


            .topbar {

                padding:
                    15px 20px;
            }


            .topbar .page-subtitle {

                display: none;
            }


            .sidebar-toggle {

                width: 40px;

                height: 40px;

                font-size: 1.15rem;
            }

        }


        /* =====================================================
           VERY SMALL SCREEN
        ===================================================== */

        @media (max-width: 480px) {

            .topbar {

                padding:
                    13px 15px;
            }


            .topbar .page-title {

                font-size: 1rem;
            }


            .main-content {

                padding:
                    15px;
            }

        }

    </style>

</head>


<body>


<?php if (is_logged_in()): ?>


<!-- =========================================================
     SIDEBAR OVERLAY
========================================================= -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar" id="sidebar">


    <!-- =====================================================
         BRAND
    ====================================================== -->

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


    <!-- =====================================================
         USER
    ====================================================== -->

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


    <!-- =====================================================
         MENU
    ====================================================== -->

    <nav class="sidebar-nav">


        <div class="nav-section-title">

            Menu Utama

        </div>


        <?php if (is_admin()): ?>


            <!-- =================================================
                 DASHBOARD ADMIN
            ================================================== -->

            <a
                href="<?= $bp ?>admin/dashboard.php"
                class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
                data-title="Dashboard"
            >

                <i class="bi bi-grid-1x2-fill"></i>

                <span>
                    Dashboard
                </span>

            </a>


            <!-- =================================================
                 DAFTAR PEMOHON
            ================================================== -->

            <a
                href="<?= $bp ?>admin/pemohon.php"
                class="<?= $currentPage === 'pemohon.php' ? 'active' : '' ?>"
                data-title="Daftar Pemohon"
            >

                <i class="bi bi-people-fill"></i>

                <span>
                    Daftar Pemohon
                </span>

            </a>


            <!-- =================================================
                 AJUKAN CETAK KTP ADMIN
            ================================================== -->

            <a
                href="<?= $bp ?>admin/form-pengajuan.php"
                class="<?= $currentPage === 'form-pengajuan.php' ? 'active' : '' ?>"
                data-title="Ajukan Cetak KTP"
            >

                <i class="bi bi-file-earmark-plus-fill"></i>

                <span>
                    Ajukan Cetak KTP
                </span>

            </a>


            <!-- =================================================
                 TAMBAH USER
            ================================================== -->

            <a
                href="<?= $bp ?>admin/tambah_user.php"
                class="<?= $currentPage === 'tambah_user.php' ? 'active' : '' ?>"
                data-title="Tambah User"
            >

                <i class="bi bi-person-plus-fill"></i>

                <span>
                    Tambah User
                </span>

            </a>


        <?php else: ?>


            <!-- =================================================
                 DASHBOARD PEMOHON
            ================================================== -->

            <a
                href="<?= $bp ?>user/dashboard.php"
                class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
                data-title="Dashboard"
            >

                <i class="bi bi-grid-1x2-fill"></i>

                <span>
                    Dashboard
                </span>

            </a>


            <!-- =================================================
                 AJUKAN CETAK KTP
            ================================================== -->

            <a
                href="<?= $bp ?>user/pengajuan.php"
                class="<?= $currentPage === 'pengajuan.php' ? 'active' : '' ?>"
                data-title="Ajukan Cetak KTP"
            >

                <i class="bi bi-file-earmark-plus-fill"></i>

                <span>
                    Ajukan Cetak KTP
                </span>

            </a>


            <!-- =================================================
                 CEK STATUS
            ================================================== -->

            <a
                href="<?= $bp ?>user/status.php"
                class="<?= $currentPage === 'status.php' ? 'active' : '' ?>"
                data-title="Cek Status"
            >

                <i class="bi bi-list-check"></i>

                <span>
                    Cek Status
                </span>

            </a>


        <?php endif; ?>


    </nav>


    <!-- =====================================================
         LOGOUT
    ====================================================== -->

    <div class="sidebar-logout">

        <a
            href="<?= $bp ?>logout.php"
            data-title="Logout"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>

    </div>


</aside>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="main-wrapper">


    <!-- =====================================================
         TOPBAR
    ====================================================== -->

    <div class="topbar">


        <div class="d-flex align-items-center gap-3">


            <!-- =================================================
                 TOGGLE SIDEBAR
            ================================================== -->

            <button
                type="button"
                class="sidebar-toggle"
                id="sidebarToggle"
                aria-label="Toggle Sidebar"
                title="Sembunyikan / Tampilkan Sidebar"
            >

                <i
                    class="bi bi-list"
                    id="sidebarToggleIcon"
                ></i>

            </button>


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


        </div>


        <!-- =====================================================
             MOBILE LOGOUT
        ====================================================== -->

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


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <div class="main-content">


<?php else: ?>


<!-- =========================================================
     LOGIN
========================================================= -->

<div class="auth-bg">


<?php endif; ?>


<!-- =========================================================
     SIDEBAR JAVASCRIPT
========================================================= -->

<?php if (is_logged_in()): ?>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const body = document.body;

    const toggleButton =
        document.getElementById('sidebarToggle');

    const toggleIcon =
        document.getElementById('sidebarToggleIcon');

    const overlay =
        document.getElementById('sidebarOverlay');


    /* =====================================================
       CEK UKURAN LAYAR
    ====================================================== */

    function isMobile() {

        return window.innerWidth <= 768;

    }


    /* =====================================================
       UPDATE ICON
    ====================================================== */

    function updateIcon() {

        if (!toggleIcon) {
            return;
        }


        if (isMobile()) {

            if (
                body.classList.contains(
                    'sidebar-mobile-open'
                )
            ) {

                toggleIcon.className =
                    'bi bi-x-lg';

            } else {

                toggleIcon.className =
                    'bi bi-list';

            }

        } else {

            if (
                body.classList.contains(
                    'sidebar-collapsed'
                )
            ) {

                toggleIcon.className =
                    'bi bi-layout-sidebar';

            } else {

                toggleIcon.className =
                    'bi bi-list';

            }

        }

    }


    /* =====================================================
       LOAD STATUS SIDEBAR
    ====================================================== */

    const savedState =
        localStorage.getItem(
            'siakKtpSidebar'
        );


    if (
        savedState === 'collapsed'
        &&
        !isMobile()
    ) {

        body.classList.add(
            'sidebar-collapsed'
        );

    }


    /* =====================================================
       TOGGLE SIDEBAR
    ====================================================== */

    if (toggleButton) {

        toggleButton.addEventListener(
            'click',
            function () {


                if (isMobile()) {


                    body.classList.toggle(
                        'sidebar-mobile-open'
                    );


                } else {


                    body.classList.toggle(
                        'sidebar-collapsed'
                    );


                    /* Simpan pilihan */

                    if (
                        body.classList.contains(
                            'sidebar-collapsed'
                        )
                    ) {

                        localStorage.setItem(
                            'siakKtpSidebar',
                            'collapsed'
                        );

                    } else {

                        localStorage.setItem(
                            'siakKtpSidebar',
                            'expanded'
                        );

                    }

                }


                updateIcon();

            }
        );

    }


    /* =====================================================
       CLOSE SIDEBAR DENGAN OVERLAY
    ====================================================== */

    if (overlay) {

        overlay.addEventListener(
            'click',
            function () {

                body.classList.remove(
                    'sidebar-mobile-open'
                );

                updateIcon();

            }
        );

    }


    /* =====================================================
       CLOSE SIDEBAR SETELAH KLIK MENU DI MOBILE
    ====================================================== */

    const sidebarLinks =
        document.querySelectorAll(
            '.sidebar-nav a, .sidebar-logout a'
        );


    sidebarLinks.forEach(function (link) {

        link.addEventListener(
            'click',
            function () {

                if (isMobile()) {

                    body.classList.remove(
                        'sidebar-mobile-open'
                    );

                    updateIcon();

                }

            }
        );

    });


    /* =====================================================
       RESPONSIVE RESIZE
    ====================================================== */

    window.addEventListener(
        'resize',
        function () {


            if (!isMobile()) {

                body.classList.remove(
                    'sidebar-mobile-open'
                );


                const saved =
                    localStorage.getItem(
                        'siakKtpSidebar'
                    );


                if (
                    saved === 'collapsed'
                ) {

                    body.classList.add(
                        'sidebar-collapsed'
                    );

                }

            } else {

                body.classList.remove(
                    'sidebar-collapsed'
                );

            }


            updateIcon();

        }
    );


    /* =====================================================
       INITIAL ICON
    ====================================================== */

    updateIcon();

});

</script>

<?php endif; ?>


</body>

</html>