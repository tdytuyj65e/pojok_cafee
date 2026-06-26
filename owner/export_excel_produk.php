<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: ../auth/login.php");
    exit;
}

/* ==========================
   HELPER
========================== */
function isValidDate(string $d): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}
function rp($n)  { return number_format($n, 0, ',', '.'); }
function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$validation_warnings = [];

/* ==========================
   PARAMETER — sama persis
   dengan export_excel.php
   cukup dari & sampai saja
========================== */
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$jenis  = $_GET['jenis']  ?? 'semua';   // opsional, default semua

/* ==========================
   VALIDASI
========================== */
$jenis = in_array($jenis, ['masuk','keluar','semua']) ? $jenis : 'semua';

if (!isValidDate($dari)) {
    $dari = date('Y-m-01');
    $validation_warnings[] = 'Parameter "dari" tidak valid — direset ke awal bulan ini.';
}
if (!isValidDate($sampai)) {
    $sampai = date('Y-m-d');
    $validation_warnings[] = 'Parameter "sampai" tidak valid — direset ke hari ini.';
}
if ($dari > $sampai) {
    [$dari, $sampai] = [$sampai, $dari];
    $validation_warnings[] = '"Dari" lebih besar dari "Sampai" — urutan dibalik otomatis.';
}
$diff = (new DateTime($dari))->diff(new DateTime($sampai))->days;
if ($diff > 366) {
    $sampai = (new DateTime($dari))->modify('+366 days')->format('Y-m-d');
    $validation_warnings[] = 'Rentang melebihi 1 tahun — tanggal "sampai" dipotong otomatis.';
}

/* ==========================
   LABEL
========================== */
$label_tampil  = date('d F Y', strtotime($dari)) . ' s/d ' . date('d F Y', strtotime($sampai));
$label_periode = date('d-m-Y', strtotime($dari)) . '_sd_' . date('d-m-Y', strtotime($sampai));

if ($jenis === 'masuk') {
    $where_jenis = "AND sl.jenis = 'masuk'";
    $label_jenis = "Produk Masuk (Restock)";
    $label_file  = "Produk_Masuk";
} elseif ($jenis === 'keluar') {
    $where_jenis = "AND sl.jenis = 'keluar'";
    $label_jenis = "Produk Keluar (Terjual)";
    $label_file  = "Produk_Keluar";
} else {
    $where_jenis = "";
    $label_jenis = "Semua Pergerakan Stok";
    $label_file  = "Semua_Stok";
}

/* ==========================
   QUERY
========================== */
$sql = "
    SELECT
        DATE_FORMAT(sl.created_at, '%d/%m/%Y') AS tgl,
        DATE_FORMAT(sl.created_at, '%H:%i')    AS jam,
        p.nama_produk,
        COALESCE(c.nama_kategori, '-')         AS nama_kategori,
        sl.jenis,
        sl.qty,
        sl.stok_lama,
        sl.stok_baru,
        p.harga,
        (sl.qty * p.harga)                     AS total_nilai,
        COALESCE(sl.keterangan, '-')           AS keterangan
    FROM stock_logs sl
    JOIN products p        ON p.id = sl.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE DATE(sl.created_at) BETWEEN ? AND ?
    $where_jenis
    ORDER BY sl.created_at ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dari, $sampai);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ==========================
   RINGKASAN
========================== */
$total_masuk  = 0; $total_keluar = 0;
$nilai_masuk  = 0; $nilai_keluar = 0;
foreach ($rows as $r) {
    if ($r['jenis'] === 'masuk') { $total_masuk  += $r['qty']; $nilai_masuk  += $r['total_nilai']; }
    else                         { $total_keluar += $r['qty']; $nilai_keluar += $r['total_nilai']; }
}
$jml_log = count($rows);

/* ==========================
   HEADER DOWNLOAD
========================== */
$filename = "Laporan_{$label_file}_{$label_periode}.xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF";
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]><xml>
<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
  <x:Name>Laporan Stok</x:Name>
  <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml><![endif]-->
