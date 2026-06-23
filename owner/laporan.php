<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id'])) { header("Location: ../auth/login.php"); exit; }
if ((int)$_SESSION['role_id'] !== 1) { header("Location: ../karyawan/dashboard.php"); exit; }

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

/* ===== TRANSAKSI ===== */
$dari_safe   = mysqli_real_escape_string($conn, $dari);
$sampai_safe = mysqli_real_escape_string($conn, $sampai);

$query = mysqli_query($conn, "
    SELECT t.*, u.nama_lengkap
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE DATE(t.tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    ORDER BY t.id DESC
");

$rows            = [];
$total_penjualan = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $rows[]           = $row;
    $total_penjualan += $row['total'];
}
$total_transaksi = count($rows);
$rata_rata       = $total_transaksi > 0 ? $total_penjualan / $total_transaksi : 0;

/* ===== GRAFIK HARIAN (dalam range) ===== */
$grafik_q = mysqli_query($conn, "
    SELECT DATE(tanggal) tgl, SUM(total) total, COUNT(*) jml
    FROM transactions
    WHERE DATE(tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    GROUP BY DATE(tanggal)
    ORDER BY tgl ASC
");
$chart_labels = [];
$chart_data   = [];
$chart_jml    = [];
while ($g = mysqli_fetch_assoc($grafik_q)) {
    $chart_labels[] = date('d/m', strtotime($g['tgl']));
    $chart_data[]   = (float)$g['total'];
    $chart_jml[]    = (int)$g['jml'];
}

/* ===== TOP KASIR ===== */
$kasir_q = mysqli_query($conn, "
    SELECT u.nama_lengkap, COUNT(t.id) jml, SUM(t.total) total
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE DATE(t.tanggal) BETWEEN '$dari_safe' AND '$sampai_safe'
    GROUP BY t.user_id
    ORDER BY total DESC
    LIMIT 5
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
    GROUP BY td.product_id
    ORDER BY qty DESC
    LIMIT 5
");
$top_produk = [];
while ($p = mysqli_fetch_assoc($produk_q)) $top_produk[] = $p;

/* ===== SELISIH HARI ===== */
$selisih_hari = (strtotime($sampai) - strtotime($dari)) / 86400 + 1;

/* ===== PENJUALAN PERIODE SEBELUMNYA ===== */
$dari_prev   = date('Y-m-d', strtotime($dari) - $selisih_hari * 86400);
$sampai_prev = date('Y-m-d', strtotime($dari) - 86400);
$prev_q = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) total FROM transactions WHERE DATE(tanggal) BETWEEN '$dari_prev' AND '$sampai_prev'");
$prev_total = (float)mysqli_fetch_assoc($prev_q)['total'];
$growth = $prev_total > 0 ? (($total_penjualan - $prev_total) / $prev_total * 100) : null;
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
  @media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .lg\\:ml-64 { margin-left: 0 !important; }
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
        <a href="export_excel.php?dari=<?= $dari ?>&sampai=<?= $sampai ?>"
           class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
          Excel
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

    <!-- STAT CARDS (overlap header) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 -mt-12 relative z-20 mb-6">

      <!-- Total Penjualan -->
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

      <!-- Total Transaksi -->
      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl">🧾</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Transaksi</p>
          <h2 class="text-2xl font-extrabold text-gray-800"><?= number_format($total_transaksi) ?></h2>
        </div>
      </div>

      <!-- Rata-rata -->
      <div class="stat-card bg-white rounded-2xl shadow-md p-5 flex flex-col justify-between">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl">📈</div>
        <div class="mt-3">
          <p class="text-gray-400 text-xs font-medium">Rata-rata / Transaksi</p>
          <h2 class="text-xl font-extrabold text-gray-800">Rp <?= number_format($rata_rata, 0, ',', '.') ?></h2>
        </div>
      </div>

    </div>

    <!-- FILTER FORM -->
    <div class="bg-white rounded-3xl shadow-sm p-5 mb-6 no-print">
      <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[140px]">
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
          <input type="date" name="dari" value="<?= $dari ?>"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition">
        </div>
        <div class="flex-1 min-w-[140px]">
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
          <input type="date" name="sampai" value="<?= $sampai ?>"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition">
        </div>

        <!-- Shortcut periode -->
        <div class="flex gap-2 flex-wrap items-end">
          <?php
            $shortcuts = [
              'Hari Ini'    => [date('Y-m-d'), date('Y-m-d')],
              'Minggu Ini'  => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
              'Bulan Ini'   => [date('Y-m-01'), date('Y-m-d')],
              'Bulan Lalu'  => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
            ];
            foreach($shortcuts as $label => [$d, $s]):
              $active = ($dari === $d && $sampai === $s);
          ?>
          <a href="?dari=<?= $d ?>&sampai=<?= $s ?>"
            class="px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= $active ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600' ?>">
            <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>

        <button type="submit"
          class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition flex-shrink-0">
          Filter
        </button>
      </form>
    </div>

    <!-- GRAFIK + TOP SECTION -->
    <div class="grid lg:grid-cols-3 gap-5 mb-6">

      <!-- Grafik Harian -->
      <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-base font-bold text-gray-800">Tren Penjualan Harian</h2>
            <p class="text-gray-400 text-xs mt-0.5"><?= count($chart_labels) ?> hari dengan data</p>
          </div>
        </div>
        <?php if(count($chart_labels) > 0): ?>
        <div style="position:relative; height:220px;">
          <canvas id="chartHarian" role="img" aria-label="Grafik penjualan harian">Data penjualan harian.</canvas>
        </div>
        <?php else: ?>
        <div class="h-52 flex flex-col items-center justify-center text-gray-300">
          <div class="text-5xl mb-2">📉</div>
          <p class="text-sm">Tidak ada data untuk rentang ini</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Top Produk -->
      <div class="bg-white rounded-3xl shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-4">🔥 Produk Terlaris</h2>
        <?php if(count($top_produk) > 0):
          $max_qty = max(array_column($top_produk, 'qty'));
        ?>
        <div class="space-y-3">
        <?php foreach($top_produk as $i => $tp):
          $pct = $max_qty > 0 ? round($tp['qty'] / $max_qty * 100) : 0;
          $colors = ['bg-orange-500','bg-orange-400','bg-amber-400','bg-gray-300','bg-gray-200'];
          $tc     = ['text-white','text-white','text-white','text-gray-600','text-gray-500'];
        ?>
        <div class="flex items-center gap-3">
          <span class="w-6 h-6 rounded-full <?= $colors[$i] ?> <?= $tc[$i] ?> flex items-center justify-center text-xs font-bold flex-shrink-0">
            <?= $i + 1 ?>
          </span>
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
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-6">
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
          $initials = strtoupper(substr($k['nama_lengkap'], 0, 1))
            . strtoupper(substr(strstr($k['nama_lengkap'], ' ') ?: ' X', 1, 1));
      ?>
      <div class="<?= $clr[0] ?> rounded-2xl p-4 flex flex-col gap-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl <?= $clr[2] ?> text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
            <?= $initials ?>
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($k['nama_lengkap']) ?></p>
            <p class="text-xs text-gray-500"><?= $k['jml'] ?> transaksi</p>
          </div>
        </div>
        <p class="font-bold text-sm <?= $clr[1] ?>">Rp <?= number_format($k['total'], 0, ',', '.') ?></p>
        <div class="h-1.5 bg-white/60 rounded-full overflow-hidden">
          <div class="h-full <?= $clr[2] ?> rounded-full" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- TABEL TRANSAKSI -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <!-- Header tabel -->
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
            $kasir_map = [];
            $warna_idx = 0;
            foreach($rows as $row):
              $kn = $row['nama_lengkap'];
              if (!isset($kasir_map[$kn])) { $kasir_map[$kn] = $kasir_warna[$warna_idx % count($kasir_warna)]; $warna_idx++; }
              $init = strtoupper(substr($kn, 0, 1)) . strtoupper(substr(strstr($kn, ' ') ?: ' X', 1, 1));
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
                <span class="w-7 h-7 rounded-full <?= $kasir_map[$kn] ?> flex items-center justify-center text-xs font-bold flex-shrink-0">
                  <?= $init ?>
                </span>
                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($kn) ?></span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-700"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
              <div class="text-xs text-gray-400"><?= date('H:i', strtotime($row['tanggal'])) ?></div>
            </td>
            <td class="px-6 py-4 text-right">
              <span class="font-bold text-green-600">Rp <?= number_format($row['total'], 0, ',', '.') ?></span>
            </td>
            <td class="px-6 py-4 text-right text-gray-500 text-sm">
              Rp <?= number_format($row['kembalian'], 0, ',', '.') ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>

          <!-- Footer total -->
          <tfoot>
            <tr class="bg-orange-50 border-t-2 border-orange-200">
              <td colspan="4" class="px-6 py-4 text-sm font-bold text-gray-700">
                TOTAL (<?= $total_transaksi ?> transaksi)
              </td>
              <td class="px-6 py-4 text-right font-extrabold text-green-600 text-base">
                Rp <?= number_format($total_penjualan, 0, ',', '.') ?>
              </td>
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

  </div><!-- /px-6 -->
</div><!-- /lg:ml-64 -->

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
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#fff',
        titleColor: '#374151',
        bodyColor: '#f97316',
        borderColor: '#e5e7eb',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10,
        callbacks: {
          label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { family:'Poppins', size:11 }, color:'#9ca3af',
          maxTicksLimit: 10, autoSkip: true },
        border: { display: false }
      },
      y: {
        grid: { color: '#f3f4f6' },
        ticks: {
          font: { family:'Poppins', size:11 }, color:'#9ca3af',
          callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v/1000).toFixed(0)+'rb')
        },
        border: { display: false }
      }
    }
  }
});
<?php endif; ?>
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>