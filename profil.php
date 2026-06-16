<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_SESSION['user_id'];

$query = mysqli_query($conn,"
SELECT u.*, r.name AS role_name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.id = '$id'
LIMIT 1
");

$user = mysqli_fetch_assoc($query);

/* foto default */
$foto = !empty($user['foto'])
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']) . "&background=f97316&color=fff";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil Saya</title>

<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<?php
/* NAVBAR DINAMIS */
if ($user['role_name'] == 'owner') {
    include "owner/navbar_owner.php";
} else {
    include "karyawan/navbar_karyawan.php";
}
?>

<div class="lg:ml-64 min-h-screen">

<!-- HEADER -->
<div class="bg-orange-500 text-white p-6 shadow">
    <h1 class="text-2xl font-bold">Profil Saya</h1>
</div>

<div class="p-6">

<!-- CARD -->
<div class="bg-white rounded-2xl shadow p-6 max-w-3xl mx-auto">

    <div class="flex flex-col items-center text-center">

        <img src="<?= $foto ?>"
             class="w-28 h-28 rounded-full object-cover border-4 border-orange-300">

        <h2 class="text-xl font-bold mt-3">
            <?= htmlspecialchars($user['nama_lengkap']) ?>
        </h2>

        <p class="text-gray-500">@<?= $user['username'] ?></p>

        <span class="mt-2 bg-orange-100 text-orange-600 px-3 py-1 rounded-full text-sm">
            <?= strtoupper($user['role_name']) ?>
        </span>

    </div>

    <!-- DETAIL -->
    <div class="grid md:grid-cols-2 gap-4 mt-6">

        <div>
            <p class="text-gray-500 text-sm">Email</p>
            <p class="font-semibold"><?= $user['email'] ?></p>
        </div>

        <div>
            <p class="text-gray-500 text-sm">Status</p>
            <p class="font-semibold"><?= $user['status'] ?></p>
        </div>

        <div>
            <p class="text-gray-500 text-sm">Bergabung</p>
            <p class="font-semibold"><?= $user['created_at'] ?></p>
        </div>

    </div>

    <!-- BUTTON EDIT (HIDE FORM nanti bisa dipakai JS) -->
    <div class="mt-6 text-center">

        <button onclick="toggleEdit()"
                class="bg-orange-500 text-white px-5 py-2 rounded-xl">
            Edit Profil
        </button>

    </div>

</div>

<!-- FORM EDIT (HIDE) -->
<div id="editForm" class="hidden bg-white rounded-2xl shadow p-6 max-w-3xl mx-auto mt-6">

    <form action="update_profil.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <div class="grid md:grid-cols-2 gap-4">

            <input type="text" name="nama_lengkap"
                   value="<?= $user['nama_lengkap'] ?>"
                   class="border p-2 rounded-xl">

            <input type="email" name="email"
                   value="<?= $user['email'] ?>"
                   class="border p-2 rounded-xl">

            <input type="file" name="foto"
                   class="border p-2 rounded-xl md:col-span-2">

        </div>

        <div class="mt-4 flex gap-3">

            <button class="bg-green-500 text-white px-4 py-2 rounded-xl">
                Simpan
            </button>

            <button type="button"
                    onclick="toggleEdit()"
                    class="bg-gray-400 text-white px-4 py-2 rounded-xl">
                Batal
            </button>

        </div>

    </form>

</div>

</div>
</div>

<script>
function toggleEdit() {
    const form = document.getElementById("editForm");
    form.classList.toggle("hidden");
}
</script>

</body>
</html>