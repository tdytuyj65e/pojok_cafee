<?php
include "../koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan.xls");

$query = mysqli_query($conn,"
SELECT
    t.kode_transaksi,
    u.nama_lengkap,
    t.total,
    t.uang_diterima,
    t.kembalian,
    t.tanggal
FROM transactions t
JOIN users u ON t.user_id = u.id
ORDER BY t.tanggal DESC
");
?>

<table border="1">
    <tr>
        <th>No</th>
        <th>Kode</th>
        <th>Kasir</th>
        <th>Total</th>
        <th>Bayar</th>
        <th>Kembalian</th>
        <th>Tanggal</th>
    </tr>

    <?php
    $no=1;
    while($d=mysqli_fetch_assoc($query)):
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $d['kode_transaksi'] ?></td>
        <td><?= $d['nama_lengkap'] ?></td>
        <td><?= $d['total'] ?></td>
        <td><?= $d['uang_diterima'] ?></td>
        <td><?= $d['kembalian'] ?></td>
        <td><?= $d['tanggal'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>