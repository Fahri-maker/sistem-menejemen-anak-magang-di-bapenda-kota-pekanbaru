-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 10 Okt 2024 pada 03.03
-- Versi Server: 10.1.30-MariaDB
-- PHP Version: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bapenda`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status_kehadiran` enum('hadir','tidak hadir','izin','sakit') NOT NULL,
  `dokumentasi` varchar(255) DEFAULT NULL,
  `validasi_by` int(11) DEFAULT NULL,
  `skor` int(11) DEFAULT NULL,
  `anak_magang_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`id`, `tanggal`, `status_kehadiran`, `dokumentasi`, `validasi_by`, `skor`, `anak_magang_id`) VALUES
(42, '2024-09-03', 'hadir', 'WhatsApp Image 2024-09-03 at 15.45.07.jpeg', 2, 100, '1'),
(43, '2024-09-04', 'hadir', 'WhatsApp Image 2024-08-27 at 13.50.39.jpeg', 2, 100, '1'),
(44, '2024-09-05', 'hadir', 'formulir_cuti_12345678901.pdf', 2, 100, '1'),
(45, '2024-09-05', 'hadir', 'WhatsApp Image 2024-09-03 at 15.45.07.jpeg', 2, 100, '1'),
(46, '2024-09-05', 'hadir', 'WhatsApp Image 2024-08-27 at 13.50.39.jpeg', 2, 100, '1'),
(47, '2024-09-05', 'sakit', 'WhatsApp Image 2024-09-05 at 13.45.44.jpeg', 2, 50, '1'),
(48, '2024-09-05', 'hadir', 'WhatsApp Image 2024-09-05 at 13.45.44.jpeg', 2, 100, '1'),
(49, '2024-09-06', 'tidak hadir', 'WhatsApp Image 2024-09-05 at 13.45.44.jpeg', 2, 0, '1'),
(50, '2024-09-30', 'sakit', 'NIM MHS 2024-2025 - Copy.xlsx', NULL, NULL, '1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun_anak_magang`
--

CREATE TABLE `akun_anak_magang` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `anak_magang_id` varchar(30) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `akun_anak_magang`
--

