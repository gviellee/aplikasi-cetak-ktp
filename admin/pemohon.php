<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $status = strtolower(trim($_POST['status'] ?? ''));
    $validStatus = array_keys(status_options());

    if ($id > 0 && in_array($status, $validStatus, true)) {
        $stmt = $pdo->prepare('UPDATE pengajuan_ktp SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        $successMsg = 'Status pengajuan berhasil diperbarui.';
    } else {
        $errorMsg = 'Data status tidak valid.';
    }
}

$search = trim($_GET['q'] ?? '');
$statusFilter = strtolower(trim($_GET['status'] ?? ''));

$sql = "SELECT p.*, u.username FROM pengajuan_ktp p LEFT JOIN users u ON u.id = p.user_id WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (p.nik LIKE ? OR p.nama_pemohon LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusFilter !== '' && array_key_exists($statusFilter, status_options())) {
    $sql .= " AND p.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$pageTitle = 'Daftar Pemohon';
require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($successMsg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i><?= e($successMsg) ?></div><?php endif; ?>
<?php if ($errorMsg): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($errorMsg) ?></div><?php endif; ?>

<div class="card p-3 mb-3"><form method="GET" class="row g-2">
  <div class="col-md-5"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span><input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari NIK / Nama / Username" value="<?= e($search) ?>"></div></div>
  <div class="col-md-4"><select name="status" class="form-select"><option value="">Semua Status</option><?php foreach (status_options() as $key=>$label): ?><option value="<?= e($key) ?>" <?= $statusFilter===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill"><i class="bi bi-funnel-fill me-1"></i>Filter</button><a href="pemohon.php" class="btn btn-outline-secondary">Reset</a></div>
</form></div>

<div class="card p-4"><div class="d-flex align-items-center justify-content-between mb-3"><h6 class="fw-bold mb-0"><i class="bi bi-people-fill me-1" style="color:#1d4ed8;"></i>Daftar Pemohon &amp; Pengajuan</h6><span class="text-muted small"><?= count($daftar) ?> data</span></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>NIK</th><th>Nama</th><th>Akun</th><th>Lampiran</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$daftar): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif; ?>
<?php foreach ($daftar as $row): ?>
<tr>
<td class="fw-semibold"><?= e($row['nik']) ?></td><td><?= e($row['nama_pemohon']) ?></td><td><?= e($row['username'] ?? '-') ?></td>
<td><?php if (!empty($row['gambar_path'])): ?><a href="../uploads/<?= e($row['gambar_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image"></i> Lihat</a><?php else: ?>-<?php endif; ?></td>
<td><?= status_badge($row['status']) ?></td><td class="text-muted small"><?= e(date('d M Y, H:i', strtotime($row['created_at']))) ?></td>
<td><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalStatus<?= (int)$row['id'] ?>"><i class="bi bi-pencil-square"></i> Ubah</button></td>
</tr>
<div class="modal fade" id="modalStatus<?= (int)$row['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST">
<div class="modal-header"><h6 class="modal-title">Ubah Status - <?= e($row['nama_pemohon']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select" required><?php foreach(status_options() as $key=>$label): ?><option value="<?= e($key) ?>" <?= $row['status']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div></div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" name="update_status" class="btn btn-primary">Simpan Perubahan</button></div>
</form></div></div></div>
<?php endforeach; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
