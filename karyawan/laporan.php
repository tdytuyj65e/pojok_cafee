<?php
include "../koneksi.php";

/* =========================
   PROSES PELUNASAN (POST)
========================= */
$pesan_bayar = '';
$error_bayar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debt_id'])) {
    $debt_id      = (int)$_POST['debt_id'];
    $jumlah_bayar = (float)str_replace(['.'], [''], $_POST['jumlah_bayar'] ?? 0);
    $metode       = in_array($_POST['metode'] ?? '', ['cash','qris']) ? $_POST['metode'] : 'cash';

    if ($debt_id <= 0 || $jumlah_bayar <= 0) {
        $error_bayar = 'Data tidak valid.';
    } else {
        $row = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT d.*, c.nama FROM debts d
             JOIN customers c ON c.id = d.customer_id
             WHERE d.id = $debt_id LIMIT 1"));

        if (!$row) {
            $error_bayar = 'Data hutang tidak ditemukan.';
        } elseif ($row['status'] === 'lunas') {
            $error_bayar = 'Hutang ini sudah lunas.';
        } elseif ($jumlah_bayar > (float)$row['sisa_hutang']) {
            $error_bayar = 'Jumlah bayar melebihi sisa hutang Rp ' . number_format($row['sisa_hutang'],0,',','.') . '.';
        } else {
            $sisa_baru   = (float)$row['sisa_hutang'] - $jumlah_bayar;
            $status_baru = $sisa_baru <= 0 ? 'lunas' : 'belum_lunas';
            $metodeSafe  = mysqli_real_escape_string($conn, $metode);

            mysqli_query($conn, "
                UPDATE debts
                SET sisa_hutang = $sisa_baru,
                    status      = '$status_baru'
                WHERE id        = $debt_id
            ");

            mysqli_query($conn, "
                INSERT INTO debt_payments (debt_id, jumlah_bayar, metode, tanggal)
                VALUES ($debt_id, $jumlah_bayar, '$metodeSafe', NOW())
            ");

            $nama = htmlspecialchars($row['nama']);
            $pesan_bayar = 'Pembayaran <b>Rp ' . number_format($jumlah_bayar,0,',','.') .
                           '</b> untuk <b>' . $nama . '</b> berhasil dicatat.' .
                           ($status_baru === 'lunas'
                               ? ' Hutang sudah <span class="font-bold text-green-700">LUNAS ✓</span>.'
                               : ' Sisa hutang: <b>Rp ' . number_format($sisa_baru,0,',','.') . '</b>.');
        }
    }
}

/* =========================
   FILTER
========================= */
$periode = $_GET['periode'] ?? 'hari';
$search  = $_GET['q'] ?? '';

$filter = "1=1";
if ($periode == 'hari') {
    $filter = "DATE(tanggal) = CURDATE()";
} elseif ($periode == 'minggu') {
    $filter = "YEARWEEK(tanggal, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($periode == 'bulan') {
    $filter = "MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
} elseif ($periode == 'tahun') {
    $filter = "YEAR(tanggal) = YEAR(CURDATE())";
}

if ($search != '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $filter .= " AND kode_transaksi LIKE '%$searchEsc%'";
}

/* =========================
   RINGKASAN
========================= */
$ringkasan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT
        COUNT(*) as total_transaksi,
        COALESCE(SUM(total),0) as pendapatan,
        COALESCE(AVG(total),0) as rata_rata
    FROM transactions
    WHERE $filter
"));

/* =========================
   TRANSAKSI
========================= */
$query = mysqli_query($conn, "
    SELECT * FROM transactions
    WHERE $filter
    ORDER BY tanggal DESC
");

/* =========================
   EXPORT CSV TRANSAKSI
========================= */
if (($_GET['export'] ?? '') === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_'.$periode.'_'.date('Ymd_His').'.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Kode Transaksi', 'Tanggal', 'Metode', 'Total'], ';');
    while ($t = mysqli_fetch_assoc($query)) {
        fputcsv($out, [$t['kode_transaksi'], $t['tanggal'], strtoupper($t['metode_pembayaran']), $t['total']], ';');
    }
    fclose($out);
    exit;
}

/* =========================
   EXPORT CSV HUTANG
========================= */
$statusHutang = $_GET['status_hutang'] ?? 'belum_lunas';

if (($_GET['export'] ?? '') === 'excel_hutang') {
    $shExp = in_array($statusHutang, ['belum_lunas','lunas'])
        ? "d.status = '" . mysqli_real_escape_string($conn, $statusHutang) . "'"
        : "1=1";

    $qExp = mysqli_query($conn, "
        SELECT c.nama, c.no_hp, c.alamat,
               d.total_hutang, d.sisa_hutang, d.status, d.created_at
        FROM debts d
        JOIN customers c ON c.id = d.customer_id
        WHERE $shExp
        ORDER BY d.sisa_hutang DESC, c.nama ASC
    ");

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="hutang_'.date('Ymd_His').'.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['No','Nama','No HP','Alamat','Total Hutang','Sisa Hutang','Status','Tgl Hutang'], ';');
    $no = 1;
    while ($r = mysqli_fetch_assoc($qExp)) {
        fputcsv($out, [
            $no++,
            $r['nama'],
            $r['no_hp'] ?: '-',
            $r['alamat'] ?: '-',
            $r['total_hutang'],
            $r['sisa_hutang'],
            $r['status'] === 'lunas' ? 'Lunas' : 'Belum Lunas',
            date('d/m/Y', strtotime($r['created_at'])),
        ], ';');
    }
    fclose($out);
    exit;
}

/* =========================
   PRODUK TERLARIS
========================= */
$qitem = mysqli_query($conn, "
    SELECT
        p.nama_produk,
        SUM(td.qty) as qty,
        SUM(td.subtotal) as pendapatan
    FROM transaction_details td
    JOIN products p ON p.id = td.product_id
    JOIN transactions t ON t.id = td.transaction_id
    WHERE $filter
    GROUP BY td.product_id
    ORDER BY qty DESC
    LIMIT 5
");
$produk_terlaris = [];
while ($row = mysqli_fetch_assoc($qitem)) {
    $produk_terlaris[] = $row;
}

/* =========================
   GRAFIK 7 HARI
========================= */
$grafik_labels = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
$grafik_values = [];
for ($i = 0; $i < 7; $i++) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COALESCE(SUM(total),0) as total
        FROM transactions
        WHERE DATE(tanggal) = DATE(NOW() - INTERVAL " . (6-$i) . " DAY)
    "));
    $grafik_values[] = (float)$r['total'];
}

/* =========================
   DAFTAR HUTANG
========================= */
$filterHutang = "1=1";
if (in_array($statusHutang, ['belum_lunas','lunas'])) {
    $sh = mysqli_real_escape_string($conn, $statusHutang);
    $filterHutang = "d.status = '$sh'";
}

$qhutang = mysqli_query($conn, "
    SELECT d.id as debt_id, c.nama, c.no_hp,
           d.total_hutang, d.sisa_hutang, d.status
    FROM debts d
    JOIN customers c ON c.id = d.customer_id
    WHERE $filterHutang
    ORDER BY d.sisa_hutang DESC, c.nama ASC
");
$daftar_hutang = [];
$total_piutang = 0;
while ($h = mysqli_fetch_assoc($qhutang)) {
    $daftar_hutang[] = $h;
    $total_piutang  += (float)$h['sisa_hutang'];
}

/* helpers */
function pk_filterUrl($periode, $search, $extra = []) {
    $params = array_merge(['periode' => $periode, 'q' => $search], $extra);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '?' . http_build_query($params);
}
function pk_hutangUrl($statusHutang, $periode, $search) {
    $params = array_filter([
        'periode'       => $periode,
        'q'             => $search,
        'status_hutang' => $statusHutang,
    ], fn($v) => $v !== '' && $v !== null);
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan | Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "on-tertiary-fixed-variant":"#713700","tertiary-container":"#a9632c",
        "inverse-surface":"#402c1f","on-primary-fixed-variant":"#723600",
        "surface-container-lowest":"#ffffff","on-secondary":"#ffffff",
        "on-secondary-fixed":"#281807","inverse-on-surface":"#ffede4",
        "error":"#ba1a1a","on-secondary-fixed-variant":"#57432e",
        "surface-dim":"#f5d3c0","on-primary-fixed":"#311400","on-primary":"#ffffff",
        "primary-fixed-dim":"#ffb786","on-tertiary-container":"#fffbff",
        "on-surface-variant":"#544339","on-surface":"#29170c",
        "secondary-fixed-dim":"#dec1a6","on-error-container":"#93000a",
        "tertiary":"#8b4b15","inverse-primary":"#ffb786","on-tertiary":"#ffffff",
        "on-background":"#29170c","outline-variant":"#d9c2b5",
        "surface-bright":"#fff8f5","secondary-container":"#fcddc1",
        "primary":"#8e4a0e","surface-container":"#ffeadf","on-error":"#ffffff",
        "surface":"#fff8f5","surface-container-highest":"#fedcc8",
        "surface-container-high":"#ffe3d3","secondary-fixed":"#fcddc1",
        "primary-container":"#ad6126","tertiary-fixed-dim":"#ffb784",
        "background":"#fff8f5","on-tertiary-fixed":"#301400",
        "error-container":"#ffdad6","on-secondary-container":"#77604a",
        "surface-container-low":"#fff1ea","tertiary-fixed":"#ffdcc5",
        "on-primary-container":"#fffbff","outline":"#867368",
        "surface-variant":"#fedcc8","secondary":"#705a44",
        "surface-tint":"#914c10","primary-fixed":"#ffdcc6"
      },
      borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},
      fontFamily:{
        "headline-md":["Plus Jakarta Sans"],"headline-lg":["Plus Jakarta Sans"],
        "button-text":["Plus Jakarta Sans"],"body-md":["Plus Jakarta Sans"],
        "label-md":["Plus Jakarta Sans"]
      },
      fontSize:{
        "headline-md":["18px",{lineHeight:"24px",fontWeight:"600"}],
        "headline-lg":["24px",{lineHeight:"32px",fontWeight:"700"}],
        "button-text":["14px",{lineHeight:"20px",fontWeight:"600"}],
        "body-md":["14px",{lineHeight:"20px",fontWeight:"400"}],
        "label-md":["12px",{lineHeight:"16px",letterSpacing:"0.02em",fontWeight:"500"}]
      }
    }
  }
}
</script>
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;min-height:max(884px,100dvh);background-color:#fff8f5;}
.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;}
.card-shadow{box-shadow:0px 2px 12px rgba(200,119,58,.12);}
.button-shadow{box-shadow:0px 4px 12px rgba(200,119,58,.30);}
.filter-chip{transition:background .15s ease,color .15s ease,border-color .15s ease;}
.filter-chip.active{background:#8e4a0e;color:#fff;border-color:#8e4a0e;}
.bar-fill{transition:height .4s ease;min-height:4px;}
.rank-badge{width:22px;height:22px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;}
table tbody tr:nth-child(even){background:#fff8f5;}

/* Modal */
#modal-hutang{display:none;}
#modal-hutang.open{display:flex;}

/* Slide up on mobile */
@keyframes slideUp{from{transform:translateY(100%);}to{transform:translateY(0);}}
#modal-box{animation:slideUp .25s ease;}

@media print{
  #pkActionBar,#pkFilterBar,#pkNavbarSlot,#pkPrintHint,#modal-hutang{display:none!important;}
  body{background:#fff!important;}
  .card-shadow,.button-shadow{box-shadow:none!important;}
}
</style>
</head>

<body class="text-on-surface pb-28">
<div class="max-w-5xl mx-auto px-4 py-6 space-y-6">

<!-- HEADER -->
<div class="flex justify-between items-start gap-3" id="pkActionBar">
    <div>
        <h1 class="text-xl font-bold text-on-surface">Laporan Penjualan</h1>
        <p class="text-sm text-on-surface-variant">Ringkasan transaksi &amp; produk terlaris</p>
    </div>
    <div class="flex gap-2 flex-shrink-0">
        <button type="button" onclick="window.print()"
            class="px-3 h-9 bg-error text-on-error rounded-lg flex items-center gap-1.5 text-sm font-medium button-shadow">
            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
            <span class="hidden sm:inline">Cetak/PDF</span>
        </button>
        <a href="<?= pk_filterUrl($periode, $search, ['export'=>'excel']) ?>"
            class="px-3 h-9 bg-green-600 text-white rounded-lg flex items-center gap-1.5 text-sm font-medium button-shadow">
            <span class="material-symbols-outlined text-[18px]">file_download</span>
            <span class="hidden sm:inline">Excel</span>
        </a>
    </div>
</div>

<!-- NOTIFIKASI PELUNASAN -->
<?php if ($pesan_bayar): ?>
<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-start gap-3">
    <span class="material-symbols-outlined text-green-600 mt-0.5 flex-shrink-0">check_circle</span>
    <p class="text-sm"><?= $pesan_bayar ?></p>
</div>
<?php endif; ?>
<?php if ($error_bayar): ?>
<div class="bg-error-container border border-error/30 text-error px-4 py-3 rounded-xl flex items-start gap-3">
    <span class="material-symbols-outlined mt-0.5 flex-shrink-0">error</span>
    <p class="text-sm"><?= htmlspecialchars($error_bayar) ?></p>
</div>
<?php endif; ?>

<!-- FILTER -->
<div class="space-y-3" id="pkFilterBar">
    <div class="flex gap-2 overflow-x-auto">
        <?php
        $opsiPeriode = ['hari'=>'Hari ini','minggu'=>'Minggu ini','bulan'=>'Bulan ini','tahun'=>'Tahun ini'];
        foreach ($opsiPeriode as $key => $label):
            $isActive = $periode == $key;
        ?>
        <a href="<?= pk_filterUrl($key, $search) ?>"
           class="filter-chip <?= $isActive?'active':'' ?> px-4 py-1.5 border border-outline-variant bg-white rounded-full text-sm whitespace-nowrap flex-shrink-0">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" class="flex gap-2">
        <input type="hidden" name="periode" value="<?= htmlspecialchars($periode) ?>">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#a98f7e] text-[20px]">search</span>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari kode transaksi..."
                class="w-full h-[42px] pl-10 pr-3 text-sm rounded-xl border border-outline-variant bg-white focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>
        <button type="submit" class="h-[42px] px-4 bg-primary text-on-primary rounded-xl text-sm font-medium">Cari</button>
        <?php if ($search != ''): ?>
        <a href="<?= pk_filterUrl($periode, '') ?>" class="h-[42px] px-3 flex items-center text-sm text-on-surface-variant">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- RINGKASAN -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-[#a9632c] text-white p-4 rounded-xl card-shadow space-y-1">
        <div class="flex items-center gap-2 text-sm opacity-90">
            <span class="material-symbols-outlined text-[18px]">payments</span>
            Total Pendapatan
        </div>
        <b class="text-lg block">Rp <?= number_format($ringkasan['pendapatan'],0,',','.') ?></b>
    </div>
    <div class="bg-white p-4 rounded-xl card-shadow space-y-1">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[18px] text-primary">receipt_long</span>
            Total Transaksi
        </div>
        <b class="text-lg block text-on-surface"><?= $ringkasan['total_transaksi'] ?></b>
    </div>
    <div class="bg-white p-4 rounded-xl card-shadow space-y-1">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[18px] text-primary">finance</span>
            Rata-rata / Transaksi
        </div>
        <b class="text-lg block text-on-surface">Rp <?= number_format($ringkasan['rata_rata'],0,',','.') ?></b>
    </div>
</div>

<!-- PRODUK TERLARIS -->
<div class="bg-white p-4 rounded-xl card-shadow">
    <h2 class="font-bold mb-3 text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[20px]">local_fire_department</span>
        Produk Terlaris
    </h2>
    <?php if (empty($produk_terlaris)): ?>
        <p class="text-sm text-on-surface-variant py-4 text-center">Belum ada data penjualan di periode ini.</p>
    <?php else: ?>
        <?php $rankColors = ['bg-amber-400 text-white','bg-gray-300 text-gray-700','bg-orange-300 text-white'];
        foreach ($produk_terlaris as $i => $p):
            $rankClass = $rankColors[$i] ?? 'bg-secondary-container text-primary'; ?>
        <div class="flex items-center gap-3 py-2 <?= $i < count($produk_terlaris)-1 ? 'border-b border-outline-variant/60' : '' ?>">
            <span class="rank-badge <?= $rankClass ?>"><?= $i+1 ?></span>
            <span class="flex-1 text-sm text-on-surface truncate"><?= htmlspecialchars($p['nama_produk']) ?></span>
            <span class="text-xs text-on-surface-variant w-16 text-right"><?= $p['qty'] ?> pcs</span>
            <b class="text-sm text-primary w-28 text-right">Rp <?= number_format($p['pendapatan'],0,',','.') ?></b>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- GRAFIK 7 HARI -->
<div class="bg-white p-4 rounded-xl card-shadow">
    <h2 class="font-bold mb-4 text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[20px]">bar_chart</span>
        Grafik 7 Hari Terakhir
    </h2>
    <?php $max = max($grafik_values) ?: 1; ?>
    <div class="flex items-end gap-2 h-36">
        <?php foreach ($grafik_values as $i => $v): ?>
        <div class="flex-1 flex flex-col items-center justify-end gap-1.5 h-full" title="Rp <?= number_format($v,0,',','.') ?>">
            <span class="text-[10px] text-on-surface-variant"><?= $v > 0 ? number_format($v/1000,0,',','.').'rb' : '' ?></span>
            <div class="bar-fill w-full rounded-t-md bg-primary" style="height:<?= max((($v/$max)*100), 2) ?>%"></div>
            <small class="text-[11px] text-on-surface-variant"><?= $grafik_labels[$i] ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================================
     ORANG YANG BERHUTANG
     ============================================================ -->
<div class="bg-white p-4 rounded-xl card-shadow">

    <!-- Header baris -->
    <div class="flex items-start justify-between gap-3 mb-3 flex-wrap">
        <h2 class="font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-error text-[20px]">credit_card</span>
            Orang yang Berhutang
        </h2>

        <div class="flex flex-wrap gap-2">
            <!-- Filter status hutang -->
            <?php
            $opsiHutang = ['belum_lunas'=>'Belum Lunas','lunas'=>'Lunas','semua'=>'Semua'];
            foreach ($opsiHutang as $key => $label):
                $isActive = $statusHutang === $key
                    || ($key === 'semua' && !in_array($statusHutang, ['belum_lunas','lunas']));
            ?>
            <a href="<?= pk_hutangUrl($key, $periode, $search) ?>"
               class="filter-chip <?= $isActive?'active':'' ?> px-3 py-1 border border-outline-variant bg-white rounded-full text-xs whitespace-nowrap">
                <?= $label ?>
            </a>
            <?php endforeach; ?>

            <!-- Export Excel Hutang -->
            <a href="<?= pk_hutangUrl($statusHutang, $periode, $search) . '&export=excel_hutang' ?>"
               class="px-3 py-1 bg-green-600 text-white rounded-full text-xs font-semibold flex items-center gap-1 whitespace-nowrap button-shadow">
                <span class="material-symbols-outlined text-[14px]">file_download</span>
                Excel
            </a>
        </div>
    </div>

    <!-- Total piutang -->
    <?php if ($statusHutang !== 'lunas'): ?>
    <div class="bg-error-container/40 rounded-lg px-4 py-2.5 mb-3 flex items-center justify-between">
        <span class="text-xs text-on-surface-variant">Total Piutang Ditampilkan</span>
        <b class="text-sm text-error">Rp <?= number_format($total_piutang,0,',','.') ?></b>
    </div>
    <?php endif; ?>

    <?php if (empty($daftar_hutang)): ?>
        <p class="text-sm text-on-surface-variant py-4 text-center">Tidak ada data hutang untuk filter ini.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[520px]">
            <thead>
                <tr class="border-b border-outline-variant text-left text-on-surface-variant">
                    <th class="py-2 font-medium">Nama</th>
                    <th class="py-2 font-medium">No HP</th>
                    <th class="py-2 font-medium text-right">Total Hutang</th>
                    <th class="py-2 font-medium text-right">Sisa Hutang</th>
                    <th class="py-2 font-medium text-center">Status</th>
                    <th class="py-2 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_hutang as $h): ?>
                <tr class="border-b border-outline-variant/60">
                    <td class="py-2.5 font-medium text-on-surface"><?= htmlspecialchars($h['nama']) ?></td>
                    <td class="py-2.5 text-on-surface-variant"><?= htmlspecialchars($h['no_hp'] ?: '-') ?></td>
                    <td class="py-2.5 text-right text-on-surface-variant">Rp <?= number_format($h['total_hutang'],0,',','.') ?></td>
                    <td class="py-2.5 text-right font-semibold <?= $h['sisa_hutang']>0?'text-error':'text-on-surface-variant' ?>">
                        Rp <?= number_format($h['sisa_hutang'],0,',','.') ?>
                    </td>
                    <td class="py-2.5 text-center">
                        <?php if ($h['status']==='lunas'): ?>
                            <span class="bg-green-100 text-green-700 text-[11px] font-semibold px-2.5 py-1 rounded-full">Lunas</span>
                        <?php else: ?>
                            <span class="bg-error-container text-error text-[11px] font-semibold px-2.5 py-1 rounded-full">Belum Lunas</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-2.5 text-center">
                        <?php if ($h['status'] !== 'lunas'): ?>
                        <button type="button"
                            onclick="bukaModal(<?= $h['debt_id'] ?>, '<?= addslashes(htmlspecialchars($h['nama'])) ?>', <?= $h['sisa_hutang'] ?>)"
                            class="px-2.5 py-1 bg-primary text-on-primary text-[11px] font-semibold rounded-lg hover:opacity-90 transition">
                            Bayar
                        </button>
                        <?php else: ?>
                        <span class="text-xs text-on-surface-variant">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- TRANSAKSI -->
<div class="bg-white p-4 rounded-xl card-shadow">
    <h2 class="font-bold mb-3 text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[20px]">history</span>
        Riwayat Transaksi
    </h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[420px]">
            <thead>
                <tr class="border-b border-outline-variant text-left text-on-surface-variant">
                    <th class="py-2 font-medium">Kode</th>
                    <th class="py-2 font-medium">Waktu</th>
                    <th class="py-2 font-medium text-center">Metode</th>
                    <th class="py-2 font-medium text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $adaTransaksi = false;
                while ($t = mysqli_fetch_assoc($query)):
                    $adaTransaksi = true;
                ?>
                <tr class="border-b border-outline-variant/60">
                    <td class="py-2.5 font-medium text-on-surface"><?= htmlspecialchars($t['kode_transaksi']) ?></td>
                    <td class="py-2.5 text-on-surface-variant"><?= date('d/m H:i', strtotime($t['tanggal'])) ?></td>
                    <td class="py-2.5 text-center">
                        <?php if (($t['metode_pembayaran']??'cash')==='qris'): ?>
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full">QRIS</span>
                        <?php else: ?>
                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">CASH</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-2.5 text-right font-semibold text-primary">
                        Rp <?= number_format($t['total'],0,',','.') ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (!$adaTransaksi): ?>
                <tr>
                    <td colspan="4" class="py-8 text-center text-on-surface-variant">
                        Tidak ada transaksi pada periode/pencarian ini.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<p id="pkPrintHint" class="text-center text-[11px] text-on-surface-variant">
    Tips: tombol "Cetak/PDF" membuka dialog cetak browser — pilih "Simpan sebagai PDF" untuk menyimpan laporan ini.
</p>

</div><!-- /container -->

<!-- =====================================================
     MODAL PELUNASAN HUTANG (hidden, muncul lewat JS)
     ===================================================== -->
<div id="modal-hutang"
     class="fixed inset-0 bg-black/50 z-50 items-end sm:items-center justify-center"
     onclick="tutupModal(event)">

    <div id="modal-box"
         class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden"
         onclick="event.stopPropagation()">

        <!-- Header modal -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant">
            <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">payments</span>
                Catat Pelunasan Hutang
            </h3>
            <button type="button" onclick="tutupModalBtn()"
                class="p-1.5 rounded-full hover:bg-surface-container-high transition text-on-surface-variant">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Info debitur -->
        <div class="mx-5 mt-4 bg-surface-container-low rounded-xl px-4 py-3 space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-on-surface-variant">Nama</span>
                <b id="modal-nama" class="text-on-surface text-right">-</b>
            </div>
            <div class="flex justify-between items-center border-t border-outline-variant/50 pt-2">
                <span class="text-on-surface-variant">Sisa Hutang</span>
                <b id="modal-sisa" class="text-error text-right">-</b>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" id="form-lunasi" class="px-5 pb-5 pt-4 space-y-4">
            <!-- Preserve filter state -->
            <input type="hidden" name="periode"       value="<?= htmlspecialchars($periode) ?>">
            <input type="hidden" name="status_hutang" value="<?= htmlspecialchars($statusHutang) ?>">
            <input type="hidden" name="q"             value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="debt_id"       id="input-debt-id">

            <!-- Nominal -->
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-on-surface-variant">Jumlah Bayar</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm font-semibold select-none">Rp</span>
                    <input type="text" id="input-jumlah" name="jumlah_bayar" required
                        inputmode="numeric" placeholder="0"
                        class="w-full h-[46px] pl-10 pr-3 text-sm rounded-xl border border-outline-variant bg-white focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold"
                        oninput="fmtRupiah(this)">
                </div>
                <!-- Quick buttons -->
                <div id="quick-pay" class="flex flex-wrap gap-2 pt-1"></div>
            </div>

            <!-- Metode -->
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-on-surface-variant">Metode Pembayaran</label>
                <div class="flex gap-3">
                    <label class="flex-1 flex items-center gap-2 border border-outline-variant rounded-xl px-3 py-2.5 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition">
                        <input type="radio" name="metode" value="cash" checked class="accent-[#8e4a0e]">
                        <span class="material-symbols-outlined text-[18px] text-green-600">payments</span>
                        <span class="text-sm font-medium">Cash</span>
                    </label>
                    <label class="flex-1 flex items-center gap-2 border border-outline-variant rounded-xl px-3 py-2.5 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition">
                        <input type="radio" name="metode" value="qris" class="accent-[#8e4a0e]">
                        <span class="material-symbols-outlined text-[18px] text-blue-600">qr_code_2</span>
                        <span class="text-sm font-medium">QRIS</span>
                    </label>
                </div>
            </div>

            <!-- Tombol aksi -->
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="tutupModalBtn()"
                    class="flex-1 h-11 border border-outline-variant rounded-xl text-sm font-medium hover:bg-surface-container transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 h-11 bg-primary text-on-primary rounded-xl text-sm font-semibold button-shadow hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    Simpan
                </button>
            </div>
        </form>

    </div>
</div>

<div id="pkNavbarSlot">
<?php include "navbar_karyawan.php"; ?>
</div>

<script>
/* ---- Format input rupiah ---- */
function fmtRupiah(el) {
    const raw = el.value.replace(/\D/g, '');
    el.value  = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
}

/* ---- State modal ---- */
let _sisaAktif = 0;

function bukaModal(debtId, nama, sisa) {
    _sisaAktif = parseFloat(sisa);

    document.getElementById('input-debt-id').value = debtId;
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('modal-sisa').textContent = 'Rp ' + _sisaAktif.toLocaleString('id-ID');
    document.getElementById('input-jumlah').value = '';

    /* Quick-pay buttons */
    const qp = document.getElementById('quick-pay');
    qp.innerHTML = '';
    [
        { label: 'Lunas penuh', val: _sisaAktif },
        { label: '½ bayar',    val: Math.floor(_sisaAktif / 2) },
        { label: '¼ bayar',    val: Math.floor(_sisaAktif / 4) },
    ].filter(o => o.val > 0).forEach(o => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = `${o.label} <span class="font-bold">Rp ${o.val.toLocaleString('id-ID')}</span>`;
        btn.className = 'px-3 py-1 text-xs border border-primary/60 text-primary rounded-full hover:bg-primary/10 transition';
        btn.onclick = () => {
            document.getElementById('input-jumlah').value = o.val.toLocaleString('id-ID');
        };
        qp.appendChild(btn);
    });

    document.getElementById('modal-hutang').classList.add('open');
    setTimeout(() => document.getElementById('input-jumlah').focus(), 120);
}

function tutupModalBtn() {
    document.getElementById('modal-hutang').classList.remove('open');
    document.getElementById('form-lunasi').reset();
}

function tutupModal(e) {
    if (e && e.currentTarget !== e.target) return;
    tutupModalBtn();
}

/* ---- Validasi sebelum submit ---- */
document.getElementById('form-lunasi').addEventListener('submit', function(e) {
    const raw = document.getElementById('input-jumlah').value.replace(/\./g, '');
    const jml = parseFloat(raw);

    if (!jml || jml <= 0) {
        e.preventDefault();
        alert('Masukkan jumlah pembayaran yang valid.');
        return;
    }
    if (jml > _sisaAktif + 0.01) {          /* toleransi floating point */
        e.preventDefault();
        alert('Jumlah bayar (Rp ' + jml.toLocaleString('id-ID') +
              ') melebihi sisa hutang (Rp ' + _sisaAktif.toLocaleString('id-ID') + ').');
        return;
    }
    /* kirim angka bersih tanpa titik */
    document.getElementById('input-jumlah').value = raw;
});

/* ---- ESC untuk tutup modal ---- */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') tutupModalBtn();
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>
</body>
</html>
