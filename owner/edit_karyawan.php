<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: ../auth/login.php");
    exit;
}

$id       = (int)$_POST['id'];
$nama     = trim($_POST['nama_lengkap']);
$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$status   = $_POST['status'];
$password = trim($_POST['password']);

// Ambil data lama
$lama = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE id = $id LIMIT 1"
));

if (!$lama) {
    $_SESSION['error'] = "Karyawan tidak ditemukan.";
    header("Location: karyawan.php");
    exit;
}

// Handle upload foto
$foto = $lama['foto']; // default: foto lama
if (!empty($_FILES['foto']['name'])) {
    $ext      = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $namaFile = 'karyawan_' . $id . '_' . time() . '.' . $ext;
    $tujuan   = "../uploads/" . $namaFile;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        // Hapus foto lama jika ada
        if ($lama['foto'] && file_exists("../uploads/" . $lama['foto'])) {
            unlink("../uploads/" . $lama['foto']);
        }
        $foto = $namaFile;
    }
}

// Update dengan atau tanpa password
if ($password !== '') {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        UPDATE users
        SET nama_lengkap=?, username=?, email=?, password=?, status=?, foto=?
        WHERE id=?
    ");
    $stmt->bind_param("ssssssi", $nama, $username, $email, $hash, $status, $foto, $id);
} else {
    $stmt = $conn->prepare("
        UPDATE users
        SET nama_lengkap=?, username=?, email=?, status=?, foto=?
        WHERE id=?
    ");
    $stmt->bind_param("sssssi", $nama, $username, $email, $status, $foto, $id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Data karyawan berhasil diperbarui.";
} else {
    $_SESSION['error'] = "Gagal memperbarui data: " . $conn->error;
}

header("Location: karyawan.php");
exit;