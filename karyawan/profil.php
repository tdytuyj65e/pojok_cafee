<?php
session_start();
include "koneksi.php";

/* =========================
   CEK LOGIN
========================= */
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

/* =========================
   HANDLE UPDATE PROFIL
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'] ?? '';

    /* FOTO */
    if (!empty($_FILES['foto']['name'])) {
        $foto = time() . "_" . $_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $foto);

        mysqli_query($conn, "UPDATE users SET foto='$foto' WHERE id='$user_id'");
    }

    /* PASSWORD */
    $pass_sql = "";
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pass_sql = ", password='$hash'";
    }

    /* UPDATE DATA */
    mysqli_query($conn, "
        UPDATE users 
        SET nama_lengkap='$nama',
            email='$email'
            $pass_sql
        WHERE id='$user_id'
    ");

    header("Location: profil.php");
    exit;
}

/* =========================
   AMBIL DATA USER
========================= */
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT u.*, r.name as role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = '$user_id'
"));

$foto = !empty($user['foto'])
    ? "uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']);
?>