<?php
include "../koneksi.php";

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
    $filter = "MONTH(tanggal) = MONTH(CURDATE())";
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
   EXPORT CSV (tombol "Excel")
   Tetap memakai hasil query + filter yang sama di atas.
========================= */
if (($_GET['export'] ?? '') === 'excel') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_'.$periode.'_'.date('Ymd_His').'.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Kode Transaksi', 'Tanggal', 'Total']);

    while ($t = mysqli_fetch_assoc($query)) {
        fputcsv($out, [$t['kode_transaksi'], $t['tanggal'], $t['total']]);
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
    $grafik_values[] = $r['total'];
}

/* helper kecil untuk query string filter yang konsisten di semua link */
function pk_filterUrl($periode, $search, $extra = []) {
    $params = array_merge(['periode' => $periode, 'q' => $search], $extra);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
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
              "colors": {
                      "on-tertiary-fixed-variant": "#713700",
                      "tertiary-container": "#a9632c",
                      "inverse-surface": "#402c1f",
                      "on-primary-fixed-variant": "#723600",
                      "surface-container-lowest": "#ffffff",
                      "on-secondary": "#ffffff",
                      "on-secondary-fixed": "#281807",
                      "inverse-on-surface": "#ffede4",
                      "error": "#ba1a1a",
                      "on-secondary-fixed-variant": "#57432e",
                      "surface-dim": "#f5d3c0",
                      "on-primary-fixed": "#311400",
                      "on-primary": "#ffffff",
                      "primary-fixed-dim": "#ffb786",
                      "on-tertiary-container": "#fffbff",
                      "on-surface-variant": "#544339",
                      "on-surface": "#29170c",
                      "secondary-fixed-dim": "#dec1a6",
                      "on-error-container": "#93000a",
                      "tertiary": "#8b4b15",
                      "inverse-primary": "#ffb786",
                      "on-tertiary": "#ffffff",
                      "on-background": "#29170c",
                      "outline-variant": "#d9c2b5",
                      "surface-bright": "#fff8f5",
                      "secondary-container": "#fcddc1",
                      "primary": "#8e4a0e",
                      "surface-container": "#ffeadf",
                      "on-error": "#ffffff",
                      "surface": "#fff8f5",
                      "surface-container-highest": "#fedcc8",
                      "surface-container-high": "#ffe3d3",
                      "secondary-fixed": "#fcddc1",
                      "primary-container": "#ad6126",
                      "tertiary-fixed-dim": "#ffb784",
                      "background": "#fff8f5",
                      "on-tertiary-fixed": "#301400",
                      "error-container": "#ffdad6",
                      "on-secondary-container": "#77604a",
                      "surface-container-low": "#fff1ea",
                      "tertiary-fixed": "#ffdcc5",
                      "on-primary-container": "#fffbff",
                      "outline": "#867368",
                      "surface-variant": "#fedcc8",
                      "secondary": "#705a44",
                      "surface-tint": "#914c10",
                      "primary-fixed": "#ffdcc6"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "xl": "32px",
                      "lg": "24px",
                      "xs": "4px",
                      "md": "16px",
                      "sm": "8px"
              },
              "fontFamily": {
                      "headline-md": ["Plus Jakarta Sans"],
                      "headline-lg": ["Plus Jakarta Sans"],
                      "button-text": ["Plus Jakarta Sans"],
                      "body-md": ["Plus Jakarta Sans"],
                      "label-md": ["Plus Jakarta Sans"]
              },
              "fontSize": {
                      "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                      "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                      "button-text": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                      "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                      "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "500"}]
              }
            },
          },
        }
    </script>
<style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: max(884px, 100dvh);
            background-color: #fff8f5;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .card-shadow {
            box-shadow: 0px 2px 12px rgba(200, 119, 58, 0.12);
        }
        .button-shadow {
            box-shadow: 0px 4px 12px rgba(200, 119, 58, 0.30);
        }

        .filter-chip {
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }
        .filter-chip.active {
            background: #8e4a0e;
            color: #fff;
            border-color: #8e4a0e;
        }

        .bar-fill {
            transition: height .4s ease;
            min-height: 4px;
        }

        .rank-badge {
            width: 22px; height: 22px;
            border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
        }

        table tbody tr:nth-child(even) { background: #fff8f5; }

        @media print {
            #pkActionBar, #pkFilterBar, #pkNavbarSlot, #pkPrintHint { display: none !important; }
            body { background: #fff !important; }
            .card-shadow, .button-shadow { box-shadow: none !important; }
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

        <a href="<?= pk_filterUrl($periode, $search, ['export' => 'excel']) ?>"
            class="px-3 h-9 bg-green-600 text-white rounded-lg flex items-center gap-1.5 text-sm font-medium button-shadow">
            <span class="material-symbols-outlined text-[18px]">file_download</span>
            <span class="hidden sm:inline">Excel</span>
        </a>
    </div>
</div>

<!-- FILTER -->
<div class="space-y-3" id="pkFilterBar">

    <div class="flex gap-2 overflow-x-auto">
        <?php
        $opsiPeriode = [
            'hari'   => 'Hari ini',
            'minggu' => 'Minggu ini',
            'bulan'  => 'Bulan ini',
            'tahun'  => 'Tahun ini',
        ];
        foreach ($opsiPeriode as $key => $label):
            $isActive = $periode == $key;
        ?>
        <a href="<?= pk_filterUrl($key, $search) ?>"
           class="filter-chip <?= $isActive ? 'active' : '' ?> px-4 py-1.5 border border-outline-variant bg-white rounded-full text-sm whitespace-nowrap flex-shrink-0">
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
        <button type="submit" class="h-[42px] px-4 bg-primary text-on-primary rounded-xl text-sm font-medium">
            Cari
        </button>
        <?php if ($search != ''): ?>
        <a href="<?= pk_filterUrl($periode, '') ?>" class="h-[42px] px-3 flex items-center text-sm text-on-surface-variant">
            Reset
        </a>
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
        <?php
        $rankColors = ['bg-amber-400 text-white','bg-gray-300 text-gray-700','bg-orange-300 text-white'];
        foreach ($produk_terlaris as $i => $p):
            $rankClass = $rankColors[$i] ?? 'bg-secondary-container text-primary';
        ?>
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
            <div class="bar-fill w-full rounded-t-md bg-primary"
                 style="height:<?= max((($v/$max)*100), 2) ?>%"></div>
            <small class="text-[11px] text-on-surface-variant"><?= $grafik_labels[$i] ?></small>
        </div>
        <?php endforeach; ?>
    </div>
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
                    <td class="py-2.5 text-on-surface-variant">
                        <?= date('d/m H:i', strtotime($t['tanggal'])) ?>
                    </td>
                    <td class="py-2.5 text-right font-semibold text-primary">
                        Rp <?= number_format($t['total'],0,',','.') ?>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if (!$adaTransaksi): ?>
                <tr>
                    <td colspan="3" class="py-8 text-center text-on-surface-variant">
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

</div>

<div id="pkNavbarSlot">
<?php include "navbar_karyawan.php"; ?>
</div>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>