<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_ktp WHERE user_id = ?"); $stmt->execute([$userId]); $total = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_ktp WHERE user_id = ? AND status = 'pending'"); $stmt->execute([$userId]); $menunggu = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_ktp WHERE user_id = ? AND status = 'selesai'"); $stmt->execute([$userId]); $selesai = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT * FROM pengajuan_ktp WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"); $stmt->execute([$userId]); $terbaru = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card hero-banner p-4 p-md-5 mb-4"><div style="position:relative;z-index:1;"><h5 class="fw-bold text-white mb-1">Selamat datang, <?= e($_SESSION['username']) ?> 👋</h5><p class="text-white-50 mb-0 small">Kelola pengajuan cetak KTP Anda di sini.</p><a href="pengajuan.php" class="btn btn-light fw-semibold mt-3"><i class="bi bi-file-earmark-plus-fill me-1"></i>Ajukan Cetak KTP</a></div></div>
<div class="row g-3 mb-4"><div class="col-6 col-md-4"><div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#312e81,#1d4ed8);"><i class="bi bi-folder2-open"></i></div><div class="stat-value"><?= (int)$total ?></div><div class="stat-label">Total Pengajuan Saya</div></div></div><div class="col-6 col-md-4"><div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);"><i class="bi bi-hourglass-split"></i></div><div class="stat-value"><?= (int)$menunggu ?></div><div class="stat-label">Sedang Menunggu</div></div></div><div class="col-12 col-md-4"><div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);"><i class="bi bi-check-circle"></i></div><div class="stat-value"><?= (int)$selesai ?></div><div class="stat-label">Selesai Diajukan</div></div></div></div>
<div class="card p-4"><div class="d-flex align-items-center justify-content-between mb-3"><h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-1" style="color:#1d4ed8;"></i>Pengajuan Terbaru</h6><a href="status.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>NIK</th><th>Nama</th><th>Status</th><th>Tanggal</th></tr></thead><tbody><?php if(!$terbaru): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada pengajuan. <a href="pengajuan.php">Ajukan sekarang</a>.</td></tr><?php endif; ?><?php foreach($terbaru as $row): ?><tr><td class="fw-semibold"><?= e($row['nik']) ?></td><td><?= e($row['nama_pemohon']) ?></td><td><?= status_badge($row['status']) ?></td><td class="text-muted small"><?= e(date('d M Y, H:i', strtotime($row['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
