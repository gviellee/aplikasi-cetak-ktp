<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {

    if (is_admin()) {

        header(
            'Location: admin/dashboard.php'
        );

    } else {

        header(
            'Location: user/dashboard.php'
        );
    }

    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim(
        $_POST['username'] ?? ''
    );

    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT * FROM users
         WHERE username = ?
         LIMIT 1"
    );

    $stmt->execute([
        $username
    ]);

    $user = $stmt->fetch();

    if (
        $user &&
        password_verify(
            $password,
            $user['password']
        )
    ) {

        $_SESSION['user_id'] =
            $user['id'];

        $_SESSION['username'] =
            $user['username'];

        $_SESSION['nama_lengkap'] =
            $user['nama_lengkap'];

        $_SESSION['role'] =
            $user['role'];

        if ($user['role'] === 'admin') {

            header(
                'Location: admin/dashboard.php'
            );

        } else {

            header(
                'Location: user/dashboard.php'
            );
        }

        exit;

    } else {

        $error =
            'Username atau password salah.';
    }
}

$pageTitle = 'Login';

require_once __DIR__ . '/includes/header.php';
?>

<div class="card p-4" style="width:100%;max-width:430px;">

    <div class="text-center mb-4">

        <div
            style="
                width:60px;
                height:60px;
                margin:auto;
                border-radius:16px;
                background:linear-gradient(
                    135deg,
                    #7c3aed,
                    #a78bfa
                );
                display:flex;
                align-items:center;
                justify-content:center;
            "
        >
            <i class="bi bi-person-vcard-fill text-white fs-3"></i>
        </div>

        <h4 class="fw-bold mt-3 mb-1">
            KTP Ludow
        </h4>

        <p class="text-muted small">
            Sistem Pengajuan Cetak KTP
        </p>

    </div>


    <?php if ($error): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="mb-3">

            <label class="form-label">
                Username
            </label>

            <input
                type="text"
                name="username"
                class="form-control"
                required
            >

        </div>


        <div class="mb-4">

            <label class="form-label">
                Password
            </label>

            <input
                type="password"
                name="password"
                class="form-control"
                required
            >

        </div>


        <button
            type="submit"
            class="btn btn-primary w-100"
        >
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Login
        </button>

    </form>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>