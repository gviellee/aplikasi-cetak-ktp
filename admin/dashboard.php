<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$total = $pdo->query("SELECT COUNT(*) FROM pengajuan_ktp")->fetchColumn();
$menunggu = $pdo->query("SELECT COUNT(*) FROM pengajuan_ktp WHERE status = 'pending'")->fetchColumn();
$diproses = $pdo->query("SELECT COUNT(*) FROM pengajuan_ktp WHERE status = 'proses'")->fetchColumn();
$selesai = $pdo->query("SELECT COUNT(*) FROM pengajuan_ktp WHERE status = 'selesai'")->fetchColumn();
$ditolak = $pdo->query("SELECT COUNT(*) FROM pengajuan_ktp WHERE status = 'ditolak'")->fetchColumn();
$totalUser = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

$stmt = $pdo->query("SELECT p.*, u.username FROM pengajuan_ktp p LEFT JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 6");
$terbaru = $stmt->fetchAll();

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card hero-banner p-4 p-md-5 mb-4">
  <div style="position:relative;z-index:1;">
    <h5 class="fw-bold text-white mb-1"><i class="bi bi-speedometer2 me-2"></i>Dashboard Administrator</h5>
    <p class="text-white-50 mb-0 small">Pantau seluruh aktivitas pengajuan cetak KTP.</p>
  </div>
</div>

<div class="row g-3 mb-4">
<?php
$stats = [
 ['folder2-open', '#312e81,#1d4ed8', $total, 'Total Pengajuan'],
 ['hourglass-split', '#f59e0b,#fbbf24', $menunggu, 'Menunggu'],
 ['arrow-repeat', '#2563eb,#60a5fa', $diproses, 'Diproses'],
 ['check-circle', '#10b981,#34d399', $selesai, 'Selesai'],
 ['x-circle', '#ef4444,#f87171', $ditolak, 'Ditolak'],
 ['people', '#1a1847,#312e81', $totalUser, 'Total Pemohon']
];
foreach ($stats as $s):
?>
  <div class="col-6 col-md-4 col-xl-2"><div class="stat-card">
    <div class="stat-icon" style="background:linear-gradient(135deg,<?= $s[1] ?>);"><i class="bi bi-<?= $s[0] ?>"></i></div>
    <div class="stat-value"><?= (int)$s[2] ?></div><div class="stat-label"><?= e($s[3]) ?></div>
  </div></div>
<?php endforeach; ?>
</div>

<div class="card p-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-1" style="color:#1d4ed8;"></i> Pengajuan Terbaru</h6>
    <a href="pemohon.php" class="btn btn-sm btn-outline-primary">Lihat Semua <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>NIK</th><th>Nama Pemohon</th><th>Akun</th><th>Status</th><th>Tanggal</th></tr></thead>
    <tbody>
    <?php if (!$terbaru): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr><?php endif; ?>
    <?php foreach ($terbaru as $row): ?>
      <tr>
        <td class="fw-semibold"><?= e($row['nik']) ?></td>
        <td><?= e($row['nama_pemohon']) ?></td>
        <td><?= e($row['username'] ?? '-') ?></td>
        <td><?= status_badge($row['status']) ?></td>
        <td class="text-muted small"><?= e(date('d M Y, H:i', strtotime($row['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
