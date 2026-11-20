-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Nov 2026 pada 12.52
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ikes_gallery`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `nama`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Owner Ike\'s Gallery', 'admin@ikesgallery.id', '$2y$12$g3ruAKvXAUiiKe7XWFMQ/OBiU22xOWHS1mfqx873rh8HroGdT72V2', 'pOTcwk4yqMh15ngxoZ4wiWcE0bH5zMZ49rcb8fjH5SyawndMAFB4CK6OvHtG', '2026-11-20 01:02:26', '2026-11-20 01:02:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1795161900),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1795161900;', 1795161900);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pemesanan`
--

CREATE TABLE `detail_pemesanan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `layanan_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_satuan` bigint(20) UNSIGNED NOT NULL,
  `subtotal` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_pemesanan`
--

INSERT INTO `detail_pemesanan` (`id`, `pemesanan_id`, `layanan_id`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 1, 12500000, 12500000),
(2, 2, 1, 1, 12500000, 12500000),
(3, 3, 1, 1, 12500000, 12500000),
(4, 4, 1, 1, 12500000, 12500000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `galeri`
--

CREATE TABLE `galeri` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `kategori` enum('Wedding','Catering') NOT NULL,
  `foto` varchar(255) NOT NULL,
  `urutan` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoice`
--

CREATE TABLE `invoice` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `revisi` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `nomor_invoice` varchar(30) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `rincian` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rincian`)),
  `alasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `klien`
--

CREATE TABLE `klien` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(30) NOT NULL,
  `no_hp_normal` varchar(20) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `klien`
--

INSERT INTO `klien` (`id`, `nama`, `no_hp`, `no_hp_normal`, `alamat`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Ayu & Rizky', '08120000000', '628120000000', 'Indramayu', '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(2, 'Siti Nurhaliza', '08120000001', '628120000001', 'Indramayu', '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(3, 'Dewi & Fajar', '08120000002', '628120000002', 'Indramayu', '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(4, 'Rina Marlina', '08120000003', '628120000003', 'Indramayu', '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `kategori` enum('Wedding','Catering') NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` bigint(20) UNSIGNED NOT NULL,
  `satuan` varchar(30) NOT NULL DEFAULT '',
  `label` varchar(40) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `unggulan` tinyint(1) NOT NULL DEFAULT 0,
  `urutan` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id`, `nama`, `kategori`, `deskripsi`, `harga`, `satuan`, `label`, `foto`, `aktif`, `unggulan`, `urutan`, `created_at`, `updated_at`) VALUES
(1, 'Wedding Rias + Dekor', 'Wedding', 'Rias pengantin, busana akad & resepsi, serta dekorasi pelaminan elegan.', 12500000, '', NULL, 'photo-1465495976277-4387d4b0b4c6', 1, 0, 0, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(2, 'Wedding Lengkap', 'Wedding', 'Rias, dekorasi, katering 300 porsi, MC, dan dokumentasi foto-video.', 35000000, '', 'Terlaris', 'photo-1519741497674-611481863552', 1, 1, 1, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(3, 'Akad Intimate', 'Wedding', 'Paket akad sederhana untuk keluarga inti dengan dekorasi minimalis.', 8500000, '', NULL, 'photo-1511795409834-ef04bbd61622', 1, 0, 0, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(4, 'Wedding Premium Gedung', 'Wedding', 'Paket mewah full service di gedung, lengkap dengan entertainment.', 65000000, '', 'Premium', 'photo-1464366400600-7168b8af9bc3', 1, 1, 2, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(5, 'Nasi Box Premium', 'Catering', 'Nasi, ayam/daging, lauk pendamping, sambal, dan buah segar.', 45000, '/ porsi', NULL, 'photo-1504674900247-0877df9cc836', 1, 0, 0, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(6, 'Prasmanan Reguler', 'Catering', 'Menu prasmanan 8 macam hidangan, minimal 100 porsi.', 75000, '/ porsi', 'Favorit', 'photo-1555244162-803834f70033', 1, 1, 3, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(7, 'Prasmanan Gold', 'Catering', 'Menu premium dengan live station, dessert, dan pelayan berseragam.', 120000, '/ porsi', NULL, 'photo-1414235077428-338989a2e8c0', 1, 1, 4, '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(8, 'Tumpeng & Snack Box', 'Catering', 'Cocok untuk syukuran, arisan, dan acara kantor.', 35000, '/ box', NULL, 'photo-1555939594-58d7cb561ad1', 1, 0, 0, '2026-11-20 01:02:26', '2026-11-20 01:02:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000000_create_ikes_tables', 1),
(5, '2026_11_05_000001_add_foto_to_testimoni_table', 1),
(6, '2026_11_13_000001_add_bukti_to_pembayaran_table', 1),
(7, '2026_11_16_000001_add_aktif_to_layanan_table', 1),
(8, '2026_11_16_000002_add_diskon_and_riwayat_rincian', 1),
(9, '2026_11_16_000003_create_pengembalian_table', 1),
(10, '2026_11_16_000004_create_pengeluaran_table', 1),
(11, '2026_11_19_000001_add_dana_batal_to_pemesanan_table', 1),
(12, '2026_11_19_000002_add_unggulan_urutan_to_layanan_table', 1),
(13, '2026_11_19_000003_create_galeri_table', 1),
(14, '2026_11_19_000004_restrict_delete_and_soft_delete_pemesanan_klien', 1),
(15, '2026_11_19_000005_add_audit_trail_to_pembayaran', 1),
(16, '2026_11_19_000006_create_nomor_urut_table', 1),
(17, '2026_11_19_000007_freeze_invoice_with_revisions', 1),
(18, '2026_11_20_000001_add_no_hp_normal_to_klien', 1),
(19, '2026_11_20_064856_create_packages_table', 1),
(20, '2026_11_20_085311_create_pelanggans_table', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `nomor_urut`
--

CREATE TABLE `nomor_urut` (
  `tahun` smallint(5) UNSIGNED NOT NULL,
  `terakhir` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggans`
