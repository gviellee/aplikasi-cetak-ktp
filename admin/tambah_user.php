<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if ($username === '' || strlen($username) < 4) $errors[] = 'Username minimal 4 karakter.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $confirmPassword) $errors[] = 'Konfirmasi password tidak sama.';
    if (!in_array($role, ['user', 'admin'], true)) $errors[] = 'Role tidak valid.';

    if (!$errors) {
        $cek = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $cek->execute([$username]);
        if ($cek->fetch()) $errors[] = 'Username sudah digunakan.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, $hash, $role]);
        $success = "Akun '$username' berhasil dibuat.";
        $_POST = [];
    }
}

$users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Tambah User';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-3"><div class="col-lg-5"><div class="card p-4">
<h6 class="fw-bold mb-3"><i class="bi bi-person-plus-fill me-1" style="color:#1d4ed8;"></i>Buat Akun Baru</h6>
<?php if($success): ?><div class="alert alert-success py-2 small"><i class="bi bi-check-circle-fill me-1"></i><?= e($success) ?></div><?php endif; ?>
<?php if($errors): ?><div class="alert alert-danger py-2 small"><ul class="mb-0 ps-3"><?php foreach($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST"><div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required minlength="4" value="<?= e($_POST['username'] ?? '') ?>"></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
<div class="mb-3"><label class="form-label">Konfirmasi Password</label><input type="password" name="confirm_password" class="form-control" required minlength="6"></div>
<div class="mb-4"><label class="form-label">Role</label><select name="role" class="form-select"><option value="user">User (Pemohon)</option><option value="admin">Admin</option></select></div>
<button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2-circle me-1"></i>Simpan User</button></form>
</div></div>
<div class="col-lg-7"><div class="card p-4"><div class="d-flex align-items-center justify-content-between mb-3"><h6 class="fw-bold mb-0"><i class="bi bi-people me-1" style="color:#1d4ed8;"></i>Daftar User</h6><span class="text-muted small"><?= count($users) ?> akun</span></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Username</th><th>Role</th><th>Dibuat</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td class="fw-semibold"><?= e($u['username']) ?></td><td><span class="status-badge <?= $u['role']==='admin'?'status-diproses':'status-selesai' ?>"><?= e($u['role']==='admin'?'Admin':'Pemohon') ?></span></td><td class="text-muted small"><?= e(date('d M Y', strtotime($u['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
