<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: ../auth/login.php");
    exit;
}

$nama     = trim($_POST['nama_lengkap'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$status   = $_POST['status'] ?? 'aktif';
$foto     = '';

/* ==========================
   VALIDASI
========================== */

if (empty($nama) || empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = "Semua field wajib diisi!";
    header("Location: karyawan.php");
    exit;
}

// Cek username sudah dipakai
$cekUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$cekUser->bind_param("s", $username);
$cekUser->execute();
$cekUser->store_result();

if ($cekUser->num_rows > 0) {
    $_SESSION['error'] = "Username \"$username\" sudah digunakan, pilih username lain.";
    header("Location: karyawan.php");
    exit;
}

// Cek email sudah dipakai
$cekEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$cekEmail->bind_param("s", $email);
$cekEmail->execute();
$cekEmail->store_result();

if ($cekEmail->num_rows > 0) {
    $_SESSION['error'] = "Email \"$email\" sudah terdaftar.";
    header("Location: karyawan.php");
    exit;
}

/* ==========================
   UPLOAD FOTO
========================== */

if (!empty($_FILES['foto']['name'])) {

    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
    $ext         = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $maxSize     = 2 * 1024 * 1024; // 2 MB

    if (!in_array($ext, $allowedExt)) {
        $_SESSION['error'] = "Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.";
        header("Location: karyawan.php");
        exit;
    }

    if ($_FILES['foto']['size'] > $maxSize) {
        $_SESSION['error'] = "Ukuran foto maksimal 2 MB.";
        header("Location: karyawan.php");
        exit;
    }

    $namaFile = uniqid('karyawan_') . '.' . $ext;
    $tujuan   = "../uploads/" . $namaFile;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        $_SESSION['error'] = "Gagal mengupload foto.";
        header("Location: karyawan.php");
        exit;
    }

    $foto = $namaFile;
}

/* ==========================
   SIMPAN KE DATABASE
========================== */

$hashPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (nama_lengkap, username, email, password, foto, status, role_id)
    VALUES (?, ?, ?, ?, ?, ?, 2)
");
$stmt->bind_param("ssssss", $nama, $username, $email, $hashPassword, $foto, $status);

if ($stmt->execute()) {
    $_SESSION['success'] = "Karyawan \"$nama\" berhasil ditambahkan!";
} else {
    $_SESSION['error'] = "Gagal menyimpan data karyawan. Silakan coba lagi.";
}

header("Location: karyawan.php");
exit;