<?php
session_start();
include "../koneksi.php";

/* =========================
   CEK LOGIN
========================= */
$user_id = $_SESSION['id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

/* =========================
   HANDLE UPDATE PROFIL
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'] ?? '';

    /* FOTO */
    if (!empty($_FILES['foto']['name'])) {

        $uploadDir = "../uploads/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {

            $namaFile = "user_" . $user_id . "_" . time() . "." . $ext;

            if (move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                $uploadDir . $namaFile
            )) {

                mysqli_query(
                    $conn,
                    "UPDATE users SET foto='$namaFile' WHERE id='$user_id'"
                );
            }
        }
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

$foto = !empty($user['foto']) &&
        file_exists("../uploads/" . $user['foto'])
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Pengguna</title>

<script src="https://cdn.tailwindcss.com">
</script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>
body{
    font-family:'Poppins',sans-serif;
}
</style>
</head>
<body class="bg-slate-50 min-h-screen pb-24">

<!-- Header -->
<div class="bg-gradient-to-r from-orange-600 to-orange-500 h-56 rounded-b-[40px] shadow-lg">

    <div class="max-w-md mx-auto px-5 pt-8 text-white">

        <h1 class="text-3xl font-bold">
            Profil Saya
        </h1>

        <p class="text-orange-100 mt-1">
            Kelola informasi akun Anda
        </p>

    </div>

</div>

<!-- Card Profil -->
<div class="max-w-md mx-auto px-4 -mt-20">

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden">

        <!-- Foto -->
        <div class="flex flex-col items-center pt-6">

            <div class="relative">

                <img
                    src="<?= $foto ?>"
                    class="w-32 h-32 rounded-full object-cover border-[6px] border-orange-100 shadow-lg"
                    alt="Foto Profil">

                <div class="absolute bottom-1 right-1 bg-green-500 w-5 h-5 rounded-full border-2 border-white">
                </div>

            </div>

            <h2 class="text-2xl font-bold mt-4 text-gray-800">
                <?= htmlspecialchars($user['nama_lengkap']) ?>
            </h2>

            <span class="mt-2 px-4 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">
                <?= htmlspecialchars($user['role_name']) ?>
            </span>

        </div>

        <!-- Form -->
        <form method="POST"
              enctype="multipart/form-data"
              class="p-6 space-y-5">

            <div>

                <label class="text-sm font-medium text-gray-600">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama_lengkap"
                    value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                    class="w-full mt-2 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                    required>

            </div>

            <div>

                <label class="text-sm font-medium text-gray-600">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($user['email']) ?>"
                    class="w-full mt-2 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                    required>

            </div>

            <div>

                <label class="text-sm font-medium text-gray-600">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Kosongkan jika tidak ingin mengganti"
                    class="w-full mt-2 border border-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">

            </div>

            <div>

                <label class="text-sm font-medium text-gray-600">
                    Foto Profil
                </label>

                <input
                    type="file"
                    name="foto"
                    class="w-full mt-2 border border-gray-200 rounded-2xl px-4 py-3">

            </div>

            <!-- Tombol -->
            <div class="space-y-3 pt-2">

                <button
                    type="submit"
                    class="w-full bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-2xl font-semibold transition">

                    Simpan Perubahan

                </button>

                <a
                    href="../logout.php"
                    class="block w-full text-center bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-semibold transition">

                    Logout

                </a>

            </div>

        </form>

    </div>

</div>

<?php include 'navbar_karyawan.php'; ?>

</body>
</html>