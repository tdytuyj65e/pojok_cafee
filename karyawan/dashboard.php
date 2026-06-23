<?php
session_start();
include "../koneksi.php";

/* ===== CEK LOGIN ===== */
$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

/* ===== DATA USER (prepared statement) ===== */
$stmtU = mysqli_prepare($conn, "SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?");
mysqli_stmt_bind_param($stmtU, "i", $user_id);
mysqli_stmt_execute($stmtU);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtU));

$fotoUser = (!empty($user['foto']) && file_exists("../uploads/" . $user['foto']))
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']) . "&background=8e4a0e&color=fff&size=80";

/* ===== STATISTIK HARI INI ===== */
$today = date('Y-m-d');

// Total penjualan hari ini
$q1 = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) AS total FROM transactions WHERE DATE(tanggal) = '$today'");
$penjualanHariIni = (int) mysqli_fetch_assoc($q1)['total'];

// Total penjualan keseluruhan
$q1b = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) AS total FROM transactions");
$penjualanTotal = (int) mysqli_fetch_assoc($q1b)['total'];

// Jumlah transaksi hari ini
$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE DATE(tanggal) = '$today'");
$transaksiHariIni = (int) mysqli_fetch_assoc($q2)['total'];

// Jumlah transaksi keseluruhan
$q2b = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions");
$transaksiTotal = (int) mysqli_fetch_assoc($q2b)['total'];

// Produk dengan stok rendah (stok <= stok_minimum)
$q3 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stok <= stok_minimum AND stok > 0");
$stokRendah = (int) mysqli_fetch_assoc($q3)['total'];

// Produk habis
$q4 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stok = 0");
$stokHabis = (int) mysqli_fetch_assoc($q4)['total'];

// Produk terbaru untuk carousel
$qProduk = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 8");
$jumlahProduk = mysqli_num_rows($qProduk);

