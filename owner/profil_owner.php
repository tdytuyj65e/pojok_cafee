<?php
session_start();
include "../koneksi.php";

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

/* ==========================
   UPDATE PROFIL
========================== */
if (isset($_POST['update_profil'])) {

    $nama  = $_POST['nama_lengkap'];
    $email = $_POST['email'];

    $fotoName = $user['foto'];

    if (!empty($_FILES['foto']['name'])) {

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoName = "user_" . time() . "." . $ext;

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

$foto = !empty($user['foto'])
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=".urlencode($user['nama_lengkap'])."&background=f97316&color=fff";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Profil Owner</title>

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

<div class="lg:ml-64 min-h-screen">

    <!-- HEADER ORANGE -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 shadow flex justify-between items-center">

        <div>
            <h1 class="text-3xl font-bold">Profil Owner 👤</h1>
            <p class="text-orange-100">Informasi akun pengguna</p>
        </div>

        <!-- BUTTON EDIT -->
        <button onclick="toggleEdit()"
                class="bg-white text-orange-600 px-5 py-2 rounded-xl font-semibold shadow">
            Edit Profil
        </button>

    </div>

    <!-- CONTENT -->
    <div class="p-6">

        <div class="max-w-5xl mx-auto">

            <!-- PROFILE CARD -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">

                <div class="p-8">

                    <div class="flex flex-col md:flex-row items-center gap-6">

                        <img src="<?= $foto ?>"
                             class="w-36 h-36 rounded-full border-4 border-orange-200 shadow-xl object-cover">

                        <div class="flex-1 text-center md:text-left">

                            <h2 class="text-3xl font-bold text-gray-800">
                                <?= htmlspecialchars($user['nama_lengkap']) ?>
                            </h2>

                            <p class="text-gray-500">
                                @<?= htmlspecialchars($user['username']) ?>
                            </p>

                            <div class="flex flex-wrap gap-2 mt-4 justify-center md:justify-start">

                                <span class="bg-orange-100 text-orange-600 px-4 py-1 rounded-full text-sm font-semibold">
                                    <?= strtoupper($user['role_name']) ?>
                                </span>

                                <span class="bg-green-100 text-green-600 px-4 py-1 rounded-full text-sm font-semibold">
                                    <?= ucfirst($user['status']) ?>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>
            </div>

            <!-- INFO GRID -->
            <div class="grid md:grid-cols-2 gap-6 mt-6">

                <div class="bg-white rounded-3xl shadow p-6">
                    <h3 class="text-xl font-bold mb-4">Informasi Akun</h3>

                    <p><b>Nama:</b> <?= $user['nama_lengkap'] ?></p>
                    <p><b>Username:</b> <?= $user['username'] ?></p>
                    <p><b>Email:</b> <?= $user['email'] ?></p>
                </div>

                <div class="bg-white rounded-3xl shadow p-6">
                    <h3 class="text-xl font-bold mb-4">Sistem</h3>

                    <p><b>Role:</b> <?= $user['role_name'] ?></p>
                    <p><b>Status:</b> <?= $user['status'] ?></p>
                    <p><b>Bergabung:</b> <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                </div>

            </div>

            <!-- ==========================
                 FORM EDIT (HIDE / SHOW)
            ========================== -->
            <div id="formEdit" class="hidden mt-6 bg-white p-6 rounded-3xl shadow">

                <h2 class="text-xl font-bold mb-4">Edit Profil</h2>

                <form method="POST" enctype="multipart/form-data"
                      class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap"
                               value="<?= $user['nama_lengkap'] ?>"
                               class="w-full border p-3 rounded-xl">
                    </div>

                    <div>
                        <label class="text-sm">Email</label>
                        <input type="email" name="email"
                               value="<?= $user['email'] ?>"
                               class="w-full border p-3 rounded-xl">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm">Foto</label>
                        <input type="file" name="foto"
                               class="w-full border p-3 rounded-xl">
                    </div>

                    <div class="md:col-span-2 flex gap-3">

                        <button type="submit" name="update_profil"
                                class="bg-orange-600 text-white px-5 py-3 rounded-xl">
                            Simpan
                        </button>

                        <button type="button" onclick="toggleEdit()"
                                class="bg-gray-500 text-white px-5 py-3 rounded-xl">
                            Batal
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>
</html>