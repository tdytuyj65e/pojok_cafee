<?php
include "../koneksi.php";
require('../assets/fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Laporan Penjualan Pojok Kafe',0,1,'C');

$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(10,10,'No',1);
$pdf->Cell(30,10,'Kode',1);
$pdf->Cell(40,10,'Kasir',1);
$pdf->Cell(35,10,'Total',1);
$pdf->Cell(65,10,'Tanggal',1);

$pdf->Ln();

$query = mysqli_query($conn,"
SELECT
    t.*,
    u.nama_lengkap
FROM transactions t
JOIN users u ON t.user_id=u.id
ORDER BY t.tanggal DESC
");

$no = 1;

$pdf->SetFont('Arial','',10);

while($d = mysqli_fetch_assoc($query))
{
    $pdf->Cell(10,10,$no++,1);
    $pdf->Cell(30,10,$d['kode_transaksi'],1);
    $pdf->Cell(40,10,$d['nama_lengkap'],1);
    $pdf->Cell(35,10,'Rp '.number_format($d['total']),1);
    $pdf->Cell(65,10,$d['tanggal'],1);
    $pdf->Ln();
}

$pdf->Output('D','Laporan_Penjualan.pdf');