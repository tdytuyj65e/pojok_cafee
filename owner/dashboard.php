<?php
session_start();
include "../koneksi.php";

/* =========================
   CEK LOGIN (FIX SESSION)
========================= */
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   CEK ROLE OWNER
========================= */
if ((int)$_SESSION['role_id'] !== 1) {
    header("Location: ../karyawan/dashboard.php");
    exit;
}

/* =========================
   STATISTIK
========================== */

$q1 = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) total FROM transactions");
$total_penjualan = mysqli_fetch_assoc($q1)['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) total FROM transactions");
$total_transaksi = mysqli_fetch_assoc($q2)['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) total FROM products");
$total_produk = mysqli_fetch_assoc($q3)['total'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) total FROM users WHERE role_id = 2");
$total_karyawan = mysqli_fetch_assoc($q4)['total'];

$q5 = mysqli_query($conn, "
SELECT COUNT(*) total
FROM products
WHERE stok <= stok_minimum
");
$stok_menipis = mysqli_fetch_assoc($q5)['total'];

/* =========================
   PRODUK TERLARIS
========================== */

$produk_terlaris = mysqli_query($conn,"
SELECT
p.nama_produk,
SUM(td.qty) total_terjual
FROM transaction_details td
JOIN products p ON td.product_id = p.id
GROUP BY td.product_id
ORDER BY total_terjual DESC
LIMIT 5
");

/* =========================
   STOK MENIPIS
========================== */

$list_stok = mysqli_query($conn,"
SELECT *
FROM products
WHERE stok <= stok_minimum
ORDER BY stok ASC
LIMIT 5
");

/* =========================
   TRANSAKSI TERBARU
========================== */

$transaksi = mysqli_query($conn,"
SELECT
t.*,
u.nama_lengkap
FROM transactions t
JOIN users u ON t.user_id=u.id
ORDER BY t.id DESC
LIMIT 5
");

/* =========================
   GRAFIK
========================== */

$grafik = mysqli_query($conn,"
SELECT
MONTH(tanggal) bulan,
SUM(total) total
FROM transactions
WHERE YEAR(tanggal)=YEAR(CURDATE())
GROUP BY MONTH(tanggal)
");

$bulan = [];
$total = [];

while($g = mysqli_fetch_assoc($grafik)) {
    $bulan[] = $g['bulan'];
    $total[] = $g['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Dashboard Owner</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
}
</style>

</head>
<body class="bg-slate-100">

<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 text-white shadow">

        <h1 class="text-3xl font-bold">
            Dashboard Owner ☕
        </h1>

        <p class="mt-1 text-orange-100">
            Selamat datang,
            <?= $_SESSION['nama'] ?? 'Owner'; ?>
        </p>

    </div>

    <!-- CONTENT -->
    <div class="p-5 pb-24">

        <!-- CARD -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5">

            <div class="bg-green-500 text-white rounded-2xl p-5 shadow">
                <p class="text-sm opacity-80">Total Penjualan</p>
                <h2 class="text-2xl font-bold mt-2">
                    Rp <?= number_format($total_penjualan,0,',','.') ?>
                </h2>
            </div>

            <div class="bg-blue-500 text-white rounded-2xl p-5 shadow">
                <p class="text-sm opacity-80">Total Transaksi</p>
                <h2 class="text-2xl font-bold mt-2">
                    <?= $total_transaksi ?>
                </h2>
            </div>

            <div class="bg-purple-500 text-white rounded-2xl p-5 shadow">
                <p class="text-sm opacity-80">Total Produk</p>
                <h2 class="text-2xl font-bold mt-2">
                    <?= $total_produk ?>
                </h2>
            </div>

            <div class="bg-pink-500 text-white rounded-2xl p-5 shadow">
                <p class="text-sm opacity-80">Total Karyawan</p>
                <h2 class="text-2xl font-bold mt-2">
                    <?= $total_karyawan ?>
                </h2>
            </div>

            <div class="bg-red-500 text-white rounded-2xl p-5 shadow">
                <p class="text-sm opacity-80">Stok Menipis</p>
                <h2 class="text-2xl font-bold mt-2">
                    <?= $stok_menipis ?>
                </h2>
            </div>

        </div>

<!-- QUICK MENU -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6">

    <a href="produk.php" class="bg-white rounded-2xl shadow p-5 text-center hover:shadow-lg transition">
        <div class="text-4xl">☕</div>
        <p class="font-semibold mt-2">Produk</p>
    </a>

    <a href="karyawan.php" class="bg-white rounded-2xl shadow p-5 text-center hover:shadow-lg transition">
        <div class="text-4xl">👨‍💼</div>
        <p class="font-semibold mt-2">Karyawan</p>
    </a>

    <a href="laporan.php" class="bg-white rounded-2xl shadow p-5 text-center hover:shadow-lg transition">
        <div class="text-4xl">📊</div>
        <p class="font-semibold mt-2">Laporan</p>
    </a>

</div>

        <!-- GRAFIK -->
        <div class="bg-white rounded-3xl shadow mt-6 p-6">

            <h2 class="font-bold text-xl mb-4">
                Grafik Penjualan Tahun Ini
            </h2>

            <div style="height:350px;">
                <canvas id="chartPenjualan"></canvas>
            </div>

        </div>

        <!-- PRODUK & STOK -->
        <div class="grid lg:grid-cols-2 gap-6 mt-6">

            <!-- PRODUK TERLARIS -->
            <div class="bg-white rounded-3xl shadow p-6">

                <h2 class="font-bold text-xl mb-5">🔥 Produk Terlaris</h2>

                <div class="space-y-3">

                <?php while($d=mysqli_fetch_assoc($produk_terlaris)): ?>
                    <div class="flex justify-between bg-orange-50 p-4 rounded-xl">
                        <div><?= htmlspecialchars($d['nama_produk']) ?></div>
                        <div class="font-bold text-orange-600">
                            <?= $d['total_terjual'] ?> Terjual
                        </div>
                    </div>
                <?php endwhile; ?>

                </div>

            </div>

            <!-- STOK MENIPIS -->
            <div class="bg-white rounded-3xl shadow p-6">

                <h2 class="font-bold text-xl mb-5">⚠️ Stok Menipis</h2>

                <div class="space-y-3">

                <?php while($s=mysqli_fetch_assoc($list_stok)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl">
                        <div class="flex justify-between">
                            <span><?= htmlspecialchars($s['nama_produk']) ?></span>
                            <span class="font-bold text-red-600">
                                <?= $s['stok'] ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>

                </div>

            </div>

        </div>

    </div>
</div>

<script>
new Chart(document.getElementById('chartPenjualan'),{
    type:'bar',
    data:{
        labels: <?= json_encode($bulan) ?>,
        datasets:[{
            label:'Penjualan',
            data: <?= json_encode($total) ?>
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false
    }
});
</script>

</body>
</html>