<?php
session_start();

$trx = $_SESSION['last_trx'] ?? null;

if (!$trx) {
    header("Location: transaksi.php");
    exit;
}

$items = $trx['items'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

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

.receipt-bottom {
  background: radial-gradient(circle at 10px 20px, transparent 10px, white 10px);
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
                    <b><?= $trx['kode'] ?? '-' ?></b>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Kasir</span>
                    <span><?= $trx['kasir'] ?? 'Kasir' ?></span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-500">Waktu</span>
                    <span><?= date('d M Y H:i') ?></span>
                </div>

            </div>

            <!-- PRODUK LIST -->
<!-- PRODUK LIST -->
<div class="space-y-2 border-b pb-3">

    <?php if (!empty($items)): ?>
        <?php foreach ($items as $item): ?>
        <div class="flex justify-between text-sm">
            <div>
                <p class="font-semibold">
                    <?= $item['nama'] ?? '-' ?>
                </p>
                <p class="text-gray-500 text-xs">
                    <?= $item['qty'] ?? 0 ?> x <?= number_format($item['harga'] ?? 0,0,',','.') ?>
                </p>
            </div>

            <p class="font-bold">
                Rp <?= number_format($item['subtotal'] ?? 0,0,',','.') ?>
            </p>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-400 text-sm">Tidak ada produk</p>
    <?php endif; ?>

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

</body>
</html>