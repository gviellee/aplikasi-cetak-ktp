<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

$errors = [];
$success = '';
$old = ['nik' => '', 'nama_pemohon' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = trim($_POST['nik'] ?? '');
    $nama_pemohon = trim($_POST['nama_pemohon'] ?? '');
    $old = ['nik' => $nik, 'nama_pemohon' => $nama_pemohon];

    if (!validate_nik($nik)) $errors[] = 'NIK harus berupa angka dan tepat 16 digit.';
    if (!validate_nama($nama_pemohon)) $errors[] = 'Nama pemohon hanya boleh berisi huruf dan spasi, maksimal 30 karakter.';

    $fileCheck = validate_file_upload($_FILES['dokumen'] ?? null);
    if (!$fileCheck['valid']) $errors[] = $fileCheck['message'];

    if (!$errors) {
        $fileName = process_file_upload($_FILES['dokumen']);
        if (!$fileName) {
            $errors[] = 'Gagal menyimpan file, silakan coba lagi.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO pengajuan_ktp (nik, nama_pemohon, gambar_path, status, user_id) VALUES (?, ?, ?, 'pending', ?)");
                $stmt->execute([$nik, $nama_pemohon, $fileName, $_SESSION['user_id']]);
                $success = 'Pengajuan cetak KTP berhasil dikirim. Silakan cek status secara berkala.';
                $old = ['nik' => '', 'nama_pemohon' => ''];
            } catch (PDOException $e) {
                @unlink(UPLOAD_DIR . $fileName);
                $errors[] = 'Pengajuan gagal disimpan: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Ajukan Cetak KTP';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-7"><div class="card p-4 p-md-5"><div class="d-flex align-items-center gap-3 mb-4"><div style="width:50px;height:50px;border-radius:13px;background:linear-gradient(135deg,#312e81,#1d4ed8);display:flex;align-items:center;justify-content:center;"><i class="bi bi-file-earmark-plus-fill text-white fs-5"></i></div><div><h6 class="fw-bold mb-0">Form Pengajuan Cetak KTP</h6><span class="text-muted small">Lengkapi data di bawah dengan benar</span></div></div>
<?php if($success): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i><?= e($success) ?></div><?php endif; ?>
<?php if($errors): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data"><div class="mb-3"><label class="form-label">NIK</label><input type="text" name="nik" class="form-control" maxlength="16" pattern="[0-9]{16}" placeholder="16 digit angka" required value="<?= e($old['nik']) ?>"></div><div class="mb-3"><label class="form-label">Nama Pemohon</label><input type="text" name="nama_pemohon" class="form-control" maxlength="30" placeholder="Nama lengkap sesuai KTP" required value="<?= e($old['nama_pemohon']) ?>"></div><div class="mb-4"><label class="form-label">Upload Gambar KTP / Surat Kehilangan</label><input type="file" name="dokumen" class="form-control" accept=".jpg,.jpeg,.png" required><div class="form-text">JPG/PNG, maksimal 5 MB.</div></div><button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-send-fill me-1"></i>Kirim Pengajuan</button></form>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
