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

$id = (int)$_GET['id'];

/* Ambil foto produk */
$stmt = $conn->prepare("
SELECT foto
FROM products
WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Produk tidak ditemukan";
    header("Location: produk.php");
    exit;
}

$produk = $result->fetch_assoc();

/* Mulai transaksi database */
mysqli_begin_transaction($conn);

try {

    /* Hapus detail transaksi yang memakai produk */
    $hapusDetail = $conn->prepare("
    DELETE FROM transaction_details
    WHERE product_id = ?
    ");

    $hapusDetail->bind_param("i", $id);
    $hapusDetail->execute();

    /* Hapus log stok */
    $hapusLog = $conn->prepare("
    DELETE FROM stock_logs
    WHERE product_id = ?
    ");

    $hapusLog->bind_param("i", $id);
    $hapusLog->execute();

    /* Hapus produk */
    $hapusProduk = $conn->prepare("
    DELETE FROM products
    WHERE id = ?
    ");

    $hapusProduk->bind_param("i", $id);
    $hapusProduk->execute();

    /* Hapus foto */
    if (!empty($produk['foto'])) {

        $file = "../uploads/produk/" . $produk['foto'];

        if (file_exists($file)) {
            unlink($file);
        }
    }

    mysqli_commit($conn);

    $_SESSION['success'] = "Produk berhasil dihapus";

} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['error'] = "Gagal menghapus produk";
}

header("Location: produk.php");
exit;
?>