--

CREATE TABLE `pelanggans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_pembayaran` enum('DP','Pelunasan') NOT NULL,
  `metode_bayar` enum('Tunai','Transfer') NOT NULL,
  `nominal` bigint(20) UNSIGNED NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `bukti_transaksi` varchar(255) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `pemesanan_id`, `admin_id`, `jenis_pembayaran`, `metode_bayar`, `nominal`, `tanggal_bayar`, `bukti_transaksi`, `catatan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Pelunasan', 'Transfer', 12500000, '2026-09-20', NULL, NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(2, 2, NULL, 'Pelunasan', 'Transfer', 12500000, '2026-08-20', NULL, NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(3, 3, NULL, 'Pelunasan', 'Transfer', 12500000, '2026-07-20', NULL, NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(4, 4, NULL, 'Pelunasan', 'Transfer', 12500000, '2026-06-20', NULL, NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(20) NOT NULL,
  `klien_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_acara` date NOT NULL,
  `lokasi_acara` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL,
  `total_biaya` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `diskon` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status_pemesanan` enum('Menunggu Konfirmasi','Dikonfirmasi','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Konfirmasi',
  `status_pembayaran` enum('Belum Bayar','DP','Lunas') NOT NULL DEFAULT 'Belum Bayar',
  `dana_batal` varchar(12) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pemesanan`
--

INSERT INTO `pemesanan` (`id`, `kode`, `klien_id`, `tanggal_acara`, `lokasi_acara`, `catatan`, `total_biaya`, `diskon`, `status_pemesanan`, `status_pembayaran`, `dana_batal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'IKG-2025-001', 1, '2026-09-20', 'Indramayu', NULL, 12500000, 0, 'Selesai', 'Lunas', NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(2, 'IKG-2025-002', 2, '2026-08-20', 'Indramayu', NULL, 12500000, 0, 'Selesai', 'Lunas', NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(3, 'IKG-2025-003', 3, '2026-07-20', 'Indramayu', NULL, 12500000, 0, 'Selesai', 'Lunas', NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL),
(4, 'IKG-2025-004', 4, '2026-06-20', 'Indramayu', NULL, 12500000, 0, 'Selesai', 'Lunas', NULL, '2026-11-20 01:02:26', '2026-11-20 01:02:26', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `kategori` varchar(40) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `nominal` bigint(20) UNSIGNED NOT NULL,
  `metode` varchar(20) NOT NULL,
  `tanggal_pengembalian` date NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pembayaran`
--

CREATE TABLE `riwayat_pembayaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pembayaran_id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `aksi` varchar(10) NOT NULL,
  `data_lama` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_lama`)),
  `data_baru` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_baru`)),
  `alasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_reschedule`
--

CREATE TABLE `riwayat_reschedule` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_lama` date NOT NULL,
  `tanggal_baru` date NOT NULL,
  `alasan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_rincian`
--

CREATE TABLE `riwayat_rincian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `total_lama` bigint(20) UNSIGNED NOT NULL,
  `total_baru` bigint(20) UNSIGNED NOT NULL,
  `diskon` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `alasan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0vuMjw8XjlnriTIzFgYnaVXuddeZy6YxA78chcNB', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI0UnlPOGVZODJad3ZpZ1dYaGdTeExsaVdDdzhhNWlPMGN4RldBcUtBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pbiIsInJvdXRlIjoiYWRtaW4uZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInVybCI6eyJpbnRlbmRlZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC90ZXN0aW1vbmlcL2tpcmltIn19', 1795177856),
('yB0prfEkO7p8LJHsTaGiw4gaALkzEWEbNbzF6iO7', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI5eG14aHRrbDdXc0JmTnBPaGZnQ3J4TEoxSTZyMTlPVEkxeDdwRklyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC90ZXN0aW1vbmkiLCJyb3V0ZSI6InRlc3RpbW9uaSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6ImFkZTUwMWM1ZWI2MDA4MmU0YTgwYWQzZDcxNjRmMTgwYThiMTcyMWZmOWE3ODI0YmRmNDhjZGQ1MmQ0MzUxY2MifQ==', 1795163859);

-- --------------------------------------------------------

--
-- Struktur dari tabel `testimoni`
--

CREATE TABLE `testimoni` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `isi` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status_tampil` enum('Menunggu','Tampil','Disembunyikan') NOT NULL DEFAULT 'Menunggu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `testimoni`
--

INSERT INTO `testimoni` (`id`, `pemesanan_id`, `rating`, `isi`, `foto`, `status_tampil`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 'Rias dan dekorasinya cantik sekali, persis seperti yang kami impikan. Tim sangat ramah dan profesional dari awal sampai acara selesai.', NULL, 'Tampil', '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(2, 2, 5, 'Katering untuk acara lamaran anak kami enak semua, tamu banyak yang tambah. Penyajian rapi dan tepat waktu.', NULL, 'Tampil', '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(3, 3, 5, 'Paket Wedding Lengkap benar-benar membantu, semua urusan beres dalam satu tempat.', NULL, 'Tampil', '2026-11-20 01:02:26', '2026-11-20 01:02:26'),
(4, 4, 4, 'Dekorasi akad intimate-nya elegan dan harganya masuk akal. Komunikasinya cepat lewat WhatsApp.', NULL, 'Tampil', '2026-11-20 01:02:26', '2026-11-20 01:02:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_email_unique` (`email`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_pemesanan_layanan_id_foreign` (`layanan_id`),
  ADD KEY `detail_pemesanan_pemesanan_id_foreign` (`pemesanan_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `galeri_aktif_index` (`aktif`);

--
-- Indeks untuk tabel `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_nomor_invoice_unique` (`nomor_invoice`),
  ADD UNIQUE KEY `invoice_pemesanan_id_revisi_unique` (`pemesanan_id`,`revisi`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `klien`
--
ALTER TABLE `klien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `klien_no_hp_unique` (`no_hp`),
  ADD UNIQUE KEY `klien_no_hp_normal_unique` (`no_hp_normal`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `layanan_aktif_index` (`aktif`),
  ADD KEY `layanan_unggulan_index` (`unggulan`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `nomor_urut`
--
ALTER TABLE `nomor_urut`
  ADD PRIMARY KEY (`tahun`);

--
-- Indeks untuk tabel `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pelanggans`
--
ALTER TABLE `pelanggans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pelanggans_email_unique` (`email`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayaran_tanggal_bayar_index` (`tanggal_bayar`),
  ADD KEY `pembayaran_pemesanan_id_foreign` (`pemesanan_id`),
  ADD KEY `pembayaran_admin_id_foreign` (`admin_id`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pemesanan_kode_unique` (`kode`),
  ADD KEY `pemesanan_klien_id_foreign` (`klien_id`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengeluaran_admin_id_foreign` (`admin_id`),
  ADD KEY `pengeluaran_tanggal_index` (`tanggal`),
  ADD KEY `pengeluaran_kategori_index` (`kategori`);

--
-- Indeks untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengembalian_pemesanan_id_foreign` (`pemesanan_id`),
  ADD KEY `pengembalian_admin_id_foreign` (`admin_id`),
  ADD KEY `pengembalian_tanggal_pengembalian_index` (`tanggal_pengembalian`);

--
-- Indeks untuk tabel `riwayat_pembayaran`
--
ALTER TABLE `riwayat_pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_pembayaran_pembayaran_id_foreign` (`pembayaran_id`),
  ADD KEY `riwayat_pembayaran_pemesanan_id_foreign` (`pemesanan_id`),
  ADD KEY `riwayat_pembayaran_admin_id_foreign` (`admin_id`),
  ADD KEY `riwayat_pembayaran_aksi_index` (`aksi`);

--
-- Indeks untuk tabel `riwayat_reschedule`
--
ALTER TABLE `riwayat_reschedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_reschedule_admin_id_foreign` (`admin_id`),
  ADD KEY `riwayat_reschedule_pemesanan_id_foreign` (`pemesanan_id`);

--
-- Indeks untuk tabel `riwayat_rincian`
--
ALTER TABLE `riwayat_rincian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_rincian_admin_id_foreign` (`admin_id`),
  ADD KEY `riwayat_rincian_pemesanan_id_foreign` (`pemesanan_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `testimoni_pemesanan_id_unique` (`pemesanan_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `klien`
--
ALTER TABLE `klien`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelanggans`
--
ALTER TABLE `pelanggans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pembayaran`
--
ALTER TABLE `riwayat_pembayaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_reschedule`
--
ALTER TABLE `riwayat_reschedule`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_rincian`
--
ALTER TABLE `riwayat_rincian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pemesanan`
--
ALTER TABLE `detail_pemesanan`
  ADD CONSTRAINT `detail_pemesanan_layanan_id_foreign` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`),
  ADD CONSTRAINT `detail_pemesanan_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `pembayaran_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_klien_id_foreign` FOREIGN KEY (`klien_id`) REFERENCES `klien` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `pengembalian_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_pembayaran`
--
ALTER TABLE `riwayat_pembayaran`
  ADD CONSTRAINT `riwayat_pembayaran_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `riwayat_pembayaran_pembayaran_id_foreign` FOREIGN KEY (`pembayaran_id`) REFERENCES `pembayaran` (`id`),
  ADD CONSTRAINT `riwayat_pembayaran_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_reschedule`
--
ALTER TABLE `riwayat_reschedule`
  ADD CONSTRAINT `riwayat_reschedule_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `riwayat_reschedule_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_rincian`
--
ALTER TABLE `riwayat_rincian`
  ADD CONSTRAINT `riwayat_rincian_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `riwayat_rincian_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  ADD CONSTRAINT `testimoni_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
