-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Jun 2026 pada 10.16
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pojok_kafe`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `nama_kategori`, `created_at`) VALUES
(1, 'makanan', '2026-06-23 11:52:15'),
(2, 'minuman', '2026-06-23 11:52:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `nama`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'jabbar', '08764098653', 'pematang', '2026-06-25 19:43:56'),
(2, 'jabbar', '08764098653', 'pematang', '2026-06-26 09:18:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `debts`
--

CREATE TABLE `debts` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `total_hutang` decimal(12,2) NOT NULL,
  `sisa_hutang` decimal(12,2) NOT NULL,
  `status` enum('belum_lunas','lunas') DEFAULT 'belum_lunas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `debts`
--

INSERT INTO `debts` (`id`, `customer_id`, `transaction_id`, `total_hutang`, `sisa_hutang`, `status`, `created_at`) VALUES
(1, 1, 47, 20000.00, 0.00, 'lunas', '2026-06-25 19:43:56'),
(2, 2, 58, 18000.00, 18000.00, 'belum_lunas', '2026-06-26 09:18:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `debt_payments`
--

CREATE TABLE `debt_payments` (
  `id` int(11) NOT NULL,
  `debt_id` int(11) NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `metode` enum('cash','qris') DEFAULT 'cash',
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `debt_payments`
--

INSERT INTO `debt_payments` (`id`, `debt_id`, `jumlah_bayar`, `metode`, `tanggal`) VALUES
(1, 1, 20000.00, 'qris', '2026-06-26 17:12:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) DEFAULT 0,
  `foto` varchar(255) DEFAULT NULL,
  `stok_minimum` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `category_id`, `nama_produk`, `harga`, `stok`, `foto`, `stok_minimum`, `created_at`) VALUES
(8, NULL, 'Rice Bowl Telur', 10000.00, 30, 'prod_6a3b6bc548337.jpeg', 5, '2026-06-23 04:23:35'),
(9, NULL, 'Rice Bowl Ayam + Telur', 15000.00, 30, 'prod_6a3b6bb505a6b.jpeg', 5, '2026-06-23 04:26:08'),
(10, NULL, 'Rice Bowl Ayam  + Mie', 15000.00, 30, 'prod_6a3b6ba6d2f00.jpeg', 5, '2026-06-23 04:27:08'),
(11, NULL, 'Rice Bowl Ayam', 10000.00, 30, 'prod_6a3b6b63e0a94.jpeg', 5, '2026-06-23 04:27:50'),
(12, NULL, 'Rice Bowl Telur + Mie', 15000.00, 30, 'prod_6a3b6b546ac91.jpeg', 5, '2026-06-23 04:28:30'),
(13, NULL, 'Rice Bowl Ayam + Telur + Mie', 20000.00, 32, 'prod_6a3b6b41a2b66.jpeg', 5, '2026-06-23 04:29:12'),
(14, NULL, 'Rice Bowl Jumbo Ayam/Telur', 15000.00, 40, 'prod_6a3b6b2b773e2.jpeg', 5, '2026-06-23 04:29:37'),
(15, NULL, 'Rice Bowl Jumbo Ayam + Telur', 18000.00, 69, 'prod_6a3b6b14cf14d.jpeg', 5, '2026-06-23 04:30:12'),
(16, NULL, 'Mie Goreng Telur', 10000.00, 50, 'prod_6a3b6b036ff08.jpeg', 5, '2026-06-23 04:30:31'),
(17, NULL, 'Es Jeruk', 5000.00, 70, 'prod_6a3b6af2d0381.jpeg', 5, '2026-06-23 04:30:50'),
(18, NULL, 'Teh', 5000.00, 70, 'prod_6a3b6adb3a702.jpeg', 5, '2026-06-23 04:31:22'),
(19, NULL, 'Kopi', 5000.00, 47, 'prod_6a3b6aca7c363.jpeg', 5, '2026-06-23 04:31:42'),
(22, NULL, 'Nipis Madu', 5000.00, 59, 'prod_6a3a2ee718daf.jpg', 5, '2026-06-23 04:32:49'),
(23, 1, 'Pop Mie', 10000.00, 62, 'prod_6a3b670451391.png', 5, '2026-06-23 04:33:27'),
(24, NULL, 'Teh Pucuk', 5000.00, 19, 'prod_6a3b66bb0ca18.jpeg', 5, '2026-06-24 05:10:19'),
(25, 2, 'Mineral', 5000.00, 36, 'prod_6a3b671d31117.jpeg', 5, '2026-06-24 05:11:08'),
(27, 2, 'nipis madu spesial', 5000.00, 9, 'prod_6a3e411b2165e.jpeg', 5, '2026-06-26 09:06:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(2, 'karyawan'),
(1, 'owner');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stok_lama` int(11) NOT NULL,
  `stok_baru` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `qty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stock_logs`
