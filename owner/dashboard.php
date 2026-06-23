<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}
if ((int)$_SESSION['role_id'] !== 1) {
    header("Location: ../karyawan/dashboard.php");
    exit;
}

/* ========== STATISTIK ========== */
$q1 = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) total FROM transactions");
$total_penjualan = mysqli_fetch_assoc($q1)['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) total FROM transactions");
$total_transaksi = mysqli_fetch_assoc($q2)['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) total FROM products");
$total_produk = mysqli_fetch_assoc($q3)['total'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) total FROM users WHERE role_id = 2");
$total_karyawan = mysqli_fetch_assoc($q4)['total'];

$q5 = mysqli_query($conn, "SELECT COUNT(*) total FROM products WHERE stok <= stok_minimum");
$stok_menipis = mysqli_fetch_assoc($q5)['total'];

/* ========== PENJUALAN HARI INI ========== */
$qhari = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) total FROM transactions WHERE DATE(tanggal) = CURDATE()");
$penjualan_hari = mysqli_fetch_assoc($qhari)['total'];

/* ========== PRODUK TERLARIS ========== */
$produk_terlaris = mysqli_query($conn, "
    SELECT p.nama_produk, SUM(td.qty) total_terjual, SUM(td.subtotal) total_omzet
    FROM transaction_details td
    JOIN products p ON td.product_id = p.id
    GROUP BY td.product_id
    ORDER BY total_terjual DESC
    LIMIT 5
");

/* ========== STOK MENIPIS ========== */
$list_stok = mysqli_query($conn, "
    SELECT * FROM products
    WHERE stok <= stok_minimum
    ORDER BY stok ASC LIMIT 5
");

/* ========== TRANSAKSI TERBARU ========== */
$transaksi = mysqli_query($conn, "
    SELECT t.*, u.nama_lengkap
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC LIMIT 6
");

/* ========== GRAFIK PENJUALAN ========== */
$grafik = mysqli_query($conn, "
    SELECT MONTH(tanggal) bulan, SUM(total) total
    FROM transactions
    WHERE YEAR(tanggal) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal)
    ORDER BY bulan ASC
");

$nama_bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$chart_labels = [];
$chart_data   = [];
while ($g = mysqli_fetch_assoc($grafik)) {
    $chart_labels[] = $nama_bulan[(int)$g['bulan'] - 1];
    $chart_data[]   = (float)$g['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard – Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * { font-family: 'Poppins', sans-serif; }

  /* Stat card shimmer on hover */
  .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
  .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.08); }

  /* Quick action hover */
  .quick-card { transition: all .2s ease; }
  .quick-card:hover { transform: translateY(-2px); }

  /* Badge pulse */
  @keyframes badge-pulse { 0%,100% { opacity:1; } 50% { opacity:.6; } }
  .badge-pulse { animation: badge-pulse 2.2s infinite; }

  /* Scrollbar */
  ::-webkit-scrollbar { width: 4px; height: 4px; }
  ::-webkit-scrollbar-thumb { background: #fb923c; border-radius: 4px; }

  /* Progress bar animation */
  .progress-bar { transition: width 1s ease; }

  /* Avatar initial */
  .avatar { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; }
</style>
</head>

<body class="bg-slate-100 min-h-screen">

<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

  <!-- ===== HEADER ===== -->
  <div class="bg-gradient-to-r from-orange-500 via-orange-500 to-amber-500 px-7 pt-8 pb-20 text-white relative overflow-hidden">

    <!-- Dekoratif lingkaran -->
    <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full"></div>
    <div class="absolute top-8 right-24 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-8 left-40 w-32 h-32 bg-white/10 rounded-full"></div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <p class="text-orange-100 text-sm font-medium tracking-wider uppercase mb-1">Pojok Kafe ☕</p>
        <h1 class="text-3xl font-extrabold tracking-tight">Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Owner') ?>!</h1>
        <p class="text-orange-100 mt-1 text-sm">
          <?= date('l, d F Y') ?> &nbsp;·&nbsp;
          <?php if ($stok_menipis > 0): ?>
          <span class="bg-red-400/30 text-white px-2 py-0.5 rounded-full text-xs badge-pulse">
            ⚠️ <?= $stok_menipis ?> produk stok menipis
          </span>
          <?php else: ?>
          <span class="text-orange-200">Semua stok aman ✓</span>
          <?php endif; ?>
        </p>
      </div>
      <a href="laporan.php"
         class="flex items-center gap-2 bg-white/20 hover:bg-white/30 border border-white/30 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition self-start sm:self-auto backdrop-blur-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414A1 1 0 0 1 19 9.414V19a2 2 0 0 1-2 2z"/></svg>
        Lihat Laporan
      </a>
    </div>

  </div>

  <!-- ===== STAT CARDS (overlapping header) ===== -->
  <div class="px-6 -mt-12 relative z-20">
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

      <!-- Penjualan Hari Ini -->
      <div class="stat-card col-span-2 lg:col-span-2 bg-white rounded-2xl shadow-md p-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-green-50 flex items-center justify-center text-2xl flex-shrink-0">💰</div>
        <div class="min-w-0">
          <p class="text-gray-400 text-xs font-medium uppercase tracking-wider">Penjualan Hari Ini</p>
          <h2 class="text-xl font-extrabold text-gray-800 mt-0.5 truncate">
            Rp <?= number_format($penjualan_hari, 0, ',', '.') ?>
          </h2>
          <p class="text-gray-400 text-xs mt-0.5">Total: Rp <?= number_format($total_penjualan, 0, ',', '.') ?></p>
        </div>
      </div>

      <!-- Total Transaksi -->
      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl">🧾</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Transaksi</p>
          <h2 class="text-2xl font-extrabold text-gray-800"><?= number_format($total_transaksi) ?></h2>
        </div>
      </div>

      <!-- Total Produk -->
      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl">📦</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Produk</p>
          <h2 class="text-2xl font-extrabold text-gray-800"><?= $total_produk ?></h2>
        </div>
      </div>

      <!-- Total Karyawan -->
      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-pink-50 flex items-center justify-center text-xl">👨‍💼</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Karyawan</p>
          <h2 class="text-2xl font-extrabold text-gray-800"><?= $total_karyawan ?></h2>
        </div>
      </div>

    </div>
  </div>

  <!-- ===== MAIN CONTENT ===== -->
  <div class="px-6 py-6 pb-16 space-y-6">

    <!-- QUICK MENU -->
    <div class="grid grid-cols-3 gap-3">

      <a href="produk.php" class="quick-card bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center text-center gap-2 border border-gray-100 hover:border-orange-200 hover:shadow-md">
        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-2xl">☕</div>
        <span class="text-sm font-semibold text-gray-700">Produk</span>
      </a>

      <a href="karyawan.php" class="quick-card bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center text-center gap-2 border border-gray-100 hover:border-blue-200 hover:shadow-md">
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-2xl">👥</div>
        <span class="text-sm font-semibold text-gray-700">Karyawan</span>
      </a>

      <a href="laporan.php" class="quick-card bg-white rounded-2xl shadow-sm p-4 flex flex-col items-center text-center gap-2 border border-gray-100 hover:border-green-200 hover:shadow-md">
        <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-2xl">📊</div>
        <span class="text-sm font-semibold text-gray-700">Laporan</span>
      </a>

    </div>

    <!-- GRAFIK -->
    <div class="bg-white rounded-3xl shadow-sm p-6">

      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-lg font-bold text-gray-800">Grafik Penjualan</h2>
          <p class="text-gray-400 text-sm">Tahun <?= date('Y') ?></p>
        </div>
        <div class="bg-orange-50 text-orange-600 text-xs font-semibold px-3 py-1.5 rounded-xl">
          Rp <?= number_format($total_penjualan, 0, ',', '.') ?> total
        </div>
      </div>

      <div style="position:relative; height:260px;">
        <canvas id="chartPenjualan"
          role="img"
          aria-label="Grafik batang penjualan bulanan Pojok Kafe tahun <?= date('Y') ?>">
          Data penjualan bulanan tahun <?= date('Y') ?>.
        </canvas>
      </div>

    </div>

    <!-- PRODUK TERLARIS + STOK MENIPIS -->
    <div class="grid lg:grid-cols-2 gap-5">

      <!-- Produk Terlaris -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-bold text-gray-800">🔥 Produk Terlaris</h2>
          <a href="laporan.php" class="text-orange-500 text-xs font-semibold hover:underline">Lihat semua →</a>
        </div>

        <div class="space-y-3">
        <?php
          $rank = 1;
          while ($d = mysqli_fetch_assoc($produk_terlaris)):
            $rank_colors = ['bg-orange-500','bg-orange-400','bg-amber-400','bg-gray-300','bg-gray-200'];
            $rank_text   = ['text-white','text-white','text-white','text-gray-600','text-gray-500'];
            $ci = min($rank-1, 4);
        ?>
        <div class="flex items-center gap-3">
          <span class="w-7 h-7 rounded-full <?= $rank_colors[$ci] ?> <?= $rank_text[$ci] ?> flex items-center justify-center text-xs font-bold flex-shrink-0">
            <?= $rank ?>
          </span>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center mb-1">
              <span class="text-sm font-medium text-gray-700 truncate"><?= htmlspecialchars($d['nama_produk']) ?></span>
              <span class="text-sm font-bold text-orange-600 flex-shrink-0 ml-2"><?= number_format($d['total_terjual']) ?> terjual</span>
            </div>
            <?php
              // Progress bar: max = first item (rank 1)
              static $max_terjual = null;
              if ($max_terjual === null) $max_terjual = max((int)$d['total_terjual'], 1);
              $pct = min(100, round($d['total_terjual'] / $max_terjual * 100));
            ?>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full bg-orange-400 rounded-full progress-bar" style="width:<?= $pct ?>%"></div>
            </div>
          </div>
        </div>
        <?php $rank++; endwhile; ?>

        <?php if($rank === 1): ?>
        <div class="text-center py-8 text-gray-400 text-sm">Belum ada data penjualan.</div>
        <?php endif; ?>

        </div>
      </div>

      <!-- Stok Menipis -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-bold text-gray-800">⚠️ Stok Menipis</h2>
          <a href="produk.php" class="text-red-500 text-xs font-semibold hover:underline">Kelola stok →</a>
        </div>

        <?php
          $stok_rows = [];
          while ($s = mysqli_fetch_assoc($list_stok)) $stok_rows[] = $s;
        ?>

        <?php if(count($stok_rows) === 0): ?>
        <div class="flex flex-col items-center justify-center py-10 text-center">
          <div class="text-4xl mb-2">✅</div>
          <p class="text-gray-500 font-medium">Semua stok aman!</p>
          <p class="text-gray-400 text-xs mt-1">Tidak ada produk di bawah stok minimum.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
        <?php foreach($stok_rows as $s):
          $ratio = $s['stok_minimum'] > 0 ? ($s['stok'] / $s['stok_minimum']) : 1;
          $bar_pct = min(100, round($ratio * 100));
          $urgent = $s['stok'] == 0;
        ?>
        <div class="rounded-2xl border <?= $urgent ? 'border-red-200 bg-red-50' : 'border-orange-100 bg-orange-50' ?> p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700 truncate flex-1"><?= htmlspecialchars($s['nama_produk']) ?></span>
            <span class="text-xs font-bold <?= $urgent ? 'text-red-600 badge-pulse' : 'text-orange-600' ?> flex-shrink-0 ml-2">
              <?= $urgent ? 'HABIS' : $s['stok'].' unit' ?>
            </span>
          </div>
          <div class="h-1.5 bg-white/70 rounded-full overflow-hidden">
            <div class="h-full <?= $urgent ? 'bg-red-400' : 'bg-orange-400' ?> rounded-full" style="width:<?= $bar_pct ?>%"></div>
          </div>
          <p class="text-xs text-gray-400 mt-1.5">Min. stok: <?= $s['stok_minimum'] ?> unit</p>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </div>

    </div>

    <!-- TRANSAKSI TERBARU -->
    <div class="bg-white rounded-3xl shadow-sm p-6">

      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-800">🧾 Transaksi Terbaru</h2>
        <a href="laporan.php" class="text-orange-500 text-xs font-semibold hover:underline">Lihat semua →</a>
      </div>

      <div class="overflow-x-auto -mx-1">
        <table class="w-full min-w-[560px] text-sm">
          <thead>
            <tr class="text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
              <th class="pb-3 text-left font-semibold pl-1">Kode</th>
              <th class="pb-3 text-left font-semibold">Kasir</th>
              <th class="pb-3 text-right font-semibold">Total</th>
              <th class="pb-3 text-right font-semibold">Kembalian</th>
              <th class="pb-3 text-right font-semibold pr-1">Waktu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
          <?php
            $colors = ['bg-orange-100 text-orange-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700',
                       'bg-green-100 text-green-700','bg-pink-100 text-pink-700','bg-amber-100 text-amber-700'];
            $ci = 0;
            $rows_shown = 0;
            while ($t = mysqli_fetch_assoc($transaksi)):
              $rows_shown++;
              $initials = strtoupper(substr($t['nama_lengkap'], 0, 1)) . strtoupper(substr(strstr($t['nama_lengkap'],' ') ?: ' X', 1, 1));
              $color = $colors[$ci % count($colors)]; $ci++;
          ?>
          <tr class="hover:bg-slate-50 transition">
            <td class="py-3.5 pl-1">
              <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">
                <?= htmlspecialchars($t['kode_transaksi'] ?? '#'.$t['id']) ?>
              </span>
            </td>
            <td class="py-3.5">
              <div class="flex items-center gap-2">
                <div class="avatar <?= $color ?> text-xs"><?= $initials ?></div>
                <span class="text-gray-700 font-medium truncate max-w-[120px]"><?= htmlspecialchars($t['nama_lengkap']) ?></span>
              </div>
            </td>
            <td class="py-3.5 text-right font-bold text-green-600">
              Rp <?= number_format($t['total'], 0, ',', '.') ?>
            </td>
            <td class="py-3.5 text-right text-gray-500">
              Rp <?= number_format($t['kembalian'], 0, ',', '.') ?>
            </td>
            <td class="py-3.5 text-right text-gray-400 text-xs pr-1">
              <?= date('d/m H:i', strtotime($t['tanggal'])) ?>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if($rows_shown === 0): ?>
          <tr><td colspan="5" class="py-10 text-center text-gray-400">Belum ada transaksi.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>

  </div><!-- /px-6 py-6 -->
</div><!-- /lg:ml-64 -->

<script>
const labels = <?= json_encode($chart_labels) ?>;
const data   = <?= json_encode($chart_data) ?>;

const ctx = document.getElementById('chartPenjualan');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Penjualan (Rp)',
      data: data,
      backgroundColor: labels.map((_, i) => i === data.indexOf(Math.max(...data)) ? '#f97316' : '#fed7aa'),
      borderRadius: 8,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
        },
        backgroundColor: '#fff',
        titleColor: '#374151',
        bodyColor: '#f97316',
        borderColor: '#e5e7eb',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: {
          font: { family: 'Poppins', size: 12 },
          color: '#9ca3af'
        },
        border: { display: false }
      },
      y: {
        grid: {
          color: '#f3f4f6',
          drawBorder: false
        },
        ticks: {
          font: { family: 'Poppins', size: 11 },
          color: '#9ca3af',
          callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v/1000).toFixed(0)+'rb')
        },
        border: { display: false }
      }
    }
  }
});
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>