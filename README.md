# SIAK-KTP - Sistem Pengajuan Cetak KTP

Versi ini sudah disesuaikan dengan database yang digunakan:
- Database: `ktp_management`
- Tabel pengguna: `users`
- Tabel pengajuan: `pengajuan_ktp`
- Role: `admin` dan `user` (user ditampilkan sebagai Pemohon)
- Status: `pending`, `proses`, `selesai`, `ditolak`

## Menjalankan di XAMPP
1. Simpan folder ini sebagai `C:\xampp\htdocs\aplikasi-cetak-ktp`.
2. Jalankan Apache dan MySQL di XAMPP.
3. Pastikan database `ktp_management` tersedia.
4. Jika tabel sudah ada, tidak perlu mengimpor ulang `database.sql`.
5. Buka `http://localhost/aplikasi-cetak-ktp/`.

## Login default
Username: `admin`
Password: `admin123`

Untuk pemohon, login menggunakan akun dengan role `user`. Admin dapat membuat akun pemohon melalui menu Tambah User.

## Composer
Composer hanya dibutuhkan untuk dependency jika project memilikinya. Project ini tidak membutuhkan `composer run dev` untuk dijalankan melalui XAMPP.