INSERT INTO `akun_anak_magang` (`id`, `username`, `password`, `anak_magang_id`, `created_by`) VALUES
(11, '1', '1', '1', 1),
(12, '2110031802015', '2110031802015', '2110031802015', 1),
(13, '2110031802009', '2110031802009', '2110031802009', 1),
(15, '9999', '9999', '9999', 1),
(16, '88888', '88888', '88888', 1),
(18, '3333333', '3333333', '3333333', 1),
(19, '242342', '242342', '242342', 1),
(20, '23423432', '23423432', '23423432', 1),
(21, '101010101', '101010101', '101010101', 1),
(22, '222222222222222', '222222222222222', '222222222222222', 1),
(23, '11111111111111111111111', '11111111111111111111111', '11111111111111111111111', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun_master`
--

CREATE TABLE `akun_master` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `akun_master`
--

INSERT INTO `akun_master` (`id`, `nama`, `username`, `password`) VALUES
(1, 'fahri', '1', '1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun_supervisor`
--

CREATE TABLE `akun_supervisor` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `karyawan_divisi_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `akun_supervisor`
--

INSERT INTO `akun_supervisor` (`id`, `username`, `password`, `created_by`, `karyawan_divisi_id`) VALUES
(42, '2', '2', 1, 2),
(44, '6', '5', 1, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `anak_magang`
--

CREATE TABLE `anak_magang` (
  `nim_nisn` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `asal_kampus_sekolah` varchar(255) NOT NULL,
  `surat_pengantar_kampus` text,
  `surat_penerimaan` text,
  `foto` varchar(255) DEFAULT NULL,
  `masa_magang` int(11) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif',
  `divisi_id` int(30) NOT NULL,
  `supervisor_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `anak_magang`
--

INSERT INTO `anak_magang` (`nim_nisn`, `nama`, `alamat`, `asal_kampus_sekolah`, `surat_pengantar_kampus`, `surat_penerimaan`, `foto`, `masa_magang`, `tanggal_mulai`, `tanggal_selesai`, `status`, `divisi_id`, `supervisor_id`) VALUES
('1', 'roza', 'panam', 'darmayuda', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 1, '2024-08-16', '2024-09-06', 'aktif', 2, '2'),
('101010101', 'nopal', 'jnsjndjas', 'nsdcjdscd', '1745-Article Text-5663-2-10-20220203.pdf', 'PRATIKUM.pdf', 'WhatsApp Image 2024-08-22 at 14.53.29.jpeg', 1, '2024-08-22', '2024-08-23', 'aktif', 2, '2'),
('11111111111111111111111', 'nana', 'sdas', 'scasa', 'WhatsApp Image 2024-08-22 at 14.51.40.jpeg', 'WhatsApp Image 2024-08-22 at 14.51.40.jpeg', 'WhatsApp Image 2024-08-22 at 14.51.40.jpeg', 1, '2024-08-26', '2024-08-27', 'aktif', 2, '2'),
('2110031802009', 'fahri', 'panam', 'universitas sains dan teknologin indonesia', 'PRATIKUM.pdf', 'PRATIKUM-2.pdf', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 1, '2024-08-17', '2024-09-25', 'aktif', 2, '2'),
('2110031802015', 'elisa novita sari', 'rawa bening', 'universitas sains dan teknologin indonesia', 'PRATIKUM-1.pdf', 'PRATIKUM-2.pdf', 'WhatsApp Image 2024-08-17 at 00.35.38.jpeg', 2, '2024-08-05', '2024-09-25', 'aktif', 2, '2'),
('222222222222222', 'sdfsfsdf', 'sfsfsdd', 'sfssd', 'WhatsApp Image 2024-08-22 at 14.51.40.jpeg', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 'WhatsApp Image 2024-08-16 at 20.14.41.jpeg', 1, '2024-08-26', '2024-08-29', 'aktif', 2, '2'),
('23423432', 'wibi', 'panam', 'sjdnjand', '202320242-TI-UTS-NMP.pdf', 'TUGAS STRUKTUR DATA (FUNGSI) (1).docx', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 1, '2024-08-22', '2024-08-23', 'aktif', 2, '2'),
('242342', 'fahri aja', 'dfrfrfref', 'rfefer', '1745-Article Text-5663-2-10-20220203-1.pdf', '202320242-TI-UTS-NMP.pdf', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 4, '2024-08-22', '2024-08-23', 'aktif', 2, '2'),
('3333333', 'rini yanti', 'panam', 'sb', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 'TUGAS INTERAKSI MANUSIA DAN KOMPUTER.docx', '481-1-2138-1-10-20210902.pdf', 1, '2024-08-21', '2024-08-22', 'aktif', 1, '1'),
('66', 'vini', 'prakom', 'bapenda', 'Desain_Dan_Implementasi_Hybrid_Cloud_Computing_Seb.pdf', 'TUGAS STRUKTUR DATA (FUNGSI).docx', 'WhatsApp Image 2024-08-17 at 12.24.19.jpeg', 1, '2024-08-21', '2024-08-22', 'aktif', 2, '2'),
('88888', 'febian', 'panam', 'usti', 'PRATIKUM-2.pdf', 'PRATIKUM-2.pdf', 'WhatsApp Image 2024-08-12 at 19.52.46.jpeg', 1, '2024-08-29', '2024-08-30', 'aktif', 1, '1'),
('9999', 'ibnu', 'panam', 'usti', 'PRATIKUM.pdf', 'PRATIKUM-2.pdf', 'WhatsApp Image 2024-08-14 at 15.56.24.jpeg', 1, '2024-08-21', '2024-08-29', 'aktif', 2, '2');

-- --------------------------------------------------------

--
-- Struktur dari tabel `divisi`
--

CREATE TABLE `divisi` (
  `id` int(11) NOT NULL,
  `nama_divisi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `divisi`
--

INSERT INTO `divisi` (`id`, `nama_divisi`) VALUES
(1, 'prakom'),
(2, 'pd 1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan_divisi`
--

CREATE TABLE `karyawan_divisi` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jabatan` varchar(255) NOT NULL,
  `divisi_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `karyawan_divisi`
--

INSERT INTO `karyawan_divisi` (`id`, `nip`, `nama`, `jabatan`, `divisi_id`) VALUES
(1, '6', 'fahri', 'kabag', 1),
(2, '2', 'elisa', 'kasi', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `penilaian_tugas_id` int(11) DEFAULT NULL,
  `absensi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id`, `nilai_akhir`, `penilaian_tugas_id`, `absensi_id`) VALUES
(6, '99.20', 6, 42),
(7, '99.20', 6, 42),
(8, '99.20', 6, 42),
(9, '99.20', 6, 42),
(10, '20.00', 6, 42),
(11, '92.00', 6, 42),
(12, '92.00', 6, 42),
(13, '92.00', 6, 42),
(14, '92.00', 6, 42),
(15, '92.00', 6, 42),
(16, '92.00', 6, 42),
(17, '92.00', 6, 42),
(18, '0.00', NULL, NULL),
(19, '0.00', NULL, NULL),
(20, '0.00', NULL, NULL),
(21, '0.00', NULL, NULL),
(22, '0.00', NULL, NULL),
(23, '0.00', NULL, NULL),
(24, '0.00', NULL, NULL),
(25, '0.00', NULL, NULL),
(26, '92.00', 6, 42);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian_tugas`
--

CREATE TABLE `penilaian_tugas` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) DEFAULT NULL,
  `status` enum('Diberikan','Telah Dibuat','Selesai') DEFAULT NULL,
  `file_tugas` varchar(255) DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `penilaian_tugas`
--

INSERT INTO `penilaian_tugas` (`id`, `tugas_id`, `status`, `file_tugas`, `nilai`) VALUES
(6, 2, 'Selesai', 'WhatsApp Image 2024-09-05 at 13.45.44.jpeg', 90),
(7, 4, 'Selesai', 'WhatsApp Image 2024-08-22 at 14.53.29.jpeg', 100),
(8, 5, 'Selesai', 'WhatsApp Image 2024-08-22 at 14.53.29.jpeg', 6),
(9, 7, 'Selesai', 'WhatsApp Image 2024-08-20 at 15.35.13.jpeg', 66),
(10, 8, 'Selesai', 'formulir_cuti_1234567890-1.pdf', 90),
(11, 9, 'Selesai', 'formulir_cuti_12345678901.pdf', 80),
(12, 10, 'Selesai', 'formulir_cuti_1234567890 (4).pdf', 90),
(15, 11, 'Selesai', 'Rekomendasi_Keamanan_SPBE_PPT.pptx', 55),
(16, 12, 'Selesai', 'formulir_cuti_12345678901.pdf', 78);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `nama_tugas` varchar(100) DEFAULT NULL,
  `uraian_tugas` text,
  `target` varchar(100) DEFAULT NULL,
  `hasil` varchar(100) DEFAULT NULL,
  `tenggat_pengumpulan` date DEFAULT NULL,
  `anak_magang_id` varchar(20) DEFAULT NULL,
  `karyawan_divisi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id`, `nama_tugas`, `uraian_tugas`, `target`, `hasil`, `tenggat_pengumpulan`, `anak_magang_id`, `karyawan_divisi_id`) VALUES
(2, 'fddfsdf', 'fdfcdsc', 'csccsc', 'Tercapai', '2024-09-03', '1', 2),
(4, 'v v vv v v', ' vvvvvv', 'vvvvvv', 'Tercapai', '2024-09-02', '1', 2),
(5, 'bbbbbbbbb', 'bbbbbbbbb', 'bbbbbbbbb', 'Tercapai', '2024-09-02', '1', 2),
(7, 'yyyy', 'yyy', 'yyy', 'Tercapai', '2024-09-02', '1', 2),
(8, 'cvdcvdd', 'dcdcdcd', 'cdc cd d dc', 'Tercapai', '2024-09-03', '1', 2),
(9, 'vvvvvvvvvvvvvvvvvvvv', 'vvvvvvvvvvvvvvvvvvvvvvv', 'vvvvvvvvvvvvvvvvvvvv', 'Tercapai', '2024-09-04', '1', 2),
(10, 'aaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaa', 'Tercapai', '2024-09-04', '1', 2),
(11, 'hhhhhhhhhhhhhhh', 'hhhhhhhhhhhhhhhhh', 'hhhhhhhhhhhhhh', 'Tercapai', '2024-09-04', '1', 2),
(12, ',,,,,,,,,,,,,,,,,,,,,,,,,,,', ',,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,jadsjuasduaduiudehuiandnsdnduoaohduahduoanodnaosndoaisdoiadaoidnoiandoandoiadoiasoidaoidhoiashdoiahdoiahdoahodihaoidhaoidhaod', ',,,,,,,,,,,,,,,,,,,,,,,,', 'Tercapai', '2024-09-05', '1', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `validasi_by` (`validasi_by`),
  ADD KEY `fk_absensi_anak_magang` (`anak_magang_id`);

--
-- Indexes for table `akun_anak_magang`
--
ALTER TABLE `akun_anak_magang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_anak_magang_id` (`anak_magang_id`);

--
-- Indexes for table `akun_master`
--
ALTER TABLE `akun_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `akun_supervisor`
--
ALTER TABLE `akun_supervisor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `karyawan_divisi_id` (`karyawan_divisi_id`),
  ADD KEY `akun_supervisor_ibfk_1` (`created_by`);

--
-- Indexes for table `anak_magang`
--
ALTER TABLE `anak_magang`
  ADD PRIMARY KEY (`nim_nisn`),
  ADD KEY `anak_magang_ibfk_1` (`supervisor_id`);

--
-- Indexes for table `divisi`
--
ALTER TABLE `divisi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `karyawan_divisi`
--
ALTER TABLE `karyawan_divisi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `karyawan_divisi_ibfk_1` (`divisi_id`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_ibfk_1` (`penilaian_tugas_id`),
  ADD KEY `laporan_ibfk_2` (`absensi_id`);

--
-- Indexes for table `penilaian_tugas`
--
ALTER TABLE `penilaian_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_id` (`tugas_id`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anak_magang_id` (`anak_magang_id`),
  ADD KEY `karyawan_divisi_id` (`karyawan_divisi_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `akun_anak_magang`
--
ALTER TABLE `akun_anak_magang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `akun_master`
--
ALTER TABLE `akun_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `akun_supervisor`
--
ALTER TABLE `akun_supervisor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `divisi`
--
ALTER TABLE `divisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `karyawan_divisi`
--
ALTER TABLE `karyawan_divisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `penilaian_tugas`
--
ALTER TABLE `penilaian_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`validasi_by`) REFERENCES `karyawan_divisi` (`id`),
  ADD CONSTRAINT `fk_absensi_anak_magang` FOREIGN KEY (`anak_magang_id`) REFERENCES `anak_magang` (`nim_nisn`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `akun_anak_magang`
--
ALTER TABLE `akun_anak_magang`
  ADD CONSTRAINT `akun_anak_magang_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `akun_master` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_anak_magang_id` FOREIGN KEY (`anak_magang_id`) REFERENCES `anak_magang` (`nim_nisn`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `akun_supervisor`
--
ALTER TABLE `akun_supervisor`
  ADD CONSTRAINT `akun_supervisor_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `akun_master` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `karyawan_divisi`
--
ALTER TABLE `karyawan_divisi`
  ADD CONSTRAINT `karyawan_divisi_ibfk_1` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`penilaian_tugas_id`) REFERENCES `penilaian_tugas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`absensi_id`) REFERENCES `absensi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penilaian_tugas`
--
ALTER TABLE `penilaian_tugas`
  ADD CONSTRAINT `penilaian_tugas_ibfk_1` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`);

--
-- Ketidakleluasaan untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`anak_magang_id`) REFERENCES `anak_magang` (`nim_nisn`),
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`karyawan_divisi_id`) REFERENCES `karyawan_divisi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
