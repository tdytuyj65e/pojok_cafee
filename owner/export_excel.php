<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ==========================
   FILTER TANGGAL
========================== */

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

/* ==========================
   DATA TRANSAKSI
========================== */

$sql = "
SELECT
    t.id,
    t.kode_transaksi,
    u.nama_lengkap AS kasir,
    t.metode_pembayaran,
    t.total,
    t.uang_diterima,
    t.kembalian,
    t.tanggal
FROM transactions t
JOIN users u ON t.user_id = u.id
WHERE DATE(t.tanggal) BETWEEN ? AND ?
ORDER BY t.tanggal DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dari, $sampai);
$stmt->execute();
$result = $stmt->get_result();

$rows        = [];
$grand_total = 0;
$total_cash  = 0;
$total_qris  = 0;

while ($row = $result->fetch_assoc()) {
    $rows[]      = $row;
    $grand_total += $row['total'];
    if ($row['metode_pembayaran'] === 'cash') $total_cash += $row['total'];
    if ($row['metode_pembayaran'] === 'qris') $total_qris += $row['total'];
}

$jml_trx = count($rows);

/* ==========================
   EXPORT EXCEL (Native HTML→XLS)
   Tidak memerlukan library
   eksternal / Composer
========================== */

$filename = "Laporan_Transaksi_"
          . date('Y-m-d', strtotime($dari))
          . "_sd_"
          . date('Y-m-d', strtotime($sampai))
          . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

/* Fungsi helper */
function rp($n) {
    return number_format($n, 0, ',', '.');
}

function esc($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]>
<xml>
  <x:ExcelWorkbook>
    <x:ExcelWorksheets>
      <x:ExcelWorksheet>
        <x:Name>Laporan Transaksi</x:Name>
        <x:WorksheetOptions>
          <x:DisplayGridlines/>
        </x:WorksheetOptions>
      </x:ExcelWorksheet>
    </x:ExcelWorksheets>
  </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
body {
    font-family: Calibri, Arial, sans-serif;
    font-size: 11pt;
}

/* ── HEADER DOKUMEN ── */
.doc-title {
    font-size: 16pt;
    font-weight: bold;
    background: #5C3D2E;
    color: #FFFFFF;
    text-align: center;
    padding: 10px;
    mso-number-format: "\@";
}

.doc-period {
    font-size: 10pt;
    color: #5C3D2E;
    text-align: center;
    font-style: italic;
    padding: 4px;
    background: #F5EDE8;
    mso-number-format: "\@";
}

.doc-generated {
    font-size: 9pt;
    color: #888;
    text-align: center;
    padding: 2px;
    mso-number-format: "\@";
}

/* ── RINGKASAN ── */
.summary-header {
    font-size: 10pt;
    font-weight: bold;
    background: #7A5240;
    color: #FFFFFF;
    padding: 6px 8px;
    mso-number-format: "\@";
}

.summary-label {
    font-size: 10pt;
    font-weight: bold;
    background: #F5EDE8;
    color: #5C3D2E;
    padding: 5px 8px;
    border: 1px solid #D4C4BC;
    mso-number-format: "\@";
}

.summary-value {
    font-size: 10pt;
    font-weight: bold;
    background: #FFFFFF;
    color: #1C1A17;
    padding: 5px 8px;
    border: 1px solid #D4C4BC;
    mso-number-format: "\@";
}

.summary-value-money {
    font-size: 10pt;
    font-weight: bold;
    background: #FFFFFF;
    color: #1C1A17;
    padding: 5px 8px;
    border: 1px solid #D4C4BC;
    text-align: right;
    mso-number-format: "\@";
}

/* ── TABEL UTAMA ── */
.th {
    background: #5C3D2E;
    color: #FFFFFF;
    font-size: 10pt;
    font-weight: bold;
    border: 1px solid #3D2518;
    padding: 7px 8px;
    text-align: center;
    vertical-align: middle;
    mso-number-format: "\@";
}

.th-r {
    background: #5C3D2E;
    color: #FFFFFF;
    font-size: 10pt;
    font-weight: bold;
    border: 1px solid #3D2518;
    padding: 7px 8px;
    text-align: right;
    vertical-align: middle;
    mso-number-format: "\@";
}