<style>
body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
.doc-title    { font-size:16pt; font-weight:bold; background:#F97316; color:#fff; text-align:center; padding:10px; mso-number-format:"\@"; }
.doc-period   { font-size:10pt; color:#C2410C; text-align:center; font-style:italic; padding:4px; background:#FFF7ED; mso-number-format:"\@"; }
.doc-generated{ font-size:9pt; color:#888; text-align:center; padding:2px; mso-number-format:"\@"; }
.warn-row     { font-size:9pt; font-weight:bold; background:#FEF3C7; color:#92400E; padding:5px 8px; border:1px solid #FCD34D; mso-number-format:"\@"; }
.sum-header   { font-size:10pt; font-weight:bold; background:#EA580C; color:#fff; padding:6px 8px; mso-number-format:"\@"; }
.sum-lbl      { font-size:10pt; font-weight:bold; background:#FFF7ED; color:#C2410C; padding:5px 8px; border:1px solid #FED7AA; mso-number-format:"\@"; }
.sum-val      { font-size:10pt; font-weight:bold; background:#fff; color:#1C1A17; padding:5px 8px; border:1px solid #FED7AA; mso-number-format:"\@"; }
.sum-val-r    { font-size:10pt; font-weight:bold; background:#fff; color:#1C1A17; padding:5px 8px; border:1px solid #FED7AA; text-align:right; mso-number-format:"\@"; }
.sum-grand    { font-size:11pt; font-weight:bold; background:#FFF8F0; color:#C2410C; padding:5px 8px; border:2px solid #F97316; text-align:right; mso-number-format:"\@"; }
.th           { background:#F97316; color:#fff; font-size:10pt; font-weight:bold; border:1px solid #C2410C; padding:7px 8px; text-align:center; vertical-align:middle; mso-number-format:"\@"; }
.th-r         { background:#F97316; color:#fff; font-size:10pt; font-weight:bold; border:1px solid #C2410C; padding:7px 8px; text-align:right; vertical-align:middle; mso-number-format:"\@"; }
.td-o    { background:#fff;    border:1px solid #FED7AA; padding:5px 8px; font-size:10pt; vertical-align:middle; mso-number-format:"\@"; }
.td-o-c  { background:#fff;    border:1px solid #FED7AA; padding:5px 8px; font-size:10pt; text-align:center; vertical-align:middle; mso-number-format:"\@"; }
.td-o-r  { background:#fff;    border:1px solid #FED7AA; padding:5px 8px; font-size:10pt; text-align:right; vertical-align:middle; mso-number-format:"\@"; }
.td-e    { background:#FFF7ED; border:1px solid #FED7AA; padding:5px 8px; font-size:10pt; vertical-align:middle; mso-number-format:"\@"; }
.td-e-c  { background:#FFF7ED; border:1px solid #FED7AA; padding:5px 8px; font-size:10pt; text-align:center; vertical-align:middle; mso-number-format:"\@"; }
.td-e-r  { background:#FFF7ED; border:1px solid #FED7AA; padding:5px 8px; font-size:10pt; text-align:right; vertical-align:middle; mso-number-format:"\@"; }
.b-masuk  { background:#DCFCE7; color:#166534; font-weight:bold; border:1px solid #BBF7D0; text-align:center; padding:4px 6px; mso-number-format:"\@"; }
.b-keluar { background:#FEE2E2; color:#991B1B; font-weight:bold; border:1px solid #FECACA; text-align:center; padding:4px 6px; mso-number-format:"\@"; }
.tf-lbl   { background:#EA580C; color:#fff; font-size:10pt; font-weight:bold; border:1px solid #C2410C; padding:6px 8px; mso-number-format:"\@"; }
.tf-val   { background:#EA580C; color:#fff; font-size:11pt; font-weight:bold; border:1px solid #C2410C; padding:6px 8px; text-align:right; mso-number-format:"\@"; }
.tf-emp   { background:#EA580C; border:1px solid #C2410C; padding:6px 8px; mso-number-format:"\@"; }
.footer-note { font-size:9pt; color:#888; font-style:italic; padding:4px 8px; mso-number-format:"\@"; }
</style>
</head>
<body>

<table><tr><td>&nbsp;</td></tr></table>

<!-- JUDUL -->
<table cellspacing="0" cellpadding="0" border="0" width="950">
<tr><td colspan="12" class="doc-title">📦 LAPORAN <?= strtoupper($label_jenis) ?> — POJOK KAFE</td></tr>
<tr><td colspan="12" class="doc-period">Periode &nbsp;<?= esc($label_tampil) ?></td></tr>
<tr><td colspan="12" class="doc-generated">
  Dicetak pada: <?= date('d-m-Y H:i:s') ?> &nbsp;|&nbsp; Admin: <?= esc($_SESSION['nama'] ?? 'Admin') ?>
</td></tr>
</table>

<!-- WARNING -->
<?php if (!empty($validation_warnings)): ?>
<table><tr><td>&nbsp;</td></tr></table>
<table cellspacing="0" cellpadding="0" border="0" width="950">
<?php foreach ($validation_warnings as $w): ?>
<tr><td colspan="12" class="warn-row">⚠️ <?= esc($w) ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<table><tr><td>&nbsp;</td></tr></table>

<!-- RINGKASAN -->
<table cellspacing="0" cellpadding="0" border="0" width="650">
<tr><td colspan="6" class="sum-header">📊 &nbsp;Ringkasan Periode</td></tr>
<tr>
  <td class="sum-lbl" width="140">Total Pergerakan</td>
  <td class="sum-val" width="100"><?= $jml_log ?> log</td>
  <td class="sum-lbl" width="80">📥 Masuk</td>
  <td class="sum-val" width="80"><?= $total_masuk ?> unit</td>
  <td class="sum-lbl" width="80">📤 Keluar</td>
  <td class="sum-val" width="80"><?= $total_keluar ?> unit</td>
</tr>
<tr>
  <td class="sum-lbl">Nilai Masuk</td>
  <td class="sum-val-r" colspan="2">Rp <?= rp($nilai_masuk) ?></td>
  <td class="sum-lbl">Nilai Keluar</td>
  <td class="sum-val-r" colspan="2">Rp <?= rp($nilai_keluar) ?></td>
</tr>
<tr>
  <td class="sum-lbl">Grand Total Nilai</td>
  <td class="sum-grand" colspan="5">Rp <?= rp($nilai_masuk + $nilai_keluar) ?></td>
</tr>
</table>

<table><tr><td>&nbsp;</td></tr></table>

<!-- TABEL DETAIL -->
<table cellspacing="0" cellpadding="0" border="0" width="950">
<tr>
  <td class="th"  width="35" >No</td>
  <td class="th"  width="80" >Tanggal</td>
  <td class="th"  width="50" >Jam</td>
  <td class="th"  width="180">Nama Produk</td>
  <td class="th"  width="100">Kategori</td>
  <td class="th"  width="70" >Jenis</td>
  <td class="th"  width="50" >Qty</td>
  <td class="th"  width="65" >Stok Sebelum</td>
  <td class="th"  width="65" >Stok Sesudah</td>
  <td class="th-r" width="110">Harga Satuan</td>
  <td class="th-r" width="120">Total Nilai</td>
  <td class="th"  width="170">Keterangan</td>
</tr>

<?php if ($jml_log > 0):
  $no = 1;
  foreach ($rows as $r):
    $ganjil = ($no % 2 !== 0);
    $b  = $ganjil ? 'td-o'   : 'td-e';
    $bc = $ganjil ? 'td-o-c' : 'td-e-c';
    $br = $ganjil ? 'td-o-r' : 'td-e-r';
    $jcls = ($r['jenis'] === 'masuk') ? 'b-masuk' : 'b-keluar';
    $jlbl = ($r['jenis'] === 'masuk') ? '📥 Masuk' : '📤 Keluar';
?>
<tr>
  <td class="<?= $bc ?>"><?= $no++ ?></td>
  <td class="<?= $bc ?>"><?= esc($r['tgl']) ?></td>
  <td class="<?= $bc ?>"><?= esc($r['jam']) ?></td>
  <td class="<?= $b  ?>" style="font-weight:bold;"><?= esc($r['nama_produk']) ?></td>
  <td class="<?= $b  ?>"><?= esc($r['nama_kategori']) ?></td>
  <td class="<?= $jcls ?>"><?= $jlbl ?></td>
  <td class="<?= $bc ?>"><?= (int)$r['qty'] ?></td>
  <td class="<?= $bc ?>"><?= (int)$r['stok_lama'] ?></td>
  <td class="<?= $bc ?>"><?= (int)$r['stok_baru'] ?></td>
  <td class="<?= $br ?>">Rp <?= rp($r['harga']) ?></td>
  <td class="<?= $br ?>" style="font-weight:bold;">Rp <?= rp($r['total_nilai']) ?></td>
  <td class="<?= $b  ?>"><?= esc($r['keterangan']) ?></td>
</tr>
<?php endforeach;
else: ?>
<tr>
  <td colspan="12" class="td-o-c" style="padding:20px; color:#888; font-style:italic;">
    Tidak ada data pergerakan stok pada periode ini.
  </td>
</tr>
<?php endif; ?>

<tr>
  <td class="tf-lbl" colspan="6">TOTAL — <?= $jml_log ?> LOG</td>
  <td class="tf-val"><?= $total_masuk + $total_keluar ?></td>
  <td class="tf-emp" colspan="3"></td>
  <td class="tf-val">Rp <?= rp($nilai_masuk + $nilai_keluar) ?></td>
  <td class="tf-emp"></td>
</tr>
</table>

<table><tr><td>&nbsp;</td></tr></table>

<!-- CATATAN KAKI -->
<table width="950">
<tr><td class="footer-note">
  * Laporan ini dibuat otomatis oleh sistem Pojok Kafe pada <?= date('d/m/Y H:i:s') ?>.
  Sumber data: tabel stock_logs (restock manual &amp; penyesuaian stok dari transaksi).
</td></tr>
<tr><td class="footer-note">
  * Masuk: <?= $total_masuk ?> unit (Rp <?= rp($nilai_masuk) ?>)
  &nbsp;|&nbsp; Keluar: <?= $total_keluar ?> unit (Rp <?= rp($nilai_keluar) ?>)
  &nbsp;|&nbsp; Grand Total: Rp <?= rp($nilai_masuk + $nilai_keluar) ?>
</td></tr>
</table>

</body>
</html>