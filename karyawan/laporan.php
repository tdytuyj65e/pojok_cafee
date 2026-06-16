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
    $filter .= " AND kode_transaksi LIKE '%$search%'";
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
</script>

<title>Laporan - Pojok Kafe</title>

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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .card-shadow {
            box-shadow: 0px 2px 12px rgba(200, 119, 58, 0.12);
        }
        .button-shadow {
            box-shadow: 0px 4px 12px rgba(200, 119, 58, 0.30);
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
</head>

<body>

<div class="max-w-5xl mx-auto px-4 py-6 space-y-6">

<!-- HEADER -->
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-xl font-bold">Laporan Penjualan</h1>
        <p class="text-sm text-gray-500">Data transaksi</p>
    </div>

    <div class="flex gap-2">
        <a href="?export=pdf" class="px-3 py-1 bg-red-500 text-white rounded"></a>
        <a href="?export=excel" class="px-3 py-1 bg-green-600 text-white rounded"></a>
    </div>
</div>

<!-- FILTER -->
<div class="flex gap-2">
    <a href="?periode=hari" class="px-3 py-1 border rounded <?= $periode=='hari'?'active':'' ?>">Hari</a>
    <a href="?periode=minggu" class="px-3 py-1 border rounded <?= $periode=='minggu'?'active':'' ?>">Minggu</a>
    <a href="?periode=bulan" class="px-3 py-1 border rounded <?= $periode=='bulan'?'active':'' ?>">Bulan</a>
    <a href="?periode=tahun" class="px-3 py-1 border rounded <?= $periode=='tahun'?'active':'' ?>">Tahun</a>
</div>

<!-- RINGKASAN -->
<div class="grid grid-cols-3 gap-4">

    <div class="bg-white p-4 rounded shadow">
        <p>Total Pendapatan</p>
        <b>Rp <?= number_format($ringkasan['pendapatan'],0,',','.') ?></b>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p>Total Transaksi</p>
        <b><?= $ringkasan['total_transaksi'] ?></b>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <p>Rata-rata</p>
        <b>Rp <?= number_format($ringkasan['rata_rata'],0,',','.') ?></b>
    </div>

</div>

<!-- PRODUK TERLARIS -->
<div class="bg-white p-4 rounded shadow">
<h2 class="font-bold mb-3">Produk Terlaris</h2>

<?php foreach ($produk_terlaris as $p): ?>
<div class="flex justify-between border-b py-1 text-sm">
    <span><?= $p['nama_produk'] ?></span>
    <span><?= $p['qty'] ?> pcs</span>
    <b>Rp <?= number_format($p['pendapatan'],0,',','.') ?></b>
</div>
<?php endforeach; ?>

</div>

<!-- GRAFIK SEDERHANA -->
<div class="bg-white p-4 rounded shadow">
<h2 class="font-bold mb-3">Grafik 7 Hari</h2>

<div class="flex items-end gap-2 h-32">
<?php
$max = max($grafik_values) ?: 1;
foreach ($grafik_values as $i => $v):
?>
    <div class="flex-1 flex flex-col items-center">
        <div style="height:<?= ($v/$max)*100 ?>px;background:#8e4a0e"
             class="w-full rounded-t"></div>
        <small><?= $grafik_labels[$i] ?></small>
    </div>
<?php endforeach; ?>
</div>

</div>

<!-- TRANSAKSI -->
<div class="bg-white p-4 rounded shadow">
<h2 class="font-bold mb-3">Riwayat Transaksi</h2>

<table class="w-full text-sm">
<tr class="border-b">
    <th>Kode</th>
    <th>Waktu</th>
    <th>Total</th>
</tr>

<?php while ($t = mysqli_fetch_assoc($query)): ?>
<tr class="border-b">
    <td><?= $t['kode_transaksi'] ?></td>
    <td><?= date('H:i', strtotime($t['tanggal'])) ?></td>
    <td>Rp <?= number_format($t['total'],0,',','.') ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>

</div>
<?php include "navbar_karyawan.php"; ?>
</body>
</html>