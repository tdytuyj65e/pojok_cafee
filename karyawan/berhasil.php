<?php
session_start();
include "../koneksi.php";

$trx = $_SESSION['last_trx'] ?? null;

if (!$trx) {
    header("Location: transaksi.php");
    exit;
}

$kode = $trx['kode'] ?? null;
$kasir = $trx['kasir'] ?? 'Kasir';

/* AMBIL TRANSAKSI */
$qtrx = mysqli_query($conn, "
    SELECT * FROM transactions
    WHERE kode_transaksi = '$kode'
");

$trans = mysqli_fetch_assoc($qtrx);

if (!$trans) {
    echo "Transaksi tidak ditemukan";
    exit;
}

/* AMBIL DETAIL + PRODUK */
$qitem = mysqli_query($conn, "
    SELECT td.*, p.nama_produk
    FROM transaction_details td
    JOIN products p ON p.id = td.product_id
    WHERE td.transaction_id = '{$trans['id']}'
");

$items = [];
while ($row = mysqli_fetch_assoc($qitem)) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">
<script src="https://cdn.tailwindcss.com"></script>

<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: "#8e4a0e",
        background: "#fff8f5",
      }
    }
  }
}
</script>

<style>
body { background:#fff8f5; }

.receipt-top {
  background: radial-gradient(circle at 10px 0px, transparent 10px, white 10px);
  background-size: 20px 20px;
  background-repeat: repeat-x;
}
</style>

</head>

<body class="min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md">

    <!-- SUCCESS -->
    <div class="text-center mb-4">
        <div class="w-16 h-16 mx-auto bg-green-600 rounded-full flex items-center justify-center">
            <span class="text-white text-3xl">✓</span>
        </div>
        <h1 class="text-xl font-bold mt-2">Pembayaran Berhasil</h1>
        <p class="text-gray-500 text-sm">Transaksi sudah tersimpan</p>
    </div>

    <!-- RECEIPT -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden">

        <div class="receipt-top h-4"></div>

        <div class="p-5 space-y-4">

            <!-- HEADER -->
            <div class="text-center border-b pb-3">
                <h2 class="text-primary font-extrabold text-lg">POJOK KAFE</h2>
                <p class="text-xs text-gray-500">Struk Transaksi</p>
            </div>

            <!-- INFO -->
            <div class="text-sm space-y-2 border-b pb-3">

                <div class="flex justify-between">
                    <span class="text-gray-500">Kode</span>
                    <b><?= $trans['kode_transaksi'] ?></b>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Kasir</span>
                    <span><?= $kasir ?></span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Waktu</span>
                    <span><?= date('d M Y H:i', strtotime($trans['tanggal'])) ?></span>
                </div>

            </div>

            <!-- PRODUK LIST -->
            <div class="space-y-2 border-b pb-3">

                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                    <div class="flex justify-between text-sm">

                        <div>
                            <p class="font-semibold">
                                <?= $item['nama_produk'] ?? '-' ?>
                            </p>
                            <p class="text-gray-500 text-xs">
                                <?= $item['qty'] ?> x <?= number_format($item['harga_satuan'],0,',','.') ?>
                            </p>
                        </div>

                        <p class="font-bold">
                            Rp <?= number_format($item['subtotal'],0,',','.') ?>
                        </p>

                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-400 text-sm">Tidak ada produk</p>
                <?php endif; ?>

            </div>

            <!-- PEMBAYARAN -->
            <div class="text-sm space-y-2 border-b pb-3">

                <div class="flex justify-between">
                    <span>Total</span>
                    <b>Rp <?= number_format($trans['total'],0,',','.') ?></b>
                </div>

                <div class="flex justify-between">
                    <span>Uang Diterima</span>
                    <b>Rp <?= number_format($trans['uang_diterima'],0,',','.') ?></b>
                </div>


            </div>

            <!-- BUTTON -->
            <div class="mt-5 space-y-2">

                <a href="transaksi.php"
                   class="block w-full bg-primary text-white text-center py-3 rounded-xl font-semibold">
                    TRANSAKSI BARU
                </a>

                <a href="dashboard.php"
                   class="block w-full border border-primary text-primary text-center py-3 rounded-xl font-semibold">
                    KEMBALI KE DASHBOARD
                </a>

            </div>

        </div>
    </div>

</div>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>