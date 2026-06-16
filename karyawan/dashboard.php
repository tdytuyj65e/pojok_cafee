<?php
session_start();
include "../koneksi.php";

$user_id = $_SESSION['id'] ?? null;

$user = null;
$fotoUser = "https://ui-avatars.com/api/?name=User";

if ($user_id) {

    $user = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE id='$user_id'
    "));

    if (
        !empty($user['foto']) &&
        file_exists("../uploads/".$user['foto'])
    ) {
        $fotoUser = "../uploads/".$user['foto'];
    } else {
      $fotoUser = 'https://ui-avatars.com/api/?name=' . urlencode($user['nama_lengkap']);
    }
}

/* TOTAL PENJUALAN */
$q1 = mysqli_query($conn,"
    SELECT COALESCE(SUM(total),0) AS total
    FROM transactions
");
$penjualan = mysqli_fetch_assoc($q1)['total'];

/* TOTAL TRANSAKSI */
$q2 = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM transactions
");
$transaksi = mysqli_fetch_assoc($q2)['total'];

/* PRODUK */
$q5 = mysqli_query($conn,"
    SELECT *
    FROM products
    ORDER BY id DESC
");
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

<!-- TOP BAR -->
<header class="fixed top-0 w-full z-50 bg-[#ad6126] text-white h-[56px] shadow-md flex items-center justify-between px-4">

    <!-- Left: Logo + Nama -->
    <div class="flex items-center gap-3">
        <img src="<?= $fotoUser ?>" class="w-8 h-8 rounded-full object-cover border border-white">

        <h1 class="font-bold text-lg">
            Pojok Kafe
        </h1>
    </div>

    <!-- Right: Notification -->
    <span class="material-symbols-outlined cursor-pointer">
        notifications
    </span>

</header>

<!-- MAIN -->
<main class="pt-[56px] pb-[140px] px-4 space-y-6">

    <!-- GREETING -->
    <section class="mt-4">
        <h2 class="text-[16px] font-semibold">Halo, Kasir 👋</h2>
        <p class="text-[12px] text-gray-500">Dashboard penjualan hari ini</p>
    </section>

    <!-- CARD GRID -->
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


    <!-- PRODUK VISUAL BESAR -->
<!-- PRODUK VISUAL BESAR -->
<section class="space-y-2">

    <h3 class="font-semibold text-sm">Produk</h3>

    <!-- WRAPPER SCROLL HORIZONTAL -->
    <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-2">

        <?php while ($p = mysqli_fetch_assoc($q5)) { ?>

        <div class="flex-shrink-0 bg-surface rounded-xl overflow-hidden shadow-[0px_2px_12px_rgba(200,119,58,0.12)] border border-transparent hover:border-primary/20 transition-all active:scale-[0.98] w-full max-w-[220px]">

            <!-- IMAGE AREA -->
            <div class="aspect-square bg-primary-fixed-dim overflow-hidden relative">

                <?php
                    $img = !empty($p['foto'])
                        ? "../uploads/".$p['foto']
                        : "https://via.placeholder.com/300";
                ?>

                <img
                    src="<?= $img ?>"
                    class="w-full h-full object-cover"
                    alt="<?= $p['nama_produk'] ?>"
                />

                <!-- STOCK BADGE -->
                <div class="absolute top-2 right-2 bg-surface/90 backdrop-blur-sm px-2 py-1 rounded-lg shadow-sm">
                    <span class="text-[10px] font-medium text-gray-800">
                        Stok: <?= $p['stok'] ?>
                    </span>
                </div>

            </div>

            <!-- CONTENT -->
            <div class="p-3 space-y-1">

                <h3 class="text-sm font-semibold text-gray-800 truncate">
                    <?= $p['nama_produk'] ?>
                </h3>

                <p class="text-sm font-bold text-[#8e4a0e]">
                    Rp <?= number_format($p['harga'],0,',','.') ?>
                </p>

            </div>

        </div>

        <?php } ?>

    </div>

</section>

</main>

<!-- NAVBAR -->
<?php include "navbar_karyawan.php"; ?>

</body>
</html>