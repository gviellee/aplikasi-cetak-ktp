CREATE DATABASE IF NOT EXISTS ktp_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ktp_management;

CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pengajuan_ktp (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nik VARCHAR(16) NOT NULL,
    nama_pemohon VARCHAR(30) NOT NULL,
    gambar_path VARCHAR(255) NOT NULL,
    status ENUM('pending','proses','selesai','ditolak') NOT NULL DEFAULT 'pending',
    user_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status),
    KEY nik (nik),
    CONSTRAINT fk_pengajuan_ktp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users (username, password, role)
SELECT 'admin', '$2y$12$mFskncxVHGMFLj3xP.B8N.NRkx2JCrVzkYQtOj5B0G8JWzraMruQW', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
