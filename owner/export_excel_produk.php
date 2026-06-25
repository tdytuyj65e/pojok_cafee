<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: ../auth/login.php");
    exit;
}

/* ==========================
   PARAMETER
========================== */
$tipe    = $_GET['tipe']    ?? 'bulan';
$jenis   = $_GET['jenis']   ?? 'semua';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$bulan   = $_GET['bulan']   ?? date('m');
$tahun   = $_GET['tahun']   ?? date('Y');
$dari    = $_GET['dari']    ?? date('Y-m-01');
$sampai  = $_GET['sampai']  ?? date('Y-m-d');

/* ==========================
   RENTANG TANGGAL
========================== */
switch ($tipe) {
    case 'hari':
        $tgl_dari      = $tanggal;
        $tgl_sampai    = $tanggal;
        $label_periode = "Tanggal_" . date('d-m-Y', strtotime($tanggal));
        $label_tampil  = "Tanggal " . date('d F Y', strtotime($tanggal));
        break;
    case 'tahun':
        $tgl_dari      = "$tahun-01-01";
        $tgl_sampai    = "$tahun-12-31";
        $label_periode = "Tahun_$tahun";
        $label_tampil  = "Tahun $tahun";
        break;
    case 'custom':
        $tgl_dari      = $dari;
        $tgl_sampai    = $sampai;
        $label_periode = date('d-m-Y', strtotime($dari)) . "_sd_" . date('d-m-Y', strtotime($sampai));
        $label_tampil  = date('d F Y', strtotime($dari)) . " s/d " . date('d F Y', strtotime($sampai));
        break;
    default: // bulan
        $tipe          = 'bulan';
        $tgl_dari      = "$tahun-$bulan-01";
        $tgl_sampai    = date('Y-m-t', strtotime("$tahun-$bulan-01"));
        $label_periode = date('F_Y', strtotime("$tahun-$bulan-01"));
        $label_tampil  = date('F Y', strtotime("$tahun-$bulan-01"));
        break;
}

/* ==========================
   FILTER JENIS
========================== */
if ($jenis === 'masuk') {
    $where_jenis  = "AND sl.jenis = 'masuk'";
    $label_jenis  = "Produk Masuk (Restock)";
    $label_file   = "Produk_Masuk";
} elseif ($jenis === 'keluar') {
    $where_jenis  = "AND sl.jenis = 'keluar'";
    $label_jenis  = "Produk Keluar (Terjual)";
    $label_file   = "Produk_Keluar";
} else {
    $where_jenis  = "";
    $label_jenis  = "Semua Pergerakan Stok";
    $label_file   = "Semua_Stok";
}

/* ==========================
   QUERY STOCK LOGS
   Sesuai struktur DB:
   stock_logs(id, product_id, stok_lama, stok_baru,
              keterangan, created_at, jenis, qty)
========================== */
$sql = "
    SELECT
        sl.id                                          AS log_id,
        DATE_FORMAT(sl.created_at,'%d/%m/%Y')         AS tgl,
        DATE_FORMAT(sl.created_at,'%H:%i')            AS jam,
        p.nama_produk,
        COALESCE(c.nama_kategori, '-')                AS nama_kategori,
        sl.jenis,
        sl.qty,
        sl.stok_lama,
        sl.stok_baru,
        p.harga,
        (sl.qty * p.harga)                            AS total_nilai,
        COALESCE(sl.keterangan, '-')                  AS keterangan
    FROM stock_logs sl
    JOIN products p   ON p.id = sl.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE DATE(sl.created_at) BETWEEN ? AND ?
    $where_jenis
    ORDER BY sl.created_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $tgl_dari, $tgl_sampai);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ==========================
   RINGKASAN
========================== */
$total_masuk        = 0;
$total_keluar       = 0;
$nilai_masuk        = 0;
$nilai_keluar       = 0;

foreach ($rows as $r) {
    $q = (int)$r['qty'];
    $n = (float)$r['total_nilai'];
    if ($r['jenis'] === 'masuk') {
        $total_masuk  += $q;
        $nilai_masuk  += $n;
    } else {
        $total_keluar += $q;
        $nilai_keluar += $n;
    }
}