/* baris ganjil */
.td-odd {
    background: #FFFFFF;
    border: 1px solid #D4C4BC;
    padding: 5px 8px;
    font-size: 10pt;
    vertical-align: middle;
    mso-number-format: "\@";
}

.td-odd-c {
    background: #FFFFFF;
    border: 1px solid #D4C4BC;
    padding: 5px 8px;
    font-size: 10pt;
    text-align: center;
    vertical-align: middle;
    mso-number-format: "\@";
}

.td-odd-r {
    background: #FFFFFF;
    border: 1px solid #D4C4BC;
    padding: 5px 8px;
    font-size: 10pt;
    text-align: right;
    vertical-align: middle;
    mso-number-format: "\@";
}

/* baris genap */
.td-even {
    background: #FAF6F3;
    border: 1px solid #D4C4BC;
    padding: 5px 8px;
    font-size: 10pt;
    vertical-align: middle;
    mso-number-format: "\@";
}

.td-even-c {
    background: #FAF6F3;
    border: 1px solid #D4C4BC;
    padding: 5px 8px;
    font-size: 10pt;
    text-align: center;
    vertical-align: middle;
    mso-number-format: "\@";
}

.td-even-r {
    background: #FAF6F3;
    border: 1px solid #D4C4BC;
    padding: 5px 8px;
    font-size: 10pt;
    text-align: right;
    vertical-align: middle;
    mso-number-format: "\@";
}

/* badge metode */
.method-cash {
    background: #D4EDDA;
    color: #155724;
    font-weight: bold;
    border: 1px solid #C3E6CB;
    text-align: center;
    padding: 4px 6px;
    mso-number-format: "\@";
}

.method-qris {
    background: #CCE5FF;
    color: #004085;
    font-weight: bold;
    border: 1px solid #B8DAFF;
    text-align: center;
    padding: 4px 6px;
    mso-number-format: "\@";
}

/* baris total akhir */
.tf-label {
    background: #BA7517;
    color: #FFFFFF;
    font-size: 10pt;
    font-weight: bold;
    border: 1px solid #854F0B;
    padding: 6px 8px;
    mso-number-format: "\@";
}

.tf-value {
    background: #BA7517;
    color: #FFFFFF;
    font-size: 11pt;
    font-weight: bold;
    border: 1px solid #854F0B;
    padding: 6px 8px;
    text-align: right;
    mso-number-format: "\@";
}

.tf-empty {
    background: #BA7517;
    border: 1px solid #854F0B;
    padding: 6px 8px;
    mso-number-format: "\@";
}

/* catatan kaki */
.footer-note {
    font-size: 9pt;
    color: #888888;
    font-style: italic;
    padding: 4px 8px;
    mso-number-format: "\@";
}
</style>
</head>
<body>

<?php /* ── Baris kosong di atas ── */ ?>
<table><tr><td>&nbsp;</td></tr></table>

<?php /* ── JUDUL DOKUMEN ── */ ?>
<table cellspacing="0" cellpadding="0" border="0" width="900">
<tr>
    <td colspan="9" class="doc-title">
        ☕ LAPORAN TRANSAKSI — POJOK KAFE
    </td>
</tr>
<tr>
    <td colspan="9" class="doc-period">
        Periode &nbsp;
        <?= date('d F Y', strtotime($dari)) ?>
        &nbsp;s/d&nbsp;
        <?= date('d F Y', strtotime($sampai)) ?>
    </td>
</tr>
<tr>
    <td colspan="9" class="doc-generated">
        Dicetak pada: <?= date('d-m-Y H:i:s') ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Kasir login: <?= esc($_SESSION['nama'] ?? 'Admin') ?>
    </td>
</tr>
</table>

<table><tr><td>&nbsp;</td></tr></table>

<?php /* ── RINGKASAN EKSEKUTIF ── */ ?>
<table cellspacing="0" cellpadding="0" border="0" width="600">
<tr>
    <td colspan="6" class="summary-header">
        📊 &nbsp;Ringkasan Periode
    </td>
