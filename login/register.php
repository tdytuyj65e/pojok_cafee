<?php
include "../koneksi.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Role otomatis karyawan
    $role_id = 2;

    // Validasi input kosong
    if (empty($nama) || empty($username) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } else {

        // Cek username
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $result = $cek->get_result();

        if ($result->num_rows > 0) {

            $error = "Username sudah digunakan!";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (role_id, nama_lengkap, username, password)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Error Prepare: " . $conn->error);
            }

            $stmt->bind_param(
                "isss",
                $role_id,
                $nama,
                $username,
                $password_hash
            );

            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register | Pojok Kafe</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

body{
    font-family:'Poppins',sans-serif;
    background:#FDF6EE;
}

.wave-container{
    position:relative;
    background:#C8773A;
    height:260px;
    overflow:hidden;
}

.form-card{
    margin-top:-55px;
    position:relative;
    z-index:10;
}

</style>

</head>

<body class="min-h-screen flex flex-col">

<header class="wave-container">

    <div class="flex flex-col items-center pt-8">

        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">

            <span class="text-4xl">☕</span>

        </div>

        <h1 class="text-white text-3xl font-bold mt-3">
            Pojok Kafe
        </h1>

        <p class="text-orange-100 text-sm">
            Sistem Kasir Digital
        </p>

    </div>

    <div class="absolute bottom-0 w-full">

        <svg viewBox="0 0 1440 180">

            <path fill="#FDF6EE"
            d="M0,96L80,101.3C160,107,320,117,480,117.3C640,117,800,107,960,96C1120,85,1280,75,1360,69.3L1440,64L1440,181L1360,181C1280,181,1120,181,960,181C800,181,640,181,480,181C320,181,160,181,80,181L0,181Z">
            </path>

        </svg>

    </div>

</header>

<main class="flex-1 px-4">

    <div class="form-card max-w-md mx-auto">

        <div class="bg-white rounded-3xl shadow-xl p-8">

            <h2 class="text-2xl font-bold text-[#2C1A0E]">
                Registrasi Akun
            </h2>

            <p class="text-gray-500 text-sm mb-6">
                Daftarkan akun karyawan baru
            </p>

            <?php if($success): ?>

            <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded-xl mb-4">
                <?= $success ?>
            </div>

            <?php endif; ?>

            <?php if($error): ?>

            <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-xl mb-4">
                <?= $error ?>
            </div>

            <?php endif; ?>

            <form method="POST">

                <div class="mb-4">

                    <label class="block mb-2 text-sm font-medium">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama"
                        required
                        placeholder="Masukkan nama lengkap"
                        class="w-full border border-orange-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">

                </div>

                <div class="mb-4">

                    <label class="block mb-2 text-sm font-medium">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        required
                        placeholder="Masukkan username"
                        class="w-full border border-orange-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">

                </div>

                <div class="mb-6">

                    <label class="block mb-2 text-sm font-medium">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        placeholder="Masukkan password"
                        class="w-full border border-orange-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-orange-400">

                </div>

                <button
                    type="submit"
                    class="w-full bg-[#C8773A] hover:bg-[#B8692D] text-white font-semibold py-3 rounded-xl transition">

                    DAFTAR

                </button>

            </form>

            <div class="text-center mt-5">

                Sudah punya akun?

                <a href="login.php"
                   class="text-[#C8773A] font-semibold hover:underline">

                    Login

                </a>

            </div>

        </div>

    </div>

</main>

<footer class="text-center py-5 text-sm text-gray-500">
    Pojok Kafe © 2025
</footer>

</body>
</html>
```