/* ==========================
   HEADER DOWNLOAD
========================== */
$filename = "Laporan_{$label_file}_{$label_periode}.xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
echo "\xEF\xBB\xBF"; // BOM UTF-8
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head><meta charset="UTF-8">
<!--[if gte mso 9]><xml>
<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>Laporan Stok</x:Name>
<x:WorksheetOptions><x:DisplayGridlines/><x:Print><x:FitToPage/></x:Print></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml><![endif]-->
<style>
body,td,th { font-family: Arial, sans-serif; font-size:10pt; }

/* ── Header utama ── */
.h-judul   { font-size:14pt; font-weight:bold; background:#F97316; color:#fff;
             text-align:center; padding:6px; }
.h-sub     { font-size:10pt; background:#FFF7ED; color:#C2410C;
             text-align:center; padding:4px; }
.h-info    { font-size:9pt; background:#F9FAFB; color:#6B7280; padding:3px 6px; }

/* ── Kolom header ── */
.c-hdr     { font-weight:bold; font-size:10pt; background:#F97316; color:#fff;
             text-align:center; border:1px solid #D1D5DB; padding:5px 4px; }

/* ── Data ── */
td         { font-size:9pt; border:1px solid #E5E7EB; padding:3px 5px;
             vertical-align:middle; }
.even      { background:#FFF7ED; }
.odd       { background:#FFFFFF; }

/* ── Jenis badge ── */
.masuk     { background:#DCFCE7; color:#166534; font-weight:bold; text-align:center; }
.keluar    { background:#FEE2E2; color:#991B1B; font-weight:bold; text-align:center; }

/* ── Angka ── */
.num       { text-align:right; }
.ctr       { text-align:center; }

/* ── Ringkasan ── */
.sum-lbl   { font-weight:bold; text-align:right; padding:4px 8px;
             border:1px solid #D1D5DB; }
.sum-masuk { background:#DCFCE7; color:#166534; font-weight:bold; }
.sum-keluar{ background:#FEE2E2; color:#991B1B; font-weight:bold; }
.sum-grand { background:#FED7AA; color:#7C2D12; font-weight:bold; }
.sum-num   { text-align:right; font-weight:bold; border:1px solid #D1D5DB; }
.sum-blank { border:1px solid #D1D5DB; }
</style>
</head>
<body>
<table cellspacing="0" cellpadding="0">

  <!-- ── Judul ── -->
  <tr><td colspan="12" class="h-judul">
    LAPORAN <?= strtoupper($label_jenis) ?> – POJOK KAFE
  </td></tr>
  <tr><td colspan="12" class="h-sub">
    Periode: <?= htmlspecialchars($label_tampil) ?> &nbsp;|&nbsp; Diekspor: <?= date('d/m/Y H:i:s') ?>
  </td></tr>
  <tr>
    <td colspan="6"  class="h-info">Dari&nbsp;&nbsp;&nbsp;: <?= $tgl_dari ?></td>
    <td colspan="6"  class="h-info" style="text-align:right">Sampai: <?= $tgl_sampai ?></td>
  </tr>
  <tr><td colspan="12" style="border:none;height:6px"></td></tr>

  <!-- ── Header Kolom ── -->
  <tr style="height:26px">
    <td class="c-hdr" width="30" >No</td>
    <td class="c-hdr" width="75" >Tanggal</td>
    <td class="c-hdr" width="50" >Jam</td>
    <td class="c-hdr" width="175">Nama Produk</td>
    <td class="c-hdr" width="100">Kategori</td>
    <td class="c-hdr" width="70" >Jenis</td>
    <td class="c-hdr" width="50" >Qty</td>
    <td class="c-hdr" width="70" >Stok Sebelum</td>
    <td class="c-hdr" width="70" >Stok Sesudah</td>
    <td class="c-hdr" width="110">Harga Satuan</td>
    <td class="c-hdr" width="120">Total Nilai</td>
    <td class="c-hdr" width="170">Keterangan</td>
  </tr>

<?php if (empty($rows)): ?>
  <tr>
    <td colspan="12" style="text-align:center;color:#9CA3AF;font-style:italic;padding:20px;border:1px solid #E5E7EB">
      Tidak ada data pergerakan stok pada periode ini.
    </td>
  </tr>
<?php else: ?>

  <?php $no = 1; foreach ($rows as $i => $r):
    $cls     = ($i % 2 === 0) ? 'odd' : 'even';
    $jcls    = ($r['jenis'] === 'masuk') ? 'masuk' : 'keluar';
    $jlabel  = ($r['jenis'] === 'masuk') ? 'Masuk' : 'Keluar';
    $qty     = (int)$r['qty'];
    $harga   = (float)$r['harga'];
    $total   = (float)$r['total_nilai'];
  ?>
  <tr class="<?= $cls ?>">
    <td class="ctr"><?= $no++ ?></td>
    <td class="ctr"><?= htmlspecialchars($r['tgl']) ?></td>
    <td class="ctr"><?= htmlspecialchars($r['jam']) ?></td>
    <td><?= htmlspecialchars($r['nama_produk']) ?></td>
    <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
    <td class="<?= $jcls ?>"><?= $jlabel ?></td>
    <td class="ctr"><?= $qty ?></td>
    <td class="ctr"><?= (int)$r['stok_lama'] ?></td>
    <td class="ctr"><?= (int)$r['stok_baru'] ?></td>
    <td class="num">Rp <?= number_format($harga, 0, ',', '.') ?></td>
    <td class="num">Rp <?= number_format($total, 0, ',', '.') ?></td>
    <td><?= htmlspecialchars($r['keterangan']) ?></td>
  </tr>
  <?php endforeach; ?>

<?php endif; ?>

  <!-- ── Spasi ── -->
  <tr><td colspan="12" style="border:none;height:8px"></td></tr>

  <!-- ── Ringkasan ── -->
<?php if ($jenis === 'masuk' || $jenis === 'semua'): ?>
  <tr class="sum-masuk">
    <td colspan="6" class="sum-lbl sum-masuk">📥 TOTAL PRODUK MASUK (RESTOCK)</td>
    <td class="sum-num sum-masuk ctr"><?= $total_masuk ?></td>
    <td class="sum-blank sum-masuk"></td>
    <td class="sum-blank sum-masuk"></td>
    <td class="sum-blank sum-masuk"></td>
    <td class="sum-num sum-masuk num">Rp <?= number_format($nilai_masuk, 0, ',', '.') ?></td>
    <td class="sum-blank sum-masuk"></td>
  </tr>
<?php endif; ?>

<?php if ($jenis === 'keluar' || $jenis === 'semua'): ?>
  <tr class="sum-keluar">
    <td colspan="6" class="sum-lbl sum-keluar">📤 TOTAL PRODUK KELUAR (TERJUAL)</td>
    <td class="sum-num sum-keluar ctr"><?= $total_keluar ?></td>
    <td class="sum-blank sum-keluar"></td>
    <td class="sum-blank sum-keluar"></td>
    <td class="sum-blank sum-keluar"></td>
    <td class="sum-num sum-keluar num">Rp <?= number_format($nilai_keluar, 0, ',', '.') ?></td>
    <td class="sum-blank sum-keluar"></td>
  </tr>
<?php endif; ?>

<?php if ($jenis === 'semua'): ?>
  <tr class="sum-grand">
    <td colspan="6" class="sum-lbl sum-grand">📊 GRAND TOTAL</td>
    <td class="sum-num sum-grand ctr"><?= $total_masuk + $total_keluar ?></td>
    <td class="sum-blank sum-grand"></td>
    <td class="sum-blank sum-grand"></td>
    <td class="sum-blank sum-grand"></td>
    <td class="sum-num sum-grand num">Rp <?= number_format($nilai_masuk + $nilai_keluar, 0, ',', '.') ?></td>
    <td class="sum-blank sum-grand"></td>
  </tr>
<?php endif; ?>

  <!-- ── Catatan kaki ── -->
  <tr><td colspan="12" style="border:none;height:10px"></td></tr>
  <tr>
    <td colspan="12" style="border:none;font-size:8pt;color:#9CA3AF;font-style:italic">
      * Laporan ini dibuat otomatis oleh sistem Pojok Kafe pada <?= date('d/m/Y H:i:s') ?>.
      Sumber data: tabel stock_logs (restock manual &amp; penyesuaian stok dari transaksi).
    </td>
  </tr>

</table>
</body>
</html>