// 5 Transaksi terakhir
$qTrx = mysqli_query($conn, "
    SELECT t.*, u.nama_lengkap
    FROM transactions t
    JOIN users u ON u.id = t.user_id
    ORDER BY t.tanggal DESC
    LIMIT 5
");

/* ===== SAPAAN ===== */
$jam = (int) date('H');
$sapaan = match(true) {
    $jam < 11 => "Selamat pagi",
    $jam < 15 => "Selamat siang",
    $jam < 19 => "Selamat sore",
    default   => "Selamat malam",
};

$namaTampil = $user['nama_lengkap'] ?? 'Kasir';

/* ===== JUMLAH ITEM KERANJANG ===== */
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $c) $cartCount += $c['qty'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title>Dashboard | Pojok Kafe</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,500,1,0" rel="stylesheet"/>
<style>
* { font-family: 'Plus Jakarta Sans', sans-serif; }
.material-symbols-rounded { font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24; user-select:none; }
body { background:#fff8f5; min-height:100dvh; }

/* Header gradient */
.header-bg {
    background: linear-gradient(135deg, #7c3a08 0%, #8e4a0e 40%, #a9632c 100%);
    position: relative; overflow: hidden;
}
.header-bg::before {
    content:''; position:absolute; width:220px; height:220px;
    background:rgba(255,255,255,0.06); border-radius:50%;
    top:-60px; right:-50px;
}
.header-bg::after {
    content:''; position:absolute; width:140px; height:140px;
    background:rgba(255,255,255,0.04); border-radius:50%;
    bottom:-40px; left:-30px;
}

/* Cards */
.stat-card { transition: transform .15s ease, box-shadow .15s ease; }
.stat-card:active { transform: scale(0.97); }

.product-card { transition: transform .15s ease; }
.product-card:active { transform: scale(0.96); }

/* Scrollbar hide */
.hide-sb::-webkit-scrollbar { display:none; }
.hide-sb { -ms-overflow-style:none; scrollbar-width:none; }

/* Fade edge */
.fade-r { background:linear-gradient(to left,#fff8f5,transparent); }

/* Badge pulse */
@keyframes pulse-badge { 0%,100%{opacity:1} 50%{opacity:.6} }
.badge-pulse { animation: pulse-badge 2s ease-in-out infinite; }
</style>
</head>
<body class="pb-28">

<!-- ====== HEADER ====== -->
<header class="header-bg text-white">
  <div class="relative z-10 max-w-md mx-auto px-4 pt-10 pb-6">

    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-3">
        <img src="<?= htmlspecialchars($fotoUser) ?>"
             class="w-10 h-10 rounded-full object-cover border-2 border-white/40"
             onerror="this.src='https://ui-avatars.com/api/?name=U&background=8e4a0e&color=fff'">
        <div>
          <p class="text-orange-200 text-xs font-medium"><?= htmlspecialchars($user['role_name'] ?? 'Karyawan') ?></p>
          <p class="font-bold text-sm leading-tight"><?= htmlspecialchars($namaTampil) ?></p>
        </div>
      </div>

      <!-- Keranjang shortcut -->
      <a href="transaksi.php" class="relative w-10 h-10 flex items-center justify-center rounded-full bg-white/15 hover:bg-white/25 transition" aria-label="Keranjang">
        <span class="material-symbols-rounded text-xl">shopping_cart</span>
        <?php if ($cartCount > 0): ?>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
    </div>

    <p class="text-orange-100 text-sm"><?= $sapaan ?> 👋</p>
    <h1 class="text-2xl font-extrabold leading-tight mt-0.5">Dashboard Pojok Kafe</h1>
    <p class="text-orange-200 text-xs mt-1"><?= date('l, d F Y') ?></p>

  </div>
</header>

<!-- ====== MAIN ====== -->
<main class="max-w-md mx-auto px-4 space-y-5 -mt-3 relative z-10">

  <!-- QUICK ACTION -->
  <a href="produk.php"
     class="flex items-center justify-center gap-2 bg-[#8e4a0e] hover:bg-[#7a3e0b] active:scale-[.98] text-white rounded-2xl py-4 font-bold text-sm shadow-lg shadow-orange-900/25 transition-all">
    <span class="material-symbols-rounded text-xl">point_of_sale</span>
    Mulai Transaksi Baru
  </a>

  <!-- STATS HARI INI -->
  <div>
    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Hari Ini · <?= date('d M Y') ?></p>
    <div class="grid grid-cols-2 gap-3">

      <!-- Penjualan hari ini -->
      <div class="stat-card bg-[#8e4a0e] text-white rounded-2xl p-4 shadow-md">
        <div class="flex items-center gap-1.5 mb-3">
          <span class="material-symbols-rounded text-orange-200 text-lg">trending_up</span>
          <span class="text-xs font-medium text-orange-200">Penjualan</span>
        </div>
        <p class="text-[11px] text-orange-300 mb-0.5">Total hari ini</p>
        <p class="text-lg font-extrabold leading-tight">Rp <?= number_format($penjualanHariIni, 0, ',', '.') ?></p>
        <p class="text-[10px] text-orange-300 mt-1">Semua: Rp <?= number_format($penjualanTotal, 0, ',', '.') ?></p>
      </div>

      <!-- Transaksi hari ini -->
      <div class="stat-card bg-white rounded-2xl p-4 shadow-md border border-orange-100">
        <div class="flex items-center gap-1.5 mb-3">
          <span class="material-symbols-rounded text-[#8e4a0e] text-lg">receipt_long</span>
          <span class="text-xs font-medium text-slate-500">Transaksi</span>
        </div>
        <p class="text-[11px] text-slate-400 mb-0.5">Total hari ini</p>
        <p class="text-lg font-extrabold text-slate-800 leading-tight"><?= $transaksiHariIni ?> <span class="text-sm font-medium text-slate-500">order</span></p>
        <p class="text-[10px] text-slate-400 mt-1">Semua: <?= $transaksiTotal ?> transaksi</p>
      </div>

    </div>
  </div>

  <!-- ALERT STOK -->
  <?php if ($stokHabis > 0 || $stokRendah > 0): ?>
  <div class="rounded-2xl overflow-hidden border border-amber-200">
    <?php if ($stokHabis > 0): ?>
    <div class="bg-red-50 px-4 py-3 flex items-center gap-2.5">
      <span class="material-symbols-rounded text-red-500 badge-pulse">error</span>
      <div>
        <p class="text-sm font-semibold text-red-700"><?= $stokHabis ?> produk stok habis</p>
        <p class="text-xs text-red-500">Segera isi ulang stok produk tersebut.</p>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($stokRendah > 0): ?>
    <div class="bg-amber-50 px-4 py-3 flex items-center gap-2.5 border-t border-amber-100">
      <span class="material-symbols-rounded text-amber-500">warning</span>
      <div>
        <p class="text-sm font-semibold text-amber-700"><?= $stokRendah ?> produk stok hampir habis</p>
        <p class="text-xs text-amber-500">Stok di bawah batas minimum.</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- PRODUK TERBARU -->
  <div>
    <div class="flex items-center justify-between mb-2.5">
      <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Produk <span class="text-slate-300">(<?= $jumlahProduk ?>)</span></p>
      <a href="produk.php" class="text-xs font-semibold text-[#8e4a0e] flex items-center gap-0.5">
        Lihat semua <span class="material-symbols-rounded text-sm">chevron_right</span>
      </a>
    </div>

    <?php if ($jumlahProduk === 0): ?>
    <div class="bg-white border border-dashed border-orange-200 rounded-2xl py-10 text-center">
      <span class="material-symbols-rounded text-4xl text-orange-200">inventory_2</span>
      <p class="text-sm font-semibold text-slate-600 mt-2">Belum ada produk</p>
      <a href="produk.php" class="inline-block mt-3 px-4 py-2 bg-[#8e4a0e] text-white text-xs font-semibold rounded-xl">+ Tambah Produk</a>
    </div>

    <?php else: ?>
    <div class="relative">
      <div class="flex gap-3 overflow-x-auto hide-sb pb-2">
        <?php while ($p = mysqli_fetch_assoc($qProduk)):
          $img = !empty($p['foto']) ? "../uploads/" . htmlspecialchars($p['foto']) : "https://via.placeholder.com/200";
          $stok = (int) $p['stok'];
          $stokColor = $stok === 0 ? 'bg-red-500' : ($stok <= 5 ? 'bg-amber-500' : 'bg-green-500');
        ?>
        <a href="produk.php"
           class="product-card flex-shrink-0 bg-white rounded-2xl overflow-hidden shadow border border-orange-50 w-36">
          <div class="relative">
            <img src="<?= $img ?>" class="w-full h-28 object-cover bg-orange-50"
                 onerror="this.src='https://via.placeholder.com/200'">
            <span class="absolute top-2 right-2 <?= $stokColor ?> text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
              <?= $stok === 0 ? 'Habis' : "Stok $stok" ?>
            </span>
          </div>
          <div class="p-2.5">
            <p class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($p['nama_produk']) ?></p>
            <p class="text-xs font-bold text-[#8e4a0e] mt-0.5">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
          </div>
        </a>
        <?php endwhile; ?>
      </div>
      <div class="fade-r absolute right-0 top-0 bottom-2 w-10 pointer-events-none"></div>
    </div>
    <?php endif; ?>
  </div>

  <!-- TRANSAKSI TERAKHIR -->
  <div class="pb-2">
    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Transaksi Terakhir</p>

    <?php $anyTrx = false; while ($trx = mysqli_fetch_assoc($qTrx)): $anyTrx = true; ?>
    <div class="bg-white rounded-2xl px-4 py-3 mb-2 shadow border border-orange-50 flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-slate-700"><?= htmlspecialchars($trx['nama_lengkap']) ?></p>
        <p class="text-[10px] text-slate-400 mt-0.5"><?= date('d M Y, H:i', strtotime($trx['tanggal'])) ?></p>
      </div>
      <p class="text-sm font-bold text-[#8e4a0e]">Rp <?= number_format($trx['total'], 0, ',', '.') ?></p>
    </div>
    <?php endwhile; ?>

    <?php if (!$anyTrx): ?>
    <div class="bg-white rounded-2xl py-8 text-center border border-dashed border-orange-100">
      <span class="material-symbols-rounded text-3xl text-orange-200">receipt_long</span>
      <p class="text-xs text-slate-400 mt-2">Belum ada transaksi hari ini</p>
    </div>
    <?php endif; ?>
  </div>

</main>

<?php include "navbar_karyawan.php"; ?>
</body>
</html>