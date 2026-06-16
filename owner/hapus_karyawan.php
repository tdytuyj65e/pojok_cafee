<?php
session_start();
include "../koneksi.php";

/* =========================
   CEK LOGIN
========================= */
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   CEK ROLE OWNER
========================= */
if ((int)$_SESSION['role_id'] !== 1) {
    header("Location: ../karyawan/dashboard.php");
    exit;
}

/* =========================
   CEK ID KARYAWAN
========================= */
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: karyawan.php");
    exit;
}

/* =========================
   CEK DATA KARYAWAN
========================= */
$stmt = mysqli_prepare($conn, "
    SELECT id FROM users
    WHERE id = ? AND role_id = 2
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: karyawan.php");
    exit;
}

/* =========================
   HAPUS KARYAWAN
========================= */
$stmtDel = mysqli_prepare($conn, "
    DELETE FROM users WHERE id = ? AND role_id = 2
");

mysqli_stmt_bind_param($stmtDel, "i", $id);
mysqli_stmt_execute($stmtDel);

/* =========================
   REDIRECT
========================= */
header("Location: karyawan.php?hapus=success");
exit;
?>