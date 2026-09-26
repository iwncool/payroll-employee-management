-- Database untuk Aplikasi Payroll dan Master Data Karyawan
CREATE DATABASE IF NOT EXISTS payroll_db;
USE payroll_db;

-- Tabel Departemen
CREATE TABLE departemen (
  id_departemen INT AUTO_INCREMENT PRIMARY KEY,
  nama_departemen VARCHAR(100) NOT NULL,
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Jabatan
CREATE TABLE jabatan (
  id_jabatan INT AUTO_INCREMENT PRIMARY KEY,
  nama_jabatan VARCHAR(100) NOT NULL,
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Master Karyawan
CREATE TABLE karyawan (
  id_karyawan INT AUTO_INCREMENT PRIMARY KEY,
  nik VARCHAR(20) UNIQUE NOT NULL,
  nama_karyawan VARCHAR(100) NOT NULL,
  tanggal_lahir DATE,
  jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
  alamat TEXT,
  nomor_telepon VARCHAR(15),
  email VARCHAR(100),
  id_departemen INT NOT NULL,
  id_jabatan INT NOT NULL,
  tanggal_masuk DATE NOT NULL,
  status_kerja ENUM('Aktif', 'Cuti', 'Resign', 'Pensiun') DEFAULT 'Aktif',
  gaji_pokok DECIMAL(12,2) NOT NULL,
  no_rekening VARCHAR(20),
  nama_bank VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_departemen) REFERENCES departemen(id_departemen),
  FOREIGN KEY (id_jabatan) REFERENCES jabatan(id_jabatan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Tunjangan
CREATE TABLE tunjangan (
  id_tunjangan INT AUTO_INCREMENT PRIMARY KEY,
  nama_tunjangan VARCHAR(100) NOT NULL,
  tipe_tunjangan ENUM('Fixed', 'Percentage') DEFAULT 'Fixed',
  jumlah DECIMAL(12,2) NOT NULL,
  keterangan TEXT,
  status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Tunjangan Karyawan
CREATE TABLE detail_tunjangan_karyawan (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_karyawan INT NOT NULL,
  id_tunjangan INT NOT NULL,
  jumlah DECIMAL(12,2),
  status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_karyawan) REFERENCES karyawan(id_karyawan) ON DELETE CASCADE,
  FOREIGN KEY (id_tunjangan) REFERENCES tunjangan(id_tunjangan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Potongan
CREATE TABLE potongan (
  id_potongan INT AUTO_INCREMENT PRIMARY KEY,
  nama_potongan VARCHAR(100) NOT NULL,
  tipe_potongan ENUM('Fixed', 'Percentage') DEFAULT 'Fixed',
  jumlah DECIMAL(12,2) NOT NULL,
  keterangan TEXT,
  status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel UMR (Upah Minimum Regional) - untuk perhitungan BPJS
CREATE TABLE umr (
  id_umr INT AUTO_INCREMENT PRIMARY KEY,
  tahun INT NOT NULL,
  bulan INT NOT NULL,
  jumlah_umr DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_tahun_bulan (tahun, bulan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Payroll
CREATE TABLE payroll (
  id_payroll INT AUTO_INCREMENT PRIMARY KEY,
  id_karyawan INT NOT NULL,
  tahun INT NOT NULL,
  bulan INT NOT NULL,
  gaji_pokok DECIMAL(12,2) NOT NULL,
  total_tunjangan DECIMAL(12,2) DEFAULT 0,
  subtotal_gaji_tunjangan DECIMAL(12,2) NOT NULL,
  umr_referensi DECIMAL(12,2) NOT NULL,
  bpjs_kesehatan DECIMAL(12,2) DEFAULT 0,
  bpjs_ketenagakerjaan DECIMAL(12,2) DEFAULT 0,
  bpjs_pensiun DECIMAL(12,2) DEFAULT 0,
  total_bpjs DECIMAL(12,2) DEFAULT 0,
  potongan_lainnya DECIMAL(12,2) DEFAULT 0,
  total_potongan DECIMAL(12,2) DEFAULT 0,
  gaji_bersih DECIMAL(12,2) NOT NULL,
  status_payroll ENUM('Draft', 'Approved', 'Paid') DEFAULT 'Draft',
  tanggal_bayar DATE,
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_karyawan) REFERENCES karyawan(id_karyawan) ON DELETE CASCADE,
  UNIQUE KEY unique_payroll (id_karyawan, tahun, bulan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Tunjangan Payroll
CREATE TABLE detail_tunjangan_payroll (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_payroll INT NOT NULL,
  id_tunjangan INT NOT NULL,
  nama_tunjangan VARCHAR(100),
  jumlah DECIMAL(12,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_payroll) REFERENCES payroll(id_payroll) ON DELETE CASCADE,
  FOREIGN KEY (id_tunjangan) REFERENCES tunjangan(id_tunjangan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Potongan Payroll
CREATE TABLE detail_potongan_payroll (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_payroll INT NOT NULL,
  id_potongan INT NOT NULL,
  nama_potongan VARCHAR(100),
  jumlah DECIMAL(12,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_payroll) REFERENCES payroll(id_payroll) ON DELETE CASCADE,
  FOREIGN KEY (id_potongan) REFERENCES potongan(id_potongan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Default Departemen
INSERT INTO departemen (nama_departemen, keterangan) VALUES
('Direksi', 'Tingkat Manajemen Puncak'),
('Operasional', 'Bagian Operasional Perusahaan'),
('HRD', 'Human Resources Development'),
('Finance', 'Bagian Keuangan'),
('Marketing', 'Bagian Pemasaran'),
('IT', 'Bagian Teknologi Informasi');

-- Data Default Jabatan
INSERT INTO jabatan (nama_jabatan, keterangan) VALUES
('Direktur', 'Pimpinan Tertinggi'),
('Manager', 'Kepala Departemen'),
('Supervisor', 'Pimpinan Tim'),
('Staff', 'Karyawan Biasa'),
('Operator', 'Operator Mesin/Sistem');

-- Data Default Tunjangan
INSERT INTO tunjangan (nama_tunjangan, tipe_tunjangan, jumlah, keterangan, status) VALUES
('Tunjangan Makan', 'Fixed', 500000, 'Tunjangan makan harian', 'Aktif'),
('Tunjangan Transportasi', 'Fixed', 300000, 'Tunjangan transportasi bulanan', 'Aktif'),
('Tunjangan Kesehatan', 'Fixed', 200000, 'Tunjangan kesehatan tambahan', 'Aktif'),
('Tunjangan Anak', 'Fixed', 100000, 'Per anak (max 3 anak)', 'Aktif'),
('Tunjangan Istri', 'Fixed', 150000, 'Tunjangan istri', 'Aktif'),
('Bonus Kinerja', 'Percentage', 5, 'Bonus berdasarkan performa', 'Aktif');

-- Data Default Potongan
INSERT INTO potongan (nama_potongan, tipe_potongan, jumlah, keterangan, status) VALUES
('PPh 21', 'Percentage', 5, 'Pajak Penghasilan', 'Aktif'),
('Cicilan Hutang', 'Fixed', 0, 'Cicilan hutang karyawan', 'Aktif');

-- Data Default UMR (Contoh untuk Tahun 2024-2025)
INSERT INTO umr (tahun, bulan, jumlah_umr) VALUES
(2024, 1, 3700000),
(2024, 2, 3700000),
(2024, 3, 3700000),
(2024, 4, 3700000),
(2024, 5, 3700000),
(2024, 6, 3700000),
(2024, 7, 3700000),
(2024, 8, 3700000),
(2024, 9, 3700000),
(2024, 10, 3700000),
(2024, 11, 3700000),
(2024, 12, 3700000),
(2025, 1, 3750000),
(2025, 2, 3750000),
(2025, 3, 3750000),
(2025, 4, 3750000),
(2025, 5, 3750000),
(2025, 6, 3750000),
(2025, 7, 3750000),
(2025, 8, 3750000),
(2025, 9, 3750000),
(2025, 10, 3750000),
(2025, 11, 3750000),
(2025, 12, 3750000);
