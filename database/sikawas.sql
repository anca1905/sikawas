-- ============================================================
-- SIKAWAS - Sistem Informasi Pelaporan Kejahatan Lingkungan di Kawasan Hutan
-- Database: sikawas
-- ============================================================

CREATE DATABASE IF NOT EXISTS sikawas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sikawas;

-- ============================================================
-- Table: admin
-- ============================================================
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: masyarakat
-- ============================================================
CREATE TABLE IF NOT EXISTS masyarakat (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: polisi_hutan
-- ============================================================
CREATE TABLE IF NOT EXISTS polisi_hutan (
    id_polisi INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: pimpinan
-- ============================================================
CREATE TABLE IF NOT EXISTS pimpinan (
    id_pimpinan INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: jenis_kejahatan
-- ============================================================
CREATE TABLE IF NOT EXISTS jenis_kejahatan (
    id_jenis INT AUTO_INCREMENT PRIMARY KEY,
    nama_jenis VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: jenis_kawasan_hutan
-- ============================================================
CREATE TABLE IF NOT EXISTS jenis_kawasan_hutan (
    id_hutan INT AUTO_INCREMENT PRIMARY KEY,
    nama_hutan VARCHAR(100) NOT NULL,
    lokasi_hutan VARCHAR(200),
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: pengaduan
-- ============================================================
CREATE TABLE IF NOT EXISTS pengaduan (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_kejadian DATE NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    bukti_foto VARCHAR(255),
    status ENUM('Baru','Diverifikasi','Diproses','Selesai','Ditolak') DEFAULT 'Baru',
    catatan_admin TEXT NULL,
    catatan_polisi TEXT NULL,
    tanggal_lapor DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    id_jenis INT NOT NULL,
    id_hutan INT NOT NULL,
    id_admin INT NULL,
    id_polisi INT NULL,
    FOREIGN KEY (id_user) REFERENCES masyarakat(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_jenis) REFERENCES jenis_kejahatan(id_jenis),
    FOREIGN KEY (id_hutan) REFERENCES jenis_kawasan_hutan(id_hutan),
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL,
    FOREIGN KEY (id_polisi) REFERENCES polisi_hutan(id_polisi) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SEEDER DATA
-- ============================================================

-- Admin (password: admin123)
INSERT INTO admin (nama, username, password) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Masyarakat (password: user123)
INSERT INTO masyarakat (nama, username, password, no_hp, alamat) VALUES
('Budi Santoso', 'budi', '$2y$10$TKh8H1.PFeDpxCV2RGEjCuKBzMW3MNHZQ9LwA7U6.0vNlHxrE/p2e', '081234567890', 'Jl. Melati No. 5, Desa Rimba'),
('Siti Rahayu', 'siti', '$2y$10$TKh8H1.PFeDpxCV2RGEjCuKBzMW3MNHZQ9LwA7U6.0vNlHxrE/p2e', '082345678901', 'Jl. Pinus No. 12, Desa Hutan Jaya'),
('Ahmad Fauzi', 'ahmad', '$2y$10$TKh8H1.PFeDpxCV2RGEjCuKBzMW3MNHZQ9LwA7U6.0vNlHxrE/p2e', '083456789012', 'Jl. Bambu Kuning No. 3, Kelurahan Rimba Raya');

-- Polisi Hutan (password: polisi123)
INSERT INTO polisi_hutan (nama, username, password) VALUES
('Rudi Hermawan', 'polisi1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dewi Kusuma', 'polisi2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Pimpinan (password: pimpinan123)
INSERT INTO pimpinan (nama, username, password) VALUES
('Ir. Hendra Wijaya', 'pimpinan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Jenis Kejahatan
INSERT INTO jenis_kejahatan (nama_jenis, keterangan) VALUES
('Illegal Logging', 'Penebangan pohon secara liar tanpa izin dari pihak berwenang'),
('Perburuan Liar', 'Perburuan satwa liar yang dilindungi tanpa izin'),
('Pembakaran Hutan', 'Pembakaran hutan secara sengaja yang merusak ekosistem'),
('Pertambangan Ilegal', 'Kegiatan pertambangan tanpa izin di kawasan hutan lindung'),
('Perambahan Hutan', 'Pengambilalihan lahan hutan secara illegal untuk pertanian atau pemukiman'),
('Pembuangan Limbah', 'Pembuangan limbah berbahaya di kawasan hutan'),
('Penangkapan Ikan Ilegal', 'Penangkapan ikan menggunakan bahan berbahaya di sungai kawasan hutan');

-- Jenis Kawasan Hutan
INSERT INTO jenis_kawasan_hutan (nama_hutan, lokasi_hutan, keterangan) VALUES
('Taman Nasional Bukit Barisan', 'Sumatera Barat', 'Kawasan taman nasional yang dilindungi dengan keanekaragaman hayati tinggi'),
('Hutan Lindung Sungai Wain', 'Kalimantan Timur', 'Kawasan hutan lindung dengan fungsi perlindungan tata air'),
('Cagar Alam Gunung Lorentz', 'Papua', 'Cagar alam warisan dunia UNESCO dengan ekosistem unik'),
('Kawasan Hutan Produksi Rimba Jaya', 'Riau', 'Kawasan hutan produksi yang dikelola secara berkelanjutan'),
('Taman Hutan Raya Wan Abdul Rachman', 'Lampung', 'Taman hutan raya di Lampung dengan fungsi konservasi');

-- Sample pengaduan
INSERT INTO pengaduan (tanggal_kejadian, lokasi, deskripsi, status, id_user, id_jenis, id_hutan) VALUES
('2026-08-15', 'Blok C, Sektor 3 Taman Nasional Bukit Barisan', 'Ditemukan jejak penebangan pohon besar secara liar. Terdapat sekitar 20 pohon berdiameter besar yang telah ditebang dan sebagian batangnya dibawa pergi menggunakan kendaraan besar.', 'Diverifikasi', 1, 1, 1),
('2026-08-20', 'Sungai Mahakam, Hutan Lindung Sungai Wain', 'Aktivitas pertambangan emas tanpa izin ditemukan di pinggiran hutan lindung. Terdapat beberapa alat berat dan pekerja illegal.', 'Diproses', 2, 4, 2),
('2026-08-28', 'Zona Inti Cagar Alam Gunung Lorentz', 'Ada asap tebal dari arah dalam hutan, diduga ada pembakaran lahan untuk pembukaan ladang baru oleh oknum tidak bertanggung jawab.', 'Baru', 3, 3, 3),
('2026-09-01', 'Blok B, Kawasan Hutan Produksi Rimba Jaya', 'Ditemukan jerat-jerat babi dan beberapa sangkar burung yang dipasang secara illegal di dalam kawasan hutan produksi.', 'Selesai', 1, 2, 4);
