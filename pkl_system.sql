-- ============================================================
--  pkl_system.sql — Struktur Database Sistem PKL
--  Jalankan file ini di phpMyAdmin atau MySQL CLI:
--  mysql -u root -p < pkl_system.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS pkl_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pkl_system;

-- ----------------------------------------
-- Tabel: users (admin, siswa, pembimbing)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    email       VARCHAR(150) DEFAULT NULL,
    role        ENUM('admin','siswa','pembimbing') NOT NULL DEFAULT 'siswa',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: perusahaan (mitra industri)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS perusahaan (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(200) NOT NULL,
    bidang      VARCHAR(150) DEFAULT NULL,
    alamat      TEXT DEFAULT NULL,
    telepon     VARCHAR(20) DEFAULT NULL,
    email       VARCHAR(150) DEFAULT NULL,
    kapasitas   INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: siswa (profil siswa PKL)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS siswa (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    nis             VARCHAR(20) DEFAULT NULL,
    nama_lengkap    VARCHAR(150) NOT NULL,
    kelas           VARCHAR(50) DEFAULT NULL,
    jurusan         VARCHAR(100) DEFAULT NULL,
    alamat          TEXT DEFAULT NULL,
    telepon         VARCHAR(20) DEFAULT NULL,
    foto            VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: pembimbing (profil pembimbing)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS pembimbing (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    nip             VARCHAR(30) DEFAULT NULL,
    nama_lengkap    VARCHAR(150) NOT NULL,
    mata_pelajaran  VARCHAR(100) DEFAULT NULL,
    telepon         VARCHAR(20) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: penempatan (siswa -> perusahaan)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS penempatan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id        INT NOT NULL,
    perusahaan_id   INT NOT NULL,
    pembimbing_id   INT DEFAULT NULL,
    tanggal_mulai   DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    status          ENUM('aktif','selesai','berhenti') DEFAULT 'aktif',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id)      REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id) ON DELETE CASCADE,
    FOREIGN KEY (pembimbing_id) REFERENCES pembimbing(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: absensi
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS absensi (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    penempatan_id   INT NOT NULL,
    tanggal         DATE NOT NULL,
    jam_masuk       TIME DEFAULT NULL,
    jam_keluar      TIME DEFAULT NULL,
    status          ENUM('hadir','izin','sakit','alpha','terlambat') DEFAULT 'hadir',
    keterangan      TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: jurnal (jurnal harian siswa)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS jurnal (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    penempatan_id   INT NOT NULL,
    tanggal         DATE NOT NULL,
    kegiatan        TEXT NOT NULL,
    status          ENUM('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
    catatan         TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: nilai (penilaian PKL)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS nilai (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    penempatan_id   INT NOT NULL UNIQUE,
    disiplin        TINYINT UNSIGNED DEFAULT 0 CHECK (disiplin <= 100),
    kompetensi      TINYINT UNSIGNED DEFAULT 0 CHECK (kompetensi <= 100),
    kerjasama       TINYINT UNSIGNED DEFAULT 0 CHECK (kerjasama <= 100),
    inisiatif       TINYINT UNSIGNED DEFAULT 0 CHECK (inisiatif <= 100),
    rata_rata       DECIMAL(5,2) AS ((disiplin + kompetensi + kerjasama + inisiatif) / 4) STORED,
    grade           CHAR(2) AS (
        CASE
            WHEN (disiplin + kompetensi + kerjasama + inisiatif) / 4 >= 90 THEN 'A'
            WHEN (disiplin + kompetensi + kerjasama + inisiatif) / 4 >= 80 THEN 'B'
            WHEN (disiplin + kompetensi + kerjasama + inisiatif) / 4 >= 70 THEN 'C'
            WHEN (disiplin + kompetensi + kerjasama + inisiatif) / 4 >= 60 THEN 'D'
            ELSE 'E'
        END
    ) STORED,
    catatan         TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: laporan (laporan akhir PKL)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS laporan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    penempatan_id   INT NOT NULL,
    judul           VARCHAR(255) DEFAULT NULL,
    file_path       VARCHAR(500) DEFAULT NULL,
    status          ENUM('menunggu','disetujui','revisi') DEFAULT 'menunggu',
    catatan         TEXT DEFAULT NULL,
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at     TIMESTAMP DEFAULT NULL,
    FOREIGN KEY (penempatan_id) REFERENCES penempatan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: notifikasi
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS notifikasi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT DEFAULT NULL,
    judul       VARCHAR(255) NOT NULL,
    pesan       TEXT NOT NULL,
    tipe        ENUM('info','warning','error','success') DEFAULT 'info',
    dibaca      TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------
-- Tabel: log_aktivitas
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT DEFAULT NULL,
    aktivitas   TEXT NOT NULL,
    ip_address  VARCHAR(45) DEFAULT NULL,
    status      ENUM('berhasil','gagal') DEFAULT 'berhasil',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
--  DATA DUMMY / SAMPLE
-- ============================================================

-- Admin default (password: admin123)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('guru_hendra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pembimbing'),
('guru_sari', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pembimbing'),
('siswa_andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa'),
('siswa_budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa'),
('siswa_citra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa');
-- Catatan: password dummy di atas = "password" (Laravel default hash)
-- Untuk produksi, gunakan: password_hash('passwordanda', PASSWORD_DEFAULT)

INSERT INTO perusahaan (nama, bidang, alamat, kapasitas) VALUES
('PT. Maju Bersama', 'IT & Software', 'Jl. Sudirman No.45, Jakarta', 10),
('CV. Teknologi Nusantara', 'Networking & Infrastructure', 'Jl. Gatot Subroto No.12, Bandung', 5),
('PT. Digital Solusi', 'Web Development', 'Jl. Ahmad Yani No.8, Surabaya', 8);

INSERT INTO siswa (user_id, nis, nama_lengkap, kelas, jurusan) VALUES
(4, '2024001', 'Andi Pratama', 'XII RPL 1', 'Rekayasa Perangkat Lunak'),
(5, '2024002', 'Budi Santoso', 'XII RPL 2', 'Rekayasa Perangkat Lunak'),
(6, '2024003', 'Citra Dewi', 'XII TKJ', 'Teknik Komputer Jaringan');

INSERT INTO pembimbing (user_id, nip, nama_lengkap, mata_pelajaran) VALUES
(2, '198501012010011001', 'Bpk. Hendra Wijaya', 'Pemrograman Web'),
(3, '199003152015012002', 'Ibu. Sari Indah', 'Basis Data');
