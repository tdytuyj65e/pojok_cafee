<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id'])) { header("Location: ../auth/login.php"); exit; }
if ((int)$_SESSION['role_id'] !== 1) { header("Location: ../karyawan/dashboard.php"); exit; }

/* =========================================================
   FILTER TANGGAL
   ========================================================= */
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

if (!strtotime($dari))   $dari   = date('Y-m-01');
if (!strtotime($sampai)) $sampai = date('Y-m-d');
if (strtotime($dari) > strtotime($sampai)) { [$dari, $sampai] = [$sampai, $dari]; }

$dari_safe   = mysqli_real_escape_string($conn, $dari);
$sampai_safe = mysqli_real_escape_string($conn, $sampai);

/* ===== TRANSAKSI ===== */
$query = mysqli_query($conn, "
    SELECT t.*, u.nama_lengkap
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE DATE(t.tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    ORDER BY t.id DESC
");
$rows = []; $total_penjualan = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
    $total_penjualan += $row['total'];
}
$total_transaksi = count($rows);
$rata_rata = $total_transaksi > 0 ? $total_penjualan / $total_transaksi : 0;

/* ===== GRAFIK HARIAN ===== */
$grafik_q = mysqli_query($conn, "
    SELECT DATE(tanggal) tgl, SUM(total) total, COUNT(*) jml
    FROM transactions
    WHERE DATE(tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    GROUP BY DATE(tanggal) ORDER BY tgl ASC
");
$chart_labels = []; $chart_data = []; $chart_jml = [];
while ($g = mysqli_fetch_assoc($grafik_q)) {
    $chart_labels[] = date('d/m', strtotime($g['tgl']));
    $chart_data[]   = (float)$g['total'];
    $chart_jml[]    = (int)$g['jml'];
}

/* ===== TOP KASIR ===== */
$kasir_q = mysqli_query($conn, "
    SELECT u.nama_lengkap, COUNT(t.id) jml, SUM(t.total) total
    FROM transactions t JOIN users u ON t.user_id = u.id
    WHERE DATE(t.tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    GROUP BY t.user_id ORDER BY total DESC LIMIT 5
");
$top_kasir = [];
while ($k = mysqli_fetch_assoc($kasir_q)) $top_kasir[] = $k;

/* ===== TOP PRODUK ===== */
$produk_q = mysqli_query($conn, "
    SELECT p.nama_produk, SUM(td.qty) qty, SUM(td.subtotal) subtotal
    FROM transaction_details td
    JOIN products p ON td.product_id = p.id
    JOIN transactions t ON td.transaction_id = t.id
    WHERE DATE(t.tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    GROUP BY td.product_id ORDER BY qty DESC LIMIT 5
");
$top_produk = [];
while ($p = mysqli_fetch_assoc($produk_q)) $top_produk[] = $p;

/* ===== SELISIH HARI & GROWTH ===== */
$selisih_hari = (strtotime($sampai) - strtotime($dari)) / 86400 + 1;
$dari_prev    = date('Y-m-d', strtotime($dari) - $selisih_hari * 86400);
$sampai_prev  = date('Y-m-d', strtotime($dari) - 86400);
$prev_q       = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) total FROM transactions WHERE DATE(tanggal) BETWEEN '$dari_prev' AND '$sampai_prev'");
$prev_total   = (float)mysqli_fetch_assoc($prev_q)['total'];
$growth       = $prev_total > 0 ? (($total_penjualan - $prev_total) / $prev_total * 100) : null;

/* ===== HUTANG — semua aktif (belum_lunas) ===== */
$hutang_q = mysqli_query($conn, "
    SELECT
        d.id            AS debt_id,
        c.nama          AS nama_pelanggan,
        c.no_hp,
        t.kode_transaksi,
        t.tanggal       AS tgl_transaksi,
        d.total_hutang,
        d.sisa_hutang,
        d.status,
        d.created_at    AS tgl_hutang,
        COALESCE((
            SELECT SUM(dp.jumlah_bayar)
            FROM debt_payments dp WHERE dp.debt_id = d.id
        ), 0)           AS total_dibayar
    FROM debts d
    JOIN customers c ON c.id = d.customer_id
    JOIN transactions t ON t.id = d.transaction_id
    WHERE d.status = 'belum_lunas'
    ORDER BY d.created_at DESC
");
$hutang_rows      = [];
$total_sisa_hutang = 0;
$total_hutang_awal = 0;
while ($h = mysqli_fetch_assoc($hutang_q)) {
    $hutang_rows[]     = $h;
    $total_sisa_hutang += $h['sisa_hutang'];
    $total_hutang_awal += $h['total_hutang'];
}
$jml_hutang = count($hutang_rows);

/* ===== DROPDOWN TAHUN ===== */
$tahun_sekarang = (int)date('Y');
$daftar_tahun   = range($tahun_sekarang, $tahun_sekarang - 4);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Penjualan – Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#16a34a">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * { font-family: 'Poppins', sans-serif; }
  .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
  .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.08); }
  .tr-row:hover { background: #fff7ed; }
  .tr-row { transition: background .15s; }
  .debt-row:hover { background: #fef2f2; }
  .debt-row { transition: background .15s; }
  .seg-btn { transition: all .15s ease; }
  .scrollbar-hide::-webkit-scrollbar { display: none; }
  .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
  @media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .lg\:ml-64 { margin-left: 0 !important; }
  }
</style>
</head>

<body class="bg-slate-100 min-h-screen">
<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

  <!-- HEADER -->
  <div class="bg-gradient-to-r from-orange-500 via-orange-500 to-amber-500 px-8 pt-8 pb-20 text-white relative overflow-hidden">
    <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full"></div>
    <div class="absolute top-8 right-24 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-orange-100 text-xs font-medium uppercase tracking-widest mb-1">Pojok Kafe</p>
        <h1 class="text-3xl font-extrabold tracking-tight">Laporan Penjualan 📊</h1>
        <p class="text-orange-100 mt-1 text-sm">
          <?= date('d M Y', strtotime($dari)) ?> – <?= date('d M Y', strtotime($sampai)) ?>
          &nbsp;·&nbsp; <?= $selisih_hari ?> hari
        </p>
      </div>
      <div class="flex gap-2 flex-wrap no-print">
        <a id="btnExcelTransaksi"
           href="export_excel.php?dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>"
           class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
          Excel Transaksi
        </a>
        <a id="btnExcelProduk"
           href="export_excel_produk.php?dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>"
           class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
          Excel Produk
        </a>
        <button onclick="window.print()"
           class="flex items-center gap-2 bg-white/20 hover:bg-white/30 border border-white/30 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Print
        </button>
      </div>
    </div>
  </div>

  <div class="px-6 pb-16">

    <!-- STAT CARDS -->
    <div id="sec-ringkasan"></div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 -mt-12 relative z-20 mb-6">

      <div class="stat-card col-span-2 bg-white rounded-2xl shadow-md p-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-green-50 flex items-center justify-center text-2xl flex-shrink-0">💰</div>
        <div class="min-w-0">
          <p class="text-gray-400 text-xs uppercase tracking-wider font-medium">Total Penjualan</p>
          <h2 class="text-2xl font-extrabold text-gray-800 truncate">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></h2>
          <?php if($growth !== null): ?>
          <p class="text-xs mt-0.5 font-semibold <?= $growth >= 0 ? 'text-green-500' : 'text-red-400' ?>">
            <?= $growth >= 0 ? '▲' : '▼' ?> <?= number_format(abs($growth), 1) ?>% vs periode sebelumnya
          </p>
          <?php endif; ?>
        </div>
      </div>

      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl">🧾</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Transaksi</p>
          <h2 class="text-2xl font-extrabold text-gray-800"><?= number_format($total_transaksi) ?></h2>
        </div>
      </div>

      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl">📈</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Rata-rata / Transaksi</p>
          <h2 class="text-xl font-extrabold text-gray-800">Rp <?= number_format($rata_rata, 0, ',', '.') ?></h2>
        </div>
      </div>

    </div>

    <!-- FILTER PANEL -->
    <!-- NAV SECTION STICKY -->
    <div id="sectionNav" class="sticky top-0 z-30 no-print -mx-6 px-6 pt-3 pb-2 bg-slate-100/95 backdrop-blur-sm shadow-sm mb-5">
      <div class="flex gap-1.5 overflow-x-auto scrollbar-hide">
        <button onclick="scrollToSection('sec-ringkasan')"
          class="nav-tab flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition bg-orange-500 text-white shadow-sm" data-section="sec-ringkasan">
          💰 Ringkasan
        </button>
        <button onclick="scrollToSection('sec-filter')"
          class="nav-tab flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition bg-white text-gray-500 shadow-sm hover:bg-orange-50 hover:text-orange-600" data-section="sec-filter">
          🔍 Filter
        </button>
        <button onclick="scrollToSection('sec-grafik')"
          class="nav-tab flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition bg-white text-gray-500 shadow-sm hover:bg-orange-50 hover:text-orange-600" data-section="sec-grafik">
          📈 Grafik & Produk
        </button>
        <button onclick="scrollToSection('sec-kasir')"
          class="nav-tab flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition bg-white text-gray-500 shadow-sm hover:bg-orange-50 hover:text-orange-600" data-section="sec-kasir">
          👨‍💼 Kasir
        </button>
        <button onclick="scrollToSection('sec-transaksi')"
          class="nav-tab flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition bg-white text-gray-500 shadow-sm hover:bg-orange-50 hover:text-orange-600" data-section="sec-transaksi">
          🧾 Transaksi
        </button>
        <button onclick="scrollToSection('sec-hutang')"
          class="nav-tab flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition bg-white text-gray-500 shadow-sm hover:bg-orange-50 hover:text-orange-600" data-section="sec-hutang">
          💳 Hutang
          <?php if($jml_hutang > 0): ?>
          <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none ml-0.5"><?= $jml_hutang ?></span>
          <?php endif; ?>
        </button>
      </div>
    </div>

    <div id="sec-filter" class="bg-white rounded-3xl shadow-sm p-5 mb-6 no-print">
      <form method="GET" id="filterForm" class="flex flex-col gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Periode Cepat</label>
          <div class="flex flex-wrap gap-2" id="periodeSegment">
            <?php
              $periodes = ['hari'=>'Hari Ini','minggu'=>'Minggu Ini','bulan'=>'Bulan Ini','lalu'=>'Bulan Lalu','tahun'=>'Tahun Ini'];
              $cek = [
                'hari'   => [date('Y-m-d'), date('Y-m-d')],
                'minggu' => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
                'bulan'  => [date('Y-m-01'), date('Y-m-d')],
                'lalu'   => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
                'tahun'  => [date('Y-01-01'), date('Y-m-d')],
              ];
              foreach($periodes as $key => $label):
                $active = ($dari === $cek[$key][0] && $sampai === $cek[$key][1]);
            ?>
            <button type="button" data-periode="<?= $key ?>"
              class="seg-btn px-4 py-2 rounded-xl text-xs font-semibold <?= $active ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600' ?>">
              <?= $label ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
            <input type="date" name="dari" id="inputDari" value="<?= htmlspecialchars($dari) ?>"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition">
          </div>
          <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
            <input type="date" name="sampai" id="inputSampai" value="<?= htmlspecialchars($sampai) ?>"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition">
          </div>
          <div class="min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Pilih Tahun</label>
            <select id="selectTahun" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition">
              <option value="">— Tahun —</option>
              <?php foreach($daftar_tahun as $th): ?>
              <option value="<?= $th ?>" <?= ($dari === "$th-01-01" && $sampai === "$th-12-31") ? 'selected' : '' ?>><?= $th ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition flex-shrink-0">
            Terapkan Filter
          </button>
        </div>
        <p class="text-xs text-gray-400">
          Tombol <b>Excel Transaksi</b> &amp; <b>Excel Produk</b> di atas otomatis mengikuti rentang tanggal yang sedang diterapkan di halaman ini.
        </p>
      </form>
    </div>

    <!-- GRAFIK + TOP PRODUK -->
    <div id="sec-grafik" class="grid lg:grid-cols-3 gap-5 mb-6">
      <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-base font-bold text-gray-800">Tren Penjualan Harian</h2>
            <p class="text-gray-400 text-xs mt-0.5"><?= count($chart_labels) ?> hari dengan data</p>
          </div>
        </div>
        <?php if(count($chart_labels) > 0): ?>
        <div style="position:relative; height:220px;">
          <canvas id="chartHarian"></canvas>
        </div>
        <?php else: ?>
        <div class="h-52 flex flex-col items-center justify-center text-gray-300">
          <div class="text-5xl mb-2">📉</div>
          <p class="text-sm">Tidak ada data untuk rentang ini</p>
        </div>
        <?php endif; ?>
      </div>

      <div class="bg-white rounded-3xl shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-4">🔥 Produk Terlaris</h2>
        <?php if(count($top_produk) > 0):
          $max_qty = max(array_column($top_produk, 'qty'));
        ?>
        <div class="space-y-3">
        <?php foreach($top_produk as $i => $tp):
          $pct    = $max_qty > 0 ? round($tp['qty'] / $max_qty * 100) : 0;
          $colors = ['bg-orange-500','bg-orange-400','bg-amber-400','bg-gray-300','bg-gray-200'];
          $tc     = ['text-white','text-white','text-white','text-gray-600','text-gray-500'];
        ?>
        <div class="flex items-center gap-3">
          <span class="w-6 h-6 rounded-full <?= $colors[$i] ?> <?= $tc[$i] ?> flex items-center justify-center text-xs font-bold flex-shrink-0"><?= $i+1 ?></span>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center text-xs mb-1">
              <span class="font-medium text-gray-700 truncate"><?= htmlspecialchars($tp['nama_produk']) ?></span>
              <span class="text-orange-600 font-bold ml-2 flex-shrink-0"><?= number_format($tp['qty']) ?>x</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full <?= $colors[$i] ?> rounded-full" style="width:<?= $pct ?>%"></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-gray-300 text-sm">Belum ada data produk.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- TOP KASIR -->
    <?php if(count($top_kasir) > 0): ?>
    <div id="sec-kasir" class="bg-white rounded-3xl shadow-sm p-6 mb-6">
      <h2 class="text-base font-bold text-gray-800 mb-4">👨‍💼 Performa Kasir</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
      <?php
        $kasir_colors = [
          ['bg-orange-50','text-orange-600','bg-orange-500'],
          ['bg-blue-50','text-blue-600','bg-blue-500'],
          ['bg-purple-50','text-purple-600','bg-purple-500'],
          ['bg-green-50','text-green-600','bg-green-500'],
          ['bg-pink-50','text-pink-600','bg-pink-500'],
        ];
        $max_kas = count($top_kasir) > 0 ? max(array_column($top_kasir, 'total')) : 1;
        foreach($top_kasir as $i => $k):
          $clr = $kasir_colors[$i % count($kasir_colors)];
          $pct = $max_kas > 0 ? round($k['total'] / $max_kas * 100) : 0;
          $initials = strtoupper(substr($k['nama_lengkap'],0,1)) . strtoupper(substr(strstr($k['nama_lengkap'],' ') ?: ' X',1,1));
      ?>
      <div class="<?= $clr[0] ?> rounded-2xl p-4 flex flex-col gap-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl <?= $clr[2] ?> text-white flex items-center justify-center text-sm font-bold flex-shrink-0"><?= $initials ?></div>
          <div class="min-w-0">
            <p class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($k['nama_lengkap']) ?></p>
            <p class="text-xs text-gray-500"><?= $k['jml'] ?> transaksi</p>
          </div>
        </div>
        <p class="font-bold text-sm <?= $clr[1] ?>">Rp <?= number_format($k['total'],0,',','.') ?></p>
        <div class="h-1.5 bg-white/60 rounded-full overflow-hidden">
          <div class="h-full <?= $clr[2] ?> rounded-full" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- TABEL TRANSAKSI -->
    <div id="sec-transaksi" class="bg-white rounded-3xl shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-gray-800">Detail Transaksi</h2>
          <p class="text-gray-400 text-xs mt-0.5"><?= $total_transaksi ?> transaksi ditemukan</p>
        </div>
        <?php if($total_transaksi > 0): ?>
        <span class="bg-orange-50 text-orange-600 text-xs font-bold px-3 py-1.5 rounded-xl">
          Rp <?= number_format($total_penjualan, 0, ',', '.') ?> total
        </span>
        <?php endif; ?>
      </div>

      <?php if($total_transaksi > 0): ?>
      <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
              <th class="px-6 py-3 text-left font-semibold">No</th>
              <th class="px-6 py-3 text-left font-semibold">Kode Transaksi</th>
              <th class="px-6 py-3 text-left font-semibold">Kasir</th>
              <th class="px-6 py-3 text-left font-semibold">Tanggal & Waktu</th>
              <th class="px-6 py-3 text-right font-semibold">Total</th>
              <th class="px-6 py-3 text-right font-semibold">Kembalian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
          <?php
            $no = 1;
            $kasir_warna = ['bg-orange-100 text-orange-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-green-100 text-green-700','bg-pink-100 text-pink-700'];
            $kasir_map = []; $warna_idx = 0;
            foreach($rows as $row):
              $kn = $row['nama_lengkap'];
              if (!isset($kasir_map[$kn])) { $kasir_map[$kn] = $kasir_warna[$warna_idx % count($kasir_warna)]; $warna_idx++; }
              $init = strtoupper(substr($kn,0,1)) . strtoupper(substr(strstr($kn,' ') ?: ' X',1,1));
          ?>
          <tr class="tr-row">
            <td class="px-6 py-4 text-gray-400 text-sm"><?= $no++ ?></td>
            <td class="px-6 py-4">
              <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">
                <?= htmlspecialchars($row['kode_transaksi'] ?? '#'.$row['id']) ?>
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-full <?= $kasir_map[$kn] ?> flex items-center justify-center text-xs font-bold flex-shrink-0"><?= $init ?></span>
                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($kn) ?></span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-700"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
              <div class="text-xs text-gray-400"><?= date('H:i', strtotime($row['tanggal'])) ?></div>
            </td>
            <td class="px-6 py-4 text-right">
              <span class="font-bold text-green-600">Rp <?= number_format($row['total'],0,',','.') ?></span>
            </td>
            <td class="px-6 py-4 text-right text-gray-500 text-sm">
              Rp <?= number_format($row['kembalian'],0,',','.') ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="bg-orange-50 border-t-2 border-orange-200">
              <td colspan="4" class="px-6 py-4 text-sm font-bold text-gray-700">TOTAL (<?= $total_transaksi ?> transaksi)</td>
              <td class="px-6 py-4 text-right font-extrabold text-green-600 text-base">Rp <?= number_format($total_penjualan,0,',','.') ?></td>
              <td class="px-6 py-4"></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <?php else: ?>
      <div class="py-20 text-center">
        <div class="text-6xl mb-4">📋</div>
        <h3 class="text-lg font-bold text-gray-600 mb-2">Tidak Ada Transaksi</h3>
        <p class="text-gray-400 text-sm">Tidak ditemukan transaksi pada rentang tanggal yang dipilih.</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- =====================================================
         TABEL HUTANG PELANGGAN
         ===================================================== -->
    <div id="sec-hutang" class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-gray-800">💳 Hutang Pelanggan</h2>
          <p class="text-gray-400 text-xs mt-0.5">
            <?= $jml_hutang ?> pelanggan belum lunas
          </p>
        </div>
        <?php if($jml_hutang > 0): ?>
        <div class="flex flex-col items-end gap-1">
          <span class="bg-red-50 text-red-600 text-xs font-bold px-3 py-1.5 rounded-xl">
            Sisa: Rp <?= number_format($total_sisa_hutang, 0, ',', '.') ?>
          </span>
          <span class="text-gray-400 text-xs">
            dari Rp <?= number_format($total_hutang_awal, 0, ',', '.') ?> total hutang
          </span>
        </div>
        <?php endif; ?>
      </div>

      <?php if($jml_hutang > 0): ?>

      <!-- Summary bar -->
      <?php
        $pct_lunas = $total_hutang_awal > 0
          ? round(($total_hutang_awal - $total_sisa_hutang) / $total_hutang_awal * 100)
          : 0;
      ?>
      <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
        <div class="flex items-center justify-between text-xs text-gray-500 mb-1.5">
          <span>Progress pelunasan keseluruhan</span>
          <span class="font-semibold text-gray-700"><?= $pct_lunas ?>% terbayar</span>
        </div>
        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-green-500 rounded-full transition-all" style="width:<?= $pct_lunas ?>%"></div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
              <th class="px-6 py-3 text-left font-semibold">No</th>
              <th class="px-6 py-3 text-left font-semibold">Pelanggan</th>
              <th class="px-6 py-3 text-left font-semibold">No HP</th>
              <th class="px-6 py-3 text-left font-semibold">Kode Transaksi</th>
              <th class="px-6 py-3 text-left font-semibold">Tgl Hutang</th>
              <th class="px-6 py-3 text-right font-semibold">Total Hutang</th>
              <th class="px-6 py-3 text-right font-semibold">Sudah Dibayar</th>
              <th class="px-6 py-3 text-right font-semibold">Sisa Hutang</th>
              <th class="px-6 py-3 text-center font-semibold">Progress</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
          <?php $no = 1; foreach($hutang_rows as $h):
            $pct_h     = $h['total_hutang'] > 0 ? round($h['total_dibayar'] / $h['total_hutang'] * 100) : 0;
            $bar_color = $pct_h >= 75 ? 'bg-green-500' : ($pct_h >= 40 ? 'bg-yellow-400' : 'bg-red-400');
            $inisial   = strtoupper(substr($h['nama_pelanggan'],0,1));
          ?>
          <tr class="debt-row">
            <td class="px-6 py-4 text-gray-400 text-sm"><?= $no++ ?></td>

            <!-- Nama pelanggan -->
            <td class="px-6 py-4">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                  <?= $inisial ?>
                </div>
                <span class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($h['nama_pelanggan']) ?></span>
              </div>
            </td>

            <!-- No HP -->
            <td class="px-6 py-4 text-sm text-gray-500">
              <?php if($h['no_hp']): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$h['no_hp']) ?>"
                   target="_blank"
                   class="flex items-center gap-1 text-green-600 hover:text-green-700 font-medium">
                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                  <?= htmlspecialchars($h['no_hp']) ?>
                </a>
              <?php else: ?>
                <span class="text-gray-300 italic">—</span>
              <?php endif; ?>
            </td>

            <!-- Kode transaksi -->
            <td class="px-6 py-4">
              <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">
                <?= htmlspecialchars($h['kode_transaksi']) ?>
              </span>
            </td>

            <!-- Tanggal hutang -->
            <td class="px-6 py-4">
              <div class="text-sm text-gray-700"><?= date('d M Y', strtotime($h['tgl_hutang'])) ?></div>
              <?php
                $hari_lalu = floor((time() - strtotime($h['tgl_hutang'])) / 86400);
              ?>
              <div class="text-xs <?= $hari_lalu > 30 ? 'text-red-400 font-semibold' : 'text-gray-400' ?>">
                <?= $hari_lalu ?> hari lalu
              </div>
            </td>

            <!-- Total hutang -->
            <td class="px-6 py-4 text-right text-sm text-gray-600">
              Rp <?= number_format($h['total_hutang'],0,',','.') ?>
            </td>

            <!-- Sudah dibayar -->
            <td class="px-6 py-4 text-right text-sm text-green-600 font-medium">
              Rp <?= number_format($h['total_dibayar'],0,',','.') ?>
            </td>

            <!-- Sisa hutang -->
            <td class="px-6 py-4 text-right">
              <span class="font-bold text-red-500 text-sm">
                Rp <?= number_format($h['sisa_hutang'],0,',','.') ?>
              </span>
            </td>

            <!-- Progress bar -->
            <td class="px-6 py-4">
              <div class="flex items-center gap-2 min-w-[90px]">
                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div class="h-full <?= $bar_color ?> rounded-full" style="width:<?= $pct_h ?>%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-500 w-8 text-right"><?= $pct_h ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>

          <!-- Footer total hutang -->
          <tfoot>
            <tr class="bg-red-50 border-t-2 border-red-200">
              <td colspan="5" class="px-6 py-4 text-sm font-bold text-gray-700">
                TOTAL HUTANG BELUM LUNAS (<?= $jml_hutang ?> pelanggan)
              </td>
              <td class="px-6 py-4 text-right text-sm font-bold text-gray-600">
                Rp <?= number_format($total_hutang_awal,0,',','.') ?>
              </td>
              <td class="px-6 py-4 text-right text-sm font-bold text-green-600">
                Rp <?= number_format($total_hutang_awal - $total_sisa_hutang,0,',','.') ?>
              </td>
              <td class="px-6 py-4 text-right font-extrabold text-red-500 text-base">
                Rp <?= number_format($total_sisa_hutang,0,',','.') ?>
              </td>
              <td class="px-6 py-4"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <?php else: ?>
      <div class="py-16 text-center">
        <div class="text-6xl mb-4">🎉</div>
        <h3 class="text-lg font-bold text-gray-600 mb-2">Tidak Ada Hutang</h3>
        <p class="text-gray-400 text-sm">Semua pelanggan sudah melunasi hutangnya.</p>
      </div>
      <?php endif; ?>

    </div>
    <!-- /TABEL HUTANG -->

  </div>
</div>

<script>
<?php if(count($chart_labels) > 0): ?>
new Chart(document.getElementById('chartHarian'), {
  type: 'line',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [{
      label: 'Penjualan',
      data: <?= json_encode($chart_data) ?>,
      borderColor: '#f97316',
      backgroundColor: 'rgba(249,115,22,.08)',
      borderWidth: 2.5,
      pointBackgroundColor: '#f97316',
      pointRadius: <?= count($chart_labels) > 20 ? 2 : 4 ?>,
      pointHoverRadius: 6,
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#fff', titleColor: '#374151', bodyColor: '#f97316',
        borderColor: '#e5e7eb', borderWidth: 1, padding: 12, cornerRadius: 10,
        callbacks: { label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') }
      }
    },
    scales: {
      x: { grid:{display:false}, ticks:{font:{family:'Poppins',size:11},color:'#9ca3af',maxTicksLimit:10,autoSkip:true}, border:{display:false} },
      y: { grid:{color:'#f3f4f6'}, ticks:{font:{family:'Poppins',size:11},color:'#9ca3af', callback:v=>'Rp '+(v>=1000000?(v/1000000).toFixed(1)+'jt':(v/1000).toFixed(0)+'rb')}, border:{display:false} }
    }
  }
});
<?php endif; ?>
</script>

<script>
function fmt(d) { return d.toISOString().split('T')[0]; }

document.querySelectorAll('#periodeSegment .seg-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const periode = btn.dataset.periode;
    const today = new Date();
    let dari, sampai;
    if (periode === 'hari')  { dari = sampai = fmt(today); }
    else if (periode === 'minggu') {
      const awal = new Date(today);
      const dow = today.getDay() === 0 ? 7 : today.getDay();
      awal.setDate(today.getDate() - dow + 1);
      dari = fmt(awal); sampai = fmt(today);
    } else if (periode === 'bulan') {
      dari = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-01';
      sampai = fmt(today);
    } else if (periode === 'lalu') {
      dari   = fmt(new Date(today.getFullYear(), today.getMonth()-1, 1));
      sampai = fmt(new Date(today.getFullYear(), today.getMonth(), 0));
    } else if (periode === 'tahun') {
      dari = today.getFullYear() + '-01-01'; sampai = fmt(today);
    }
    document.getElementById('inputDari').value   = dari;
    document.getElementById('inputSampai').value = sampai;
    document.getElementById('selectTahun').value = '';
    document.getElementById('filterForm').submit();
  });
});

document.getElementById('selectTahun').addEventListener('change', function() {
  if (!this.value) return;
  document.getElementById('inputDari').value   = this.value + '-01-01';
  document.getElementById('inputSampai').value = this.value + '-12-31';
  document.getElementById('filterForm').submit();
});
</script>

<script>
if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/pojok_cafe/sw.js'); }
</script>

<script>
/* ── Section Nav: scroll + highlight aktif ── */
function scrollToSection(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const offset = document.getElementById('sectionNav').offsetHeight + 8;
  const top = el.getBoundingClientRect().top + window.scrollY - offset;
  window.scrollTo({ top, behavior: 'smooth' });
}

const sections = ['sec-ringkasan','sec-filter','sec-grafik','sec-kasir','sec-transaksi','sec-hutang'];

function updateActiveTab() {
  const navHeight = document.getElementById('sectionNav').offsetHeight + 16;
  let current = sections[0];
  sections.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.getBoundingClientRect().top <= navHeight + 40) current = id;
  });
  document.querySelectorAll('.nav-tab').forEach(btn => {
    const active = btn.dataset.section === current;
    btn.className = btn.className
      .replace(/bg-orange-500 text-white|bg-white text-gray-500 hover:bg-orange-50 hover:text-orange-600/g, '')
      .trim();
    btn.classList.add(...(active
      ? ['bg-orange-500','text-white']
      : ['bg-white','text-gray-500','hover:bg-orange-50','hover:text-orange-600']));
  });
}

window.addEventListener('scroll', updateActiveTab, { passive: true });
updateActiveTab();
</script>

</body>
</html>