--

INSERT INTO `stock_logs` (`id`, `product_id`, `stok_lama`, `stok_baru`, `keterangan`, `created_at`, `jenis`, `qty`) VALUES
(5, 23, 30, 29, 'Terjual via transaksi TRX-20260623-856D4', '2026-06-23 13:16:35', 'masuk', 0),
(6, 22, 30, 29, 'Terjual via transaksi TRX-20260623-856D4', '2026-06-23 13:16:35', 'masuk', 0),
(8, 23, 29, 28, 'Terjual via transaksi TRX-20260623-5276B', '2026-06-23 13:34:43', 'masuk', 0),
(9, 22, 29, 28, 'Terjual via transaksi TRX-20260623-5276B', '2026-06-23 13:34:43', 'masuk', 0),
(11, 23, 28, 27, 'Terjual via transaksi TRX-20260623-56265', '2026-06-23 19:07:36', 'masuk', 0),
(12, 22, 28, 27, 'Terjual via transaksi TRX-20260623-56265', '2026-06-23 19:07:36', 'masuk', 0),
(14, 23, 27, 26, 'Terjual via transaksi TRX-20260623-4EBAE', '2026-06-23 19:56:39', 'masuk', 0),
(15, 22, 27, 26, 'Terjual via transaksi TRX-20260623-4EBAE', '2026-06-23 19:56:39', 'masuk', 0),
(16, 23, 26, 25, 'Terjual via transaksi TRX-20260624-DC56E', '2026-06-24 04:55:58', 'masuk', 0),
(17, 22, 26, 25, 'Terjual via transaksi TRX-20260624-DC56E', '2026-06-24 04:55:58', 'masuk', 0),
(18, 19, 30, 29, 'Terjual via transaksi TRX-20260624-DC56E', '2026-06-24 04:55:58', 'masuk', 0),
(19, 23, 25, 23, 'Terjual via transaksi TRX-20260624-61DB8', '2026-06-24 04:56:14', 'masuk', 0),
(20, 25, 30, 29, 'Terjual via transaksi TRX-20260624-49DD6', '2026-06-24 07:53:07', 'masuk', 0),
(21, 24, 30, 29, 'Terjual via transaksi TRX-20260624-49DD6', '2026-06-24 07:53:07', 'masuk', 0),
(22, 23, 23, 22, 'Terjual via transaksi TRX-20260624-49DD6', '2026-06-24 07:53:07', 'masuk', 0),
(23, 22, 25, 24, 'Terjual via transaksi TRX-20260624-49DD6', '2026-06-24 07:53:07', 'masuk', 0),
(24, 25, 4, 3, 'Terjual via transaksi TRX-20260625-0A87D', '2026-06-25 07:32:45', 'masuk', 0),
(25, 24, 29, 28, 'Terjual via transaksi TRX-20260625-61153', '2026-06-25 15:10:05', 'masuk', 0),
(26, 23, 22, 21, 'Terjual via transaksi TRX-20260625-61153', '2026-06-25 15:10:05', 'masuk', 0),
(27, 22, 24, 23, 'Terjual via transaksi TRX-20260625-61153', '2026-06-25 15:10:05', 'masuk', 0),
(28, 24, 28, 27, 'Terjual via transaksi TRX-20260625-C523F', '2026-06-25 15:20:05', 'masuk', 0),
(29, 23, 21, 20, 'Terjual via transaksi TRX-20260625-C523F', '2026-06-25 15:20:05', 'masuk', 0),
(30, 22, 23, 22, 'Terjual via transaksi TRX-20260625-C523F', '2026-06-25 15:20:05', 'masuk', 0),
(31, 24, 27, 26, 'Terjual via transaksi TRX-20260625-02DDA', '2026-06-25 16:41:26', 'keluar', 1),
(32, 25, 3, 2, 'Terjual via transaksi TRX-20260625-9D094', '2026-06-25 16:41:48', 'keluar', 1),
(33, 24, 26, 25, 'Terjual via transaksi TRX-20260625-9D094', '2026-06-25 16:41:48', 'keluar', 1),
(34, 23, 20, 19, 'Terjual via transaksi TRX-20260625-9D094', '2026-06-25 16:41:48', 'keluar', 1),
(35, 24, 25, 30, 'Penyesuaian stok produk', '2026-06-25 17:07:40', 'masuk', 5),
(37, 25, 10, 15, 'Penyesuaian stok produk', '2026-06-25 17:11:34', 'masuk', 5),
(38, 25, 15, 20, 'Penyesuaian stok produk', '2026-06-25 17:20:55', 'masuk', 5),
(40, 25, 20, 30, 'Penambahan stok manual', '2026-06-25 17:45:59', 'masuk', 10),
(41, 24, 30, 40, 'Penambahan stok manual', '2026-06-25 17:46:40', 'masuk', 10),
(42, 25, 30, 31, 'Penambahan stok manual', '2026-06-25 17:55:15', 'masuk', 1),
(43, 22, 22, 62, 'Penambahan stok manual', '2026-06-25 17:59:42', 'masuk', 40),
(44, 25, 31, 71, 'Penambahan stok manual', '2026-06-25 18:01:54', 'masuk', 40),
(45, 23, 19, 59, 'Penambahan stok manual', '2026-06-25 18:05:59', 'masuk', 40),
(46, 23, 59, 69, 'Penambahan stok manual', '2026-06-25 18:13:36', 'masuk', 10),
(47, 17, 30, 70, 'Penambahan stok manual', '2026-06-25 18:17:11', 'masuk', 40),
(48, 18, 30, 70, 'Penambahan stok manual', '2026-06-25 18:18:05', 'masuk', 40),
(49, 16, 30, 40, 'Penambahan stok manual', '2026-06-25 18:29:15', 'masuk', 10),
(50, 13, 30, 40, 'Penambahan stok manual', '2026-06-25 18:29:45', 'masuk', 10),
(51, 14, 30, 40, 'Penyesuaian stok produk', '2026-06-25 18:30:29', 'masuk', 10),
(52, 15, 30, 50, 'Penambahan stok manual', '2026-06-25 18:40:21', 'masuk', 20),
(53, 15, 50, 70, 'Penambahan stok manual', '2026-06-25 18:40:31', 'masuk', 20),
(54, 19, 29, 49, 'Penambahan stok manual', '2026-06-25 18:45:24', 'masuk', 20),
(55, 25, 71, 70, 'Terjual via transaksi TRX-20260625-931D2', '2026-06-25 19:21:31', 'keluar', 1),
(56, 24, 40, 39, 'Terjual via transaksi TRX-20260625-931D2', '2026-06-25 19:21:31', 'keluar', 1),
(57, 25, 70, 69, 'Terjual via transaksi TRX-20260625-7BBDC', '2026-06-25 19:22:15', 'keluar', 1),
(58, 24, 39, 38, 'Terjual via transaksi TRX-20260625-7BBDC', '2026-06-25 19:22:15', 'keluar', 1),
(59, 25, 69, 68, 'Terjual via transaksi TRX-20260625-7CCCB', '2026-06-25 19:29:43', 'keluar', 1),
(60, 24, 38, 36, 'Terjual via transaksi TRX-20260625-7CCCB', '2026-06-25 19:29:43', 'keluar', 2),
(61, 23, 69, 68, 'Terjual via transaksi TRX-20260625-7CCCB', '2026-06-25 19:29:43', 'keluar', 1),
(62, 24, 36, 35, 'Terjual via transaksi TRX-20260625-A09F4', '2026-06-25 19:43:56', 'keluar', 1),
(63, 25, 68, 67, 'Terjual via transaksi TRX-20260625-A09F4', '2026-06-25 19:43:56', 'keluar', 1),
(64, 23, 68, 67, 'Terjual via transaksi TRX-20260625-A09F4', '2026-06-25 19:43:56', 'keluar', 1),
(65, 25, 67, 66, 'Terjual via transaksi TRX-20260625-5BFC5', '2026-06-25 19:46:18', 'keluar', 1),
(66, 23, 67, 66, 'Terjual via transaksi TRX-20260625-5BFC5', '2026-06-25 19:46:18', 'keluar', 1),
(67, 24, 35, 34, 'Terjual via transaksi TRX-20260625-5BFC5', '2026-06-25 19:46:18', 'keluar', 1),
(68, 25, 66, 65, 'Terjual via transaksi TRX-20260625-CC73C', '2026-06-25 19:48:09', 'keluar', 1),
(69, 24, 34, 33, 'Terjual via transaksi TRX-20260625-CC73C', '2026-06-25 19:48:09', 'keluar', 1),
(70, 25, 65, 64, 'Terjual via transaksi TRX-20260625-33AB2', '2026-06-25 20:23:06', 'keluar', 1),
(71, 24, 33, 32, 'Terjual via transaksi TRX-20260625-33AB2', '2026-06-25 20:23:06', 'keluar', 1),
(72, 23, 66, 65, 'Terjual via transaksi TRX-20260625-33AB2', '2026-06-25 20:23:06', 'keluar', 1),
(73, 22, 62, 61, 'Terjual via transaksi TRX-20260625-33AB2', '2026-06-25 20:23:06', 'keluar', 1),
(74, 19, 49, 48, 'Terjual via transaksi TRX-20260625-33AB2', '2026-06-25 20:23:06', 'keluar', 1),
(75, 25, 64, 4, 'Penyesuaian stok produk', '2026-06-26 03:54:23', 'keluar', 60),
(76, 24, 32, 0, 'Penyesuaian stok produk', '2026-06-26 03:54:36', 'keluar', 32),
(77, 25, 4, 3, 'Terjual via transaksi TRX-20260626-41BCA', '2026-06-26 03:59:33', 'keluar', 1),
(78, 22, 61, 59, 'Terjual via transaksi TRX-20260626-41BCA', '2026-06-26 03:59:33', 'keluar', 2),
(79, 25, 3, 2, 'Terjual via transaksi TRX-20260626-89423', '2026-06-26 04:22:13', 'keluar', 1),
(80, 23, 65, 64, 'Terjual via transaksi TRX-20260626-89423', '2026-06-26 04:22:13', 'keluar', 1),
(81, 25, 2, 0, 'Terjual via transaksi TRX-20260626-4A19F', '2026-06-26 04:37:32', 'keluar', 2),
(82, 23, 64, 63, 'Terjual via transaksi TRX-20260626-361EB', '2026-06-26 04:37:45', 'keluar', 1),
(83, 23, 63, 62, 'Terjual via transaksi TRX-20260626-5DA28', '2026-06-26 04:38:51', 'keluar', 1),
(84, 19, 48, 47, 'Terjual via transaksi TRX-20260626-32488', '2026-06-26 04:39:08', 'keluar', 1),
(85, 25, 0, 20, 'Penambahan stok manual', '2026-06-26 04:57:32', 'masuk', 20),
(86, 24, 0, 20, 'Penambahan stok manual', '2026-06-26 04:59:48', 'masuk', 20),
(87, 8, 20, 30, 'Penambahan stok manual', '2026-06-26 05:12:02', 'masuk', 10),
(88, 16, 40, 50, 'Penambahan stok manual', '2026-06-26 05:17:15', 'masuk', 10),
(89, 25, 20, 40, 'Penambahan stok manual', '2026-06-26 09:05:51', 'masuk', 20),
(90, 27, 0, 20, 'Stok awal produk baru', '2026-06-26 09:06:35', 'masuk', 20),
(91, 27, 20, 12, 'Terjual via transaksi TRX-20260626-854BD', '2026-06-26 09:16:36', 'keluar', 8),
(92, 25, 40, 39, 'Terjual via transaksi TRX-20260626-854BD', '2026-06-26 09:16:36', 'keluar', 1),
(93, 24, 20, 19, 'Terjual via transaksi TRX-20260626-854BD', '2026-06-26 09:16:36', 'keluar', 1),
(94, 15, 70, 69, 'Terjual via transaksi TRX-20260626-68F66', '2026-06-26 09:18:30', 'keluar', 1),
(95, 13, 40, 32, 'Terjual via transaksi TRX-20260626-0E12E', '2026-06-26 10:06:06', 'keluar', 8),
(96, 27, 12, 11, 'Terjual via transaksi TRX-20260626-A7EA2', '2026-06-26 10:28:20', 'keluar', 1),
(97, 25, 39, 38, 'Terjual via transaksi TRX-20260626-A7EA2', '2026-06-26 10:28:20', 'keluar', 1),
(98, 27, 11, 10, 'Terjual via transaksi TRX-20260626-00F07', '2026-06-26 10:33:02', 'keluar', 1),
(99, 25, 38, 37, 'Terjual via transaksi TRX-20260626-00F07', '2026-06-26 10:33:02', 'keluar', 1),
(100, 27, 10, 9, 'Terjual via transaksi TRX-20260629-93457', '2026-06-29 03:18:00', 'keluar', 1),
(101, 25, 37, 36, 'Terjual via transaksi TRX-20260629-93457', '2026-06-29 03:18:00', 'keluar', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(30) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `uang_diterima` decimal(12,2) DEFAULT 0.00,
  `kembalian` decimal(12,2) DEFAULT 0.00,
  `metode_pembayaran` enum('cash','qris') NOT NULL DEFAULT 'cash',
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `kode_transaksi`, `user_id`, `total`, `uang_diterima`, `kembalian`, `metode_pembayaran`, `tanggal`) VALUES
(1, 'TRX20260612070240', 1, 10000.00, 10000.00, 0.00, 'cash', '2026-06-12 12:02:40'),
(2, 'TRX20260612074020', 1, 10000.00, 10000.00, 0.00, 'cash', '2026-06-12 12:40:20'),
(3, 'TRX20260612074149', 1, 15000.00, 15000.00, 0.00, 'cash', '2026-06-12 12:41:49'),
(4, 'TRX20260612074452', 1, 15015.00, 15015.00, 0.00, 'cash', '2026-06-12 12:44:52'),
(5, 'TRX20260612074724', 1, 15015.00, 15015.00, 0.00, 'cash', '2026-06-12 12:47:24'),
(6, 'TRX20260612075349', 1, 25000.00, 25000.00, 0.00, 'cash', '2026-06-12 12:53:49'),
(7, 'TRX20260612075735', 1, 10000.00, 10000.00, 0.00, 'cash', '2026-06-12 12:57:35'),
(8, 'TRX20260612080314', 1, 25000.00, 25000.00, 0.00, 'cash', '2026-06-12 13:03:14'),
(9, 'TRX20260612080359', 1, 10000.00, 10000.00, 0.00, 'cash', '2026-06-12 13:03:59'),
(10, 'TRX20260612111358', 1, 0.00, 0.00, 0.00, 'cash', '2026-06-12 16:13:58'),
(11, 'TRX20260612112722', 1, 25000.00, 25000.00, 0.00, 'cash', '2026-06-12 16:27:22'),
(12, 'TRX20260612112832', 1, 35015.00, 35015.00, 0.00, 'cash', '2026-06-12 16:28:32'),
(13, 'TRX20260612112846', 1, 30030.00, 30030.00, 0.00, 'cash', '2026-06-12 16:28:46'),
(14, 'TRX20260612124704', 1, 15015.00, 15015.00, 0.00, 'cash', '2026-06-12 17:47:04'),
(15, 'TRX20260616065155', 1, 25015.00, 25015.00, 0.00, 'cash', '2026-06-16 11:51:55'),
(16, 'TRX20260616093130', 1, 15000.00, 15000.00, 0.00, 'cash', '2026-06-16 14:31:30'),
(17, 'TRX20260620130205', 1, 60000.00, 60000.00, 0.00, 'cash', '2026-06-20 18:02:05'),
(18, 'TRX20260622052835', 1, 20000.00, 20000.00, 0.00, 'cash', '2026-06-22 10:28:35'),
(19, 'TRX20260622053238', 1, 0.00, 0.00, 0.00, 'cash', '2026-06-22 10:32:38'),
(20, NULL, 1, 10000.00, 0.00, 0.00, 'cash', '2026-06-22 05:44:05'),
(21, NULL, 1, 10000.00, 0.00, 0.00, 'cash', '2026-06-22 05:49:03'),
(22, NULL, 1, 20000.00, 0.00, 0.00, 'cash', '2026-06-22 05:50:40'),
(23, NULL, 1, 20000.00, 0.00, 0.00, 'cash', '2026-06-22 05:59:53'),
(24, 'TRX-20260622-B56E3', 1, 30000.00, 30000.00, 0.00, 'cash', '2026-06-22 11:14:09'),
(25, 'TRX-20260622-10612', 1, 10000.00, 10000.00, 0.00, 'qris', '2026-06-22 11:18:17'),
(26, 'TRX-20260623-7E70E', 1, 20000.00, 20000.00, 0.00, 'cash', '2026-06-23 11:11:07'),
(27, 'TRX-20260623-736D1', 1, 20000.00, 50000.00, 30000.00, 'cash', '2026-06-23 11:11:36'),
(28, 'TRX-20260623-856D4', 1, 20000.00, 50000.00, 30000.00, 'cash', '2026-06-23 20:16:35'),
(29, 'TRX-20260623-5276B', 5, 20000.00, 20000.00, 0.00, 'cash', '2026-06-23 20:34:43'),
(30, 'TRX-20260623-56265', 6, 20000.00, 20000.00, 0.00, 'cash', '2026-06-24 02:07:36'),
(31, 'TRX-20260623-4EBAE', 1, 15000.00, 15000.00, 0.00, 'cash', '2026-06-24 02:56:39'),
(32, 'TRX-20260624-DC56E', 5, 20000.00, 50000.00, 30000.00, 'cash', '2026-06-24 11:55:58'),
(33, 'TRX-20260624-61DB8', 5, 20000.00, 20000.00, 0.00, 'cash', '2026-06-24 11:56:14'),
(34, 'TRX-20260624-49DD6', 6, 25000.00, 55000.00, 30000.00, 'cash', '2026-06-24 14:53:07'),
(35, 'TRX-20260625-0A87D', 6, 5000.00, 10000.00, 5000.00, 'cash', '2026-06-25 14:32:45'),
(36, 'TRX-20260625-61153', 6, 20000.00, 20000.00, 0.00, 'cash', '2026-06-25 22:10:05'),
(37, 'TRX-20260625-C523F', 6, 20000.00, 20000.00, 0.00, 'cash', '2026-06-25 22:20:05'),
(42, 'TRX-20260625-02DDA', 6, 5000.00, 10000.00, 5000.00, 'cash', '2026-06-25 23:41:26'),
(43, 'TRX-20260625-9D094', 6, 20000.00, 50000.00, 30000.00, 'cash', '2026-06-25 23:41:48'),
(44, 'TRX-20260625-931D2', 6, 10000.00, 20000.00, 10000.00, 'cash', '2026-06-26 02:21:31'),
(45, 'TRX-20260625-7BBDC', 6, 10000.00, 10000.00, 0.00, 'cash', '2026-06-26 02:22:15'),
(46, 'TRX-20260625-7CCCB', 6, 25000.00, 30000.00, 5000.00, 'cash', '2026-06-26 02:29:43'),
(47, 'TRX-20260625-A09F4', 6, 20000.00, 0.00, 0.00, '', '2026-06-26 02:43:56'),
(48, 'TRX-20260625-5BFC5', 6, 20000.00, 0.00, -20000.00, 'qris', '2026-06-26 02:46:18'),
(49, 'TRX-20260625-CC73C', 6, 10000.00, 0.00, -10000.00, 'qris', '2026-06-26 02:48:09'),
(50, 'TRX-20260625-33AB2', 6, 30000.00, 0.00, -30000.00, 'qris', '2026-06-26 03:23:06'),
(51, 'TRX-20260626-41BCA', 6, 15000.00, 0.00, -15000.00, 'qris', '2026-06-26 10:59:33'),
(52, 'TRX-20260626-89423', 6, 15000.00, 0.00, -15000.00, 'qris', '2026-06-26 11:22:13'),
(53, 'TRX-20260626-4A19F', 6, 10000.00, 10000.00, 0.00, 'cash', '2026-06-26 11:37:32'),
(54, 'TRX-20260626-361EB', 6, 10000.00, 10000.00, 0.00, 'qris', '2026-06-26 11:37:45'),
(55, 'TRX-20260626-5DA28', 6, 10000.00, 10000.00, 0.00, 'cash', '2026-06-26 11:38:51'),
(56, 'TRX-20260626-32488', 6, 5000.00, 5000.00, 0.00, 'qris', '2026-06-26 11:39:08'),
(57, 'TRX-20260626-854BD', 6, 50000.00, 100000.00, 50000.00, 'cash', '2026-06-26 16:16:36'),
(58, 'TRX-20260626-68F66', 6, 18000.00, 0.00, 0.00, '', '2026-06-26 16:18:30'),
(59, 'TRX-20260626-0E12E', 6, 160000.00, 160000.00, 0.00, 'qris', '2026-06-26 17:06:06'),
(60, 'TRX-20260626-A7EA2', 6, 10000.00, 20000.00, 10000.00, 'cash', '2026-06-26 17:28:20'),
(61, 'TRX-20260626-00F07', 6, 10000.00, 10000.00, 0.00, 'cash', '2026-06-26 17:33:02'),
(62, 'TRX-20260629-93457', 6, 10000.00, 10000.00, 0.00, 'qris', '2026-06-29 10:18:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaction_details`
--

INSERT INTO `transaction_details` (`id`, `transaction_id`, `product_id`, `qty`, `harga_satuan`, `subtotal`) VALUES
(34, 28, 23, 1, 10000.00, 10000.00),
(35, 28, 22, 1, 5000.00, 5000.00),
(37, 29, 23, 1, 10000.00, 10000.00),
(38, 29, 22, 1, 5000.00, 5000.00),
(40, 30, 23, 1, 10000.00, 10000.00),
(41, 30, 22, 1, 5000.00, 5000.00),
(43, 31, 23, 1, 10000.00, 10000.00),
(44, 31, 22, 1, 5000.00, 5000.00),
(45, 32, 23, 1, 10000.00, 10000.00),
(46, 32, 22, 1, 5000.00, 5000.00),
(47, 32, 19, 1, 5000.00, 5000.00),
(48, 33, 23, 2, 10000.00, 20000.00),
(49, 34, 25, 1, 5000.00, 5000.00),
(50, 34, 24, 1, 5000.00, 5000.00),
(51, 34, 23, 1, 10000.00, 10000.00),
(52, 34, 22, 1, 5000.00, 5000.00),
(53, 35, 25, 1, 5000.00, 5000.00),
(54, 36, 24, 1, 5000.00, 5000.00),
(55, 36, 23, 1, 10000.00, 10000.00),
(56, 36, 22, 1, 5000.00, 5000.00),
(57, 37, 24, 1, 5000.00, 5000.00),
(58, 37, 23, 1, 10000.00, 10000.00),
(59, 37, 22, 1, 5000.00, 5000.00),
(64, 42, 24, 1, 5000.00, 5000.00),
(65, 43, 25, 1, 5000.00, 5000.00),
(66, 43, 24, 1, 5000.00, 5000.00),
(67, 43, 23, 1, 10000.00, 10000.00),
(68, 44, 25, 1, 5000.00, 5000.00),
(69, 44, 24, 1, 5000.00, 5000.00),
(70, 45, 25, 1, 5000.00, 5000.00),
(71, 45, 24, 1, 5000.00, 5000.00),
(72, 46, 25, 1, 5000.00, 5000.00),
(73, 46, 24, 2, 5000.00, 10000.00),
(74, 46, 23, 1, 10000.00, 10000.00),
(75, 47, 24, 1, 5000.00, 5000.00),
(76, 47, 25, 1, 5000.00, 5000.00),
(77, 47, 23, 1, 10000.00, 10000.00),
(78, 48, 25, 1, 5000.00, 5000.00),
(79, 48, 23, 1, 10000.00, 10000.00),
(80, 48, 24, 1, 5000.00, 5000.00),
(81, 49, 25, 1, 5000.00, 5000.00),
(82, 49, 24, 1, 5000.00, 5000.00),
(83, 50, 25, 1, 5000.00, 5000.00),
(84, 50, 24, 1, 5000.00, 5000.00),
(85, 50, 23, 1, 10000.00, 10000.00),
(86, 50, 22, 1, 5000.00, 5000.00),
(87, 50, 19, 1, 5000.00, 5000.00),
(88, 51, 25, 1, 5000.00, 5000.00),
(89, 51, 22, 2, 5000.00, 10000.00),
(90, 52, 25, 1, 5000.00, 5000.00),
(91, 52, 23, 1, 10000.00, 10000.00),
(92, 53, 25, 2, 5000.00, 10000.00),
(93, 54, 23, 1, 10000.00, 10000.00),
(94, 55, 23, 1, 10000.00, 10000.00),
(95, 56, 19, 1, 5000.00, 5000.00),
(96, 57, 27, 8, 5000.00, 40000.00),
(97, 57, 25, 1, 5000.00, 5000.00),
(98, 57, 24, 1, 5000.00, 5000.00),
(99, 58, 15, 1, 18000.00, 18000.00),
(100, 59, 13, 8, 20000.00, 160000.00),
(101, 60, 27, 1, 5000.00, 5000.00),
(102, 60, 25, 1, 5000.00, 5000.00),
(103, 61, 27, 1, 5000.00, 5000.00),
(104, 61, 25, 1, 5000.00, 5000.00),
(105, 62, 27, 1, 5000.00, 5000.00),
(106, 62, 25, 1, 5000.00, 5000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `nama_lengkap`, `email`, `foto`, `status`, `created_at`) VALUES
(1, 2, 'agus', '$2y$10$MSvrZadKwtVVNpFqN4/31unkKAZ5k5SJdsjDXPGGhGU1OzFbNREdO', 'agus', 'agus@gmail.com', 'user_1_1781586718.png', 'aktif', '2026-06-11 14:21:40'),
(3, 1, 'jabbar', '$2y$10$KvnCwdrSHbfz5/5wTBUMu.pGOxaJZEJFZGr2ISmdBUBcNwVv6RtFW', 'ahmad', 'ahmad@gmail.com', 'user_1781597943.png', 'aktif', '2026-06-16 05:59:23'),
(5, 2, 'arza', '$2y$10$TMw6pfF5T1eQq8wxsm3W3.CqYLefqqtObbf7.uFPdI1nN4qSMh9By', 'arza', 'arza@gmail.com', 'karyawan_5_1782237941.png', 'aktif', '2026-06-23 13:34:21'),
(6, 2, 'nanda', '$2y$10$vQm2PQwuDCodC2CyeG0qGOcUi9isMoGNbJEg4iPmIzYqBIKmyB3fW', 'nanda', 'nanda@gmail.com', 'karyawan_6a3acad8c2460.png', 'aktif', '2026-06-23 18:05:12');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indeks untuk tabel `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debt_id` (`debt_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `debts`
--
ALTER TABLE `debts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `debt_payments`
--
ALTER TABLE `debt_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `debts`
--
ALTER TABLE `debts`
  ADD CONSTRAINT `debts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `debts_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`);

--
-- Ketidakleluasaan untuk tabel `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD CONSTRAINT `debt_payments_ibfk_1` FOREIGN KEY (`debt_id`) REFERENCES `debts` (`id`);

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `stock_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `transaction_details_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
