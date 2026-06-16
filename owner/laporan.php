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

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$query = mysqli_query($conn,"
SELECT
t.*,
u.nama_lengkap
FROM transactions t
JOIN users u ON t.user_id=u.id
WHERE DATE(t.tanggal)
BETWEEN '$dari' AND '$sampai'
ORDER BY t.id DESC
");

$total_penjualan = 0;
$total_transaksi = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Laporan Penjualan</title>

<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">

<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

<div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 text-white">

<h1 class="text-3xl font-bold">
Laporan Penjualan
</h1>

<p>
Download PDF & Excel
</p>

</div>

<div class="p-6">

<form method="GET"
class="bg-white p-5 rounded-3xl shadow mb-6">

<div class="grid md:grid-cols-4 gap-4">

<div>
<label>Dari</label>
<input
type="date"
name="dari"
value="<?= $dari ?>"
class="w-full border rounded-xl p-3">
</div>

<div>
<label>Sampai</label>
<input
type="date"
name="sampai"
value="<?= $sampai ?>"
class="w-full border rounded-xl p-3">
</div>

<div class="flex items-end">
<button
class="bg-orange-500 text-white px-6 py-3 rounded-xl w-full">
Filter
</button>
</div>

<div class="flex items-end gap-2">

<a
href="export_pdf.php?dari=<?= $dari ?>&sampai=<?= $sampai ?>"
class="bg-red-500 text-white px-4 py-3 rounded-xl">

PDF
</a>

<a
href="export_excel.php?dari=<?= $dari ?>&sampai=<?= $sampai ?>"
class="bg-green-500 text-white px-4 py-3 rounded-xl">

Excel
</a>

</div>

</div>

</form>

<div class="bg-white rounded-3xl shadow overflow-hidden">

<table class="w-full">

<thead class="bg-orange-500 text-white">

<tr>
<th class="p-3">Kode</th>
<th class="p-3">Kasir</th>
<th class="p-3">Tanggal</th>
<th class="p-3">Total</th>
</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($query)): ?>

<?php $total_penjualan += $row['total']; ?>

<tr class="border-b">

<td class="p-3">
<?= $row['kode_transaksi'] ?>
</td>

<td class="p-3">
<?= $row['nama_lengkap'] ?>
</td>

<td class="p-3">
<?= date('d/m/Y H:i',strtotime($row['tanggal'])) ?>
</td>

<td class="p-3 text-green-600 font-bold">
Rp <?= number_format($row['total'],0,',','.') ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

<div class="mt-5 bg-white p-5 rounded-3xl shadow">

<p>
Total Transaksi :
<b><?= $total_transaksi ?></b>
</p>

<p>
Total Penjualan :
<b class="text-green-600">
Rp <?= number_format($total_penjualan,0,',','.') ?>
</b>
</p>

</div>

</div>

</div>

</body>
</html>