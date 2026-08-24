<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

$stmt = $pdo->prepare("SELECT * FROM pengajuan_ktp WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$daftar = $stmt->fetchAll();

$pageTitle = 'Cek Status Pengajuan';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4"><div class="d-flex align-items-center justify-content-between mb-3"><h6 class="fw-bold mb-0"><i class="bi bi-list-check me-1" style="color:#1d4ed8;"></i>Status Pengajuan Cetak KTP Saya</h6><span class="text-muted small"><?= count($daftar) ?> pengajuan</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>NIK</th><th>Nama</th><th>Lampiran</th><th>Status</th><th>Tanggal Ajuan</th><th>Terakhir Update</th></tr></thead><tbody><?php if(!$daftar): ?><tr><td colspan="6" class="text-center text-muted py-4">Anda belum memiliki pengajuan. <a href="pengajuan.php">Ajukan sekarang</a>.</td></tr><?php endif; ?><?php foreach($daftar as $row): ?><tr><td class="fw-semibold"><?= e($row['nik']) ?></td><td><?= e($row['nama_pemohon']) ?></td><td><?php if($row['gambar_path']): ?><a href="../uploads/<?= e($row['gambar_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image"></i> Lihat</a><?php else: ?>-<?php endif; ?></td><td><?= status_badge($row['status']) ?></td><td class="text-muted small"><?= e(date('d M Y, H:i', strtotime($row['created_at']))) ?></td><td class="text-muted small"><?= e(date('d M Y, H:i', strtotime($row['updated_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
