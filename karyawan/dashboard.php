<?php
include "../koneksi.php";

/* =========================
   QUERY DATABASE (PAKAI SQL KAMU ASLI)
   ========================= */

$q1 = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) AS total FROM transactions");
$penjualan = mysqli_fetch_assoc($q1)['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions");
$transaksi = mysqli_fetch_assoc($q2)['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
$produk = mysqli_fetch_assoc($q3)['total'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stok <= stok_minimum");
$stokMenipis = mysqli_fetch_assoc($q4)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

body {
    min-height: max(884px, 100dvh);
}
</style>

</head>

<body class="bg-[#fff8f5] font-sans">

<!-- TOP BAR (TIDAK DIUBAH UI) -->
<header class="fixed top-0 w-full h-[56px] z-50 bg-[#ad6126] text-white flex items-center justify-between px-4 shadow">

    <h1 class="font-bold text-lg">Pojok Kafe</h1>

    <span class="material-symbols-outlined">notifications</span>

</header>

<!-- MAIN -->
<main class="pt-[56px] pb-[140px] px-4 space-y-6">

    <!-- GREETING -->
    <section class="mt-4">
        <h2 class="text-[16px] font-semibold">Halo, Kasir 👋</h2>
        <p class="text-[12px] text-gray-500">Dashboard penjualan hari ini</p>
    </section>

    <!-- CARD GRID (UI TETAP) -->
    <section class="grid grid-cols-2 gap-3">

        <!-- PENJUALAN -->
        <div class="bg-[#a9632c] text-white p-4 rounded-xl h-[120px] flex flex-col justify-between shadow">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">trending_up</span>
                <span>Penjualan</span>
            </div>
            <div>
                <p class="text-[12px] opacity-80">Total</p>
                <p class="text-[18px] font-bold">
                    Rp <?= number_format($penjualan,0,',','.') ?>
                </p>
            </div>
        </div>

        <!-- TRANSAKSI -->
        <div class="bg-white border p-4 rounded-xl h-[120px] flex flex-col justify-between shadow">
            <div class="flex items-center gap-2 text-[#8e4a0e]">
                <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                <span>Transaksi</span>
            </div>
            <div>
                <p class="text-[12px] text-gray-500">Total</p>
                <p class="text-[18px] font-bold"><?= $transaksi ?></p>
            </div>
        </div>

    </section>

</main>

<!-- NAVBAR (FIX PATH ERROR) -->
<?php include "navbar.php"; ?>

</body>
</html>