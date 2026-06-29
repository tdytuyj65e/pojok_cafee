-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Jun 2026 pada 18.16
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
(1, 'makanan', '2026-06-29 14:52:08'),
(2, 'minuman', '2026-06-29 14:52:15');

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
(1, 'jabbar', '082374995107', 'pematang gebernur', '2026-06-29 15:43:54');

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
(1, 1, 2, 5000.00, 0.00, 'lunas', '2026-06-29 15:43:54');

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
(1, 1, 5000.00, 'cash', '2026-06-29 22:44:26');

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
(1, 2, 'nipis madu', 5000.00, 5, 'prod_6a4286cd79b76.jpeg', 5, '2026-06-29 14:53:01'),
(2, 2, 'le mineral', 5000.00, 5, 'prod_6a4288ec2188d.jpeg', 5, '2026-06-29 15:02:04'),
(3, 2, 'teh pucuk', 5000.00, 5, 'prod_6a4289172a225.jpeg', 5, '2026-06-29 15:02:47'),
(4, 1, 'pop mie soto', 7000.00, 5, 'prod_6a42899ecd428.jpeg', 5, '2026-06-29 15:05:02'),
(5, 1, 'Rice Bowl Ayam', 10000.00, 5, 'prod_6a4289f61743f.jpeg', 5, '2026-06-29 15:06:30'),
(6, 1, 'mie telur', 10000.00, 5, 'prod_6a428a19da9d2.jpeg', 5, '2026-06-29 15:07:05'),
(7, 1, 'Rice Bowl Ayam + Telur', 15000.00, 5, 'prod_6a428a854e02b.jpeg', 5, '2026-06-29 15:08:53'),
(8, 1, 'rice bowl telur', 10000.00, 5, 'prod_6a428b983269d.jpeg', 5, '2026-06-29 15:13:28'),
(9, 1, 'rice bowl ayam + mie', 15000.00, 5, 'prod_6a428c04829c7.jpeg', 5, '2026-06-29 15:15:16'),
(10, 2, 'es jeruk', 5000.00, 5, 'prod_6a428c327a36a.jpeg', 5, '2026-06-29 15:16:02'),
(11, 1, 'rice bowl ayam + telur + mie', 20000.00, 5, 'prod_6a428d8604339.jpeg', 5, '2026-06-29 15:21:42'),
(12, 1, 'Rice Bowl Jumbo Ayam/Telur', 15000.00, 5, 'prod_6a428dbecf9fe.jpeg', 5, '2026-06-29 15:22:38'),
(13, 1, 'rice bowl jumbo ayam + telur', 20000.00, 5, 'prod_6a428e286c01e.jpeg', 5, '2026-06-29 15:24:24'),
(14, 1, 'mie + nasi', 10000.00, 5, 'prod_6a428e5372aa5.jpeg', 5, '2026-06-29 15:25:07'),
(15, 2, 'kopi', 5000.00, 4, 'prod_6a428e8410c2a.jpeg', 5, '2026-06-29 15:25:56'),
(16, 2, 'teh', 5000.00, 4, 'prod_6a428eb122c22.jpeg', 5, '2026-06-29 15:26:41');

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
(1, 1, 0, 5, 'Stok awal produk baru', '2026-06-29 14:53:01', 'masuk', 5),
(2, 2, 0, 5, 'Stok awal produk baru', '2026-06-29 15:02:04', 'masuk', 5),
(3, 3, 0, 5, 'Stok awal produk baru', '2026-06-29 15:02:47', 'masuk', 5),
(4, 4, 0, 5, 'Stok awal produk baru', '2026-06-29 15:05:02', 'masuk', 5),
(5, 5, 0, 5, 'Stok awal produk baru', '2026-06-29 15:06:30', 'masuk', 5),
(6, 6, 0, 5, 'Stok awal produk baru', '2026-06-29 15:07:05', 'masuk', 5),
(7, 7, 0, 5, 'Stok awal produk baru', '2026-06-29 15:08:53', 'masuk', 5),
(8, 8, 0, 5, 'Stok awal produk baru', '2026-06-29 15:13:28', 'masuk', 5),
(9, 9, 0, 5, 'Stok awal produk baru', '2026-06-29 15:15:16', 'masuk', 5),
(10, 10, 0, 5, 'Stok awal produk baru', '2026-06-29 15:16:02', 'masuk', 5),
(11, 11, 0, 5, 'Stok awal produk baru', '2026-06-29 15:21:42', 'masuk', 5),
(12, 12, 0, 5, 'Stok awal produk baru', '2026-06-29 15:22:38', 'masuk', 5),
(13, 13, 0, 5, 'Stok awal produk baru', '2026-06-29 15:24:24', 'masuk', 5),
(14, 14, 0, 5, 'Stok awal produk baru', '2026-06-29 15:25:07', 'masuk', 5),
(15, 15, 0, 5, 'Stok awal produk baru', '2026-06-29 15:25:56', 'masuk', 5),
(16, 16, 0, 5, 'Stok awal produk baru', '2026-06-29 15:26:41', 'masuk', 5),
(17, 16, 5, 4, 'Terjual via transaksi TRX-20260629-07D07', '2026-06-29 15:42:50', 'keluar', 1),
(18, 15, 5, 4, 'Terjual via transaksi TRX-20260629-41088', '2026-06-29 15:43:54', 'keluar', 1);

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
(1, 'TRX-20260629-07D07', 2, 5000.00, 5000.00, 0.00, 'cash', '2026-06-29 22:42:50'),
(2, 'TRX-20260629-41088', 2, 5000.00, 0.00, 0.00, '', '2026-06-29 22:43:54');

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
(1, 1, 16, 1, 5000.00, 5000.00),
(2, 2, 15, 1, 5000.00, 5000.00);

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
(1, 1, 'pojok_kafe', '$2y$10$zP/0ljyNrhe1yUdb4x/1Q.Zs8RxuoavcP8NkD1iQEGDTdeYtmRYZa', 'pojok_kafe', NULL, NULL, 'aktif', '2026-06-29 14:43:46'),
(2, 2, 'karyawan', '$2y$10$vo5rxYjNuLGSbQfxCJ6HJ.SAevGj9nv4IRPB/fSIcd.GUwQTK6Yyq', 'karyawan1', 'karyawan1@gmail.com', '', 'aktif', '2026-06-29 14:45:41');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `debts`
--
ALTER TABLE `debts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `debt_payments`
--
ALTER TABLE `debt_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
