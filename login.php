<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . (is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        }

        $error = 'Username atau password salah.';
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card border-0" style="max-width:430px;width:100%;border-radius:22px;box-shadow:0 30px 70px rgba(11,10,31,.45);">
  <div class="card-body p-4 p-md-5">
    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#312e81 0%,#1d4ed8 100%);box-shadow:0 10px 28px rgba(29,78,216,.4);">
        <i class="bi bi-person-vcard-fill text-white fs-2"></i>
      </div>
      <h4 class="fw-bold mb-1" style="color:#1a1847;">SIAK-KTP</h4>
      <p class="text-muted small mb-0">Sistem Pengajuan Cetak KTP</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
          <input type="text" name="username" class="form-control border-start-0 ps-0" required autofocus value="<?= e($username) ?>">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
          <input type="password" name="password" class="form-control border-start-0 ps-0" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</button>
    </form>

    <hr class="my-4">
    <p class="text-muted small text-center mb-0">
      Akun admin default: <code>admin</code> / <code>admin123</code><br>
      Akun pemohon dibuat oleh admin melalui menu "Tambah User".
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
