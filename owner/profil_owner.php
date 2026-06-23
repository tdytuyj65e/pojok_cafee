<?php
session_start();
include "../koneksi.php";

/* =========================
   CEK LOGIN (FIX SESSION)
========================= */
$id = $_SESSION['id'] ?? null;

if (!$id) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   AMBIL DATA USER (AMAN + PREPARED)
========================= */
$stmt = $conn->prepare("
SELECT u.*, r.name AS role_name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.id = ?
LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

/* =========================
   JIKA USER TIDAK ADA
========================= */
if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   UPDATE PROFIL
========================= */
if (isset($_POST['update_profil'])) {

    $nama  = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);

    $fotoName = $user['foto'];

    if (!empty($_FILES['foto']['name'])) {

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $fotoName = "user_" . $id . "_" . time() . "." . $ext;

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "../uploads/" . $fotoName
        );
    }

    $update = $conn->prepare("
        UPDATE users
        SET nama_lengkap=?, email=?, foto=?
        WHERE id=?
    ");

    $update->bind_param("sssi", $nama, $email, $fotoName, $id);
    $update->execute();

    header("Location: profil_owner.php");
    exit;
}

/* =========================
   FOTO PROFILE (SAFE)
========================= */
$foto = (!empty($user['foto']) && file_exists("../uploads/" . $user['foto']))
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']) . "&background=f97316&color=fff";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Profil Owner</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#16a34a">

<script src="https://cdn.tailwindcss.com"></script>

<style>
body{
    font-family:'Poppins',sans-serif;
}
</style>

<script>
function toggleEdit(){
    document.getElementById("formEdit").classList.toggle("hidden");
}
</script>

</head>

<body class="bg-slate-100">

<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen p-4 sm:p-6">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-5 rounded-2xl shadow flex flex-col sm:flex-row justify-between gap-3">

        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">Profil Owner 👤</h1>
            <p class="text-orange-100 text-sm">Informasi akun pengguna</p>
        </div>

        <button onclick="toggleEdit()"
                class="bg-white text-orange-600 px-4 py-2 rounded-xl font-semibold w-full sm:w-auto">
            Edit Profil
        </button>

    </div>

    <!-- CONTENT -->
    <div class="mt-6 max-w-5xl mx-auto space-y-6">

        <!-- CARD -->
        <div class="bg-white rounded-3xl shadow p-6">

            <div class="flex flex-col md:flex-row items-center gap-6">

                <img src="<?= $foto ?>"
                     class="w-32 h-32 sm:w-36 sm:h-36 rounded-full border-4 border-orange-200 object-cover">

                <div class="text-center md:text-left">

                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">
                        <?= htmlspecialchars($user['nama_lengkap']) ?>
                    </h2>

                    <p class="text-gray-500">
                        @<?= htmlspecialchars($user['username']) ?>
                    </p>

                    <span class="inline-block mt-3 bg-orange-100 text-orange-600 px-4 py-1 rounded-full text-sm font-semibold">
                        <?= strtoupper($user['role_name']) ?>
                    </span>

                </div>

            </div>

        </div>

        <!-- INFO -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-2xl shadow p-5">
                <h3 class="font-bold mb-3">Informasi Akun</h3>
                <p><b>Nama:</b> <?= htmlspecialchars($user['nama_lengkap']) ?></p>
                <p><b>Username:</b> <?= htmlspecialchars($user['username']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow p-5">
                <h3 class="font-bold mb-3">Sistem</h3>
                <p><b>Role:</b> <?= $user['role_name'] ?></p>
                <p><b>Status:</b> <?= $user['status'] ?></p>
                <p><b>Bergabung:</b> <?= date('d M Y', strtotime($user['created_at'])) ?></p>
            </div>

        </div>

        <!-- FORM EDIT -->
        <div id="formEdit" class="hidden bg-white rounded-2xl shadow p-5">

            <h2 class="font-bold text-xl mb-4">Edit Profil</h2>

            <form method="POST" enctype="multipart/form-data"
                  class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <input type="text" name="nama_lengkap"
                       value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                       class="border p-3 rounded-xl w-full">

                <input type="email" name="email"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       class="border p-3 rounded-xl w-full">

                <input type="file" name="foto"
                       class="border p-3 rounded-xl w-full md:col-span-2">

                <button type="submit" name="update_profil"
                        class="bg-orange-600 text-white py-3 rounded-xl md:col-span-2">
                    Simpan Perubahan
                </button>

            </form>

        </div>

    </div>

</div>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>