</tr>
<tr>
    <td class="summary-label" width="140">Total Transaksi</td>
    <td class="summary-value" width="100"><?= $jml_trx ?> trx</td>

    <td class="summary-label" width="80">Cash</td>
    <td class="summary-value-money" width="140">Rp <?= rp($total_cash) ?></td>

    <td class="summary-label" width="80">QRIS</td>
    <td class="summary-value-money" width="140">Rp <?= rp($total_qris) ?></td>
</tr>
<tr>
    <td class="summary-label">Grand Total</td>
    <td class="summary-value-money" colspan="5"
        style="font-size:12pt; color:#5C3D2E; background:#FFF8F0; border: 2px solid #BA7517;">
        Rp <?= rp($grand_total) ?>
    </td>
</tr>
</table>

<table><tr><td>&nbsp;</td></tr></table>

<?php /* ── TABEL DETAIL ── */ ?>
<table cellspacing="0" cellpadding="0" border="0" width="900">

<?php /* Header kolom */ ?>
<tr>
    <td class="th" width="35">No</td>
    <td class="th" width="150">Kode Transaksi</td>
    <td class="th" width="120">Kasir</td>
    <td class="th" width="70">Metode</td>
    <td class="th-r" width="120">Total</td>
    <td class="th-r" width="130">Uang Diterima</td>
    <td class="th-r" width="110">Kembalian</td>
    <td class="th" width="90">Tanggal</td>
    <td class="th" width="75">Jam</td>
</tr>

<?php
if ($jml_trx > 0):
    $no = 1;
    foreach ($rows as $r):
        $odd  = ($no % 2 !== 0);
        $base = $odd ? 'td-odd' : 'td-even';
        $basec = $base . '-c';
        $baser = $base . '-r';
        $method_class = ($r['metode_pembayaran'] === 'cash') ? 'method-cash' : 'method-qris';
?>
<tr>
    <td class="<?= $basec ?>"><?= $no++ ?></td>
    <td class="<?= $base ?>" style="font-family: Courier New, monospace; font-size:9pt;">
        <?= esc($r['kode_transaksi']) ?>
    </td>
    <td class="<?= $base ?>" style="font-weight:bold;">
        <?= esc($r['kasir']) ?>
    </td>
    <td class="<?= $method_class ?>">
        <?= strtoupper(esc($r['metode_pembayaran'])) ?>
    </td>
    <td class="<?= $baser ?>" style="font-weight:bold;">
        <?= rp($r['total']) ?>
    </td>
    <td class="<?= $baser ?>">
        <?= rp($r['uang_diterima']) ?>
    </td>
    <td class="<?= $baser ?>">
        <?= rp($r['kembalian']) ?>
    </td>
    <td class="<?= $basec ?>">
        <?= date('d-m-Y', strtotime($r['tanggal'])) ?>
    </td>
    <td class="<?= $basec ?>">
        <?= date('H:i:s', strtotime($r['tanggal'])) ?>
    </td>
</tr>
<?php
    endforeach;
else:
?>
<tr>
    <td colspan="9" class="td-odd-c" style="padding: 20px; color: #888; font-style: italic;">
        Tidak ada transaksi pada periode ini.
    </td>
</tr>
<?php endif; ?>

<?php /* Baris total */ ?>
<tr>
    <td class="tf-label" colspan="4">
        TOTAL &mdash; <?= $jml_trx ?> TRANSAKSI
    </td>
    <td class="tf-value">
        <?= rp($grand_total) ?>
    </td>
    <td class="tf-empty" colspan="4"></td>
</tr>

</table>

<table><tr><td>&nbsp;</td></tr></table>

<?php /* ── CATATAN KAKI ── */ ?>
<table width="900">
<tr>
    <td class="footer-note">
        * Dokumen ini dibuat otomatis oleh sistem POS Pojok Kafe.
        Jangan ubah data pada file ini secara manual.
    </td>
</tr>
<tr>
    <td class="footer-note">
        * Cash: Rp <?= rp($total_cash) ?>
        &nbsp;|&nbsp;
        QRIS: Rp <?= rp($total_qris) ?>
        &nbsp;|&nbsp;
        Grand Total: Rp <?= rp($grand_total) ?>
    </td>
</tr>
</table>

</body>
</html>