<?php
session_start();
include "../koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT users.*, roles.name AS role_name
            FROM users
            JOIN roles ON users.role_id = roles.id
            WHERE users.username = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {

        if (password_verify($password, $user['password'])) {

            if ($user['status'] == 'nonaktif') {
                $error = "Akun tidak aktif!";
            } else {

                // ================= SESSION FIX (PAKAI ROLE_ID SAJA) =================
                $_SESSION['id'] = $user['id'];
                $_SESSION['nama'] = $user['nama_lengkap'];
                $_SESSION['role_id'] = $user['role_id'];

                // ================= REDIRECT =================
                if ($user['role_id'] == 1) {
                    header("Location: ../owner/dashboard.php");
                } else {
                    header("Location: ../karyawan/dashboard.php");
                }
                exit();
            }

        } else {
            $error = "Password salah!";
        }

    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Pojok Kafe</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:#FDF6EE;
}
</style>
</head>
<body class="min-h-screen">

<!-- Header -->
<div class="bg-[#D97732] h-72 relative">

    <div class="flex flex-col items-center pt-10">

        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-xl">
            <span class="text-5xl">☕</span>
        </div>

        <h1 class="text-white text-4xl font-bold mt-4">
            Pojok Kafe
        </h1>

        <p class="text-orange-100">
            Sistem Kasir Digital
        </p>

    </div>

</div>

<!-- Login Card -->
<div class="max-w-md mx-auto px-4 -mt-16 relative z-10">

    <div class="bg-white rounded-3xl shadow-xl p-8">

        <h2 class="text-3xl font-bold text-gray-800">
            Selamat Datang 👋
        </h2>

        <p class="text-gray-500 mt-1 mb-6">
            Masuk untuk melanjutkan sistem kasir.
        </p>

        <?php if(!empty($error)): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-xl mb-4">
            <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-4">
                <label class="block mb-2 font-medium text-gray-700">
                    Username
                </label>

                <input type="text" name="username" required
                    class="w-full p-3 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-medium text-gray-700">
                    Password
                </label>

                <input type="password" name="password" required
                    class="w-full p-3 border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-500">
            </div>

            <button type="submit"
                class="w-full bg-[#D97732] hover:bg-[#C96A28] text-white py-3 rounded-xl font-semibold">

                MASUK

            </button>

        </form>

    </div>

</div>

<footer class="text-center py-8 text-gray-500 text-sm">
    Pojok Kafe © 2025
</footer>

</body>
</html>