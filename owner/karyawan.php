<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ((int)$_SESSION['role_id'] !== 1) {
    header("Location: ../karyawan/dashboard.php");
    exit;
}

/* ==========================
   PENCARIAN
========================== */

$cari = $_GET['cari'] ?? '';

$stmt = $conn->prepare("
SELECT *
FROM users
WHERE role_id = 2
AND (
    nama_lengkap LIKE CONCAT('%', ?, '%')
    OR username LIKE CONCAT('%', ?, '%')
)
ORDER BY id DESC
");

$stmt->bind_param("ss", $cari, $cari);
$stmt->execute();

$karyawan = $stmt->get_result();

/* ==========================
   STATISTIK
========================== */

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM users
WHERE role_id = 2
"))['total'];

$aktif = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM users
WHERE role_id = 2
AND status='aktif'
"))['total'];

$nonaktif = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM users
WHERE role_id = 2
AND status='nonaktif'
"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manajemen Karyawan</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
}
</style>

</head>
<body class="bg-slate-100">

<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 shadow">

        <h1 class="text-3xl font-bold">
            Manajemen Karyawan 👨‍💼
        </h1>

        <p class="text-orange-100">
            Kelola data karyawan Pojok Kafe
        </p>

    </div>

    <div class="p-6">

        <!-- ALERT -->
        <?php if(isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-xl mb-5">
            <?= $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-5">
            <?= $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- STATISTIK -->
        <div class="grid md:grid-cols-3 gap-5 mb-6">

            <div class="bg-white rounded-3xl shadow p-5">
                <p class="text-gray-500">Total Karyawan</p>
                <h2 class="text-3xl font-bold text-orange-600">
                    <?= $total ?>
                </h2>
            </div>

            <div class="bg-white rounded-3xl shadow p-5">
                <p class="text-gray-500">Karyawan Aktif</p>
                <h2 class="text-3xl font-bold text-green-600">
                    <?= $aktif ?>
                </h2>
            </div>

            <div class="bg-white rounded-3xl shadow p-5">
                <p class="text-gray-500">Karyawan Nonaktif</p>
                <h2 class="text-3xl font-bold text-red-600">
                    <?= $nonaktif ?>
                </h2>
            </div>

        </div>

        <!-- SEARCH -->
        <div class="bg-white rounded-3xl shadow p-5 mb-6">

            <div class="flex flex-col md:flex-row gap-4 justify-between">

                <form method="GET" class="flex gap-2">

                    <input
                        type="text"
                        name="cari"
                        value="<?= htmlspecialchars($cari) ?>"
                        placeholder="Cari karyawan..."
                        class="border border-gray-300 rounded-xl px-4 py-3 w-full md:w-80">

                    <button
                        type="submit"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-5 rounded-xl">

                        Cari

                    </button>

                </form>

                <a
                    href="tambah_karyawan.php"
                    class="bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl">

                    + Tambah Karyawan

                </a>

            </div>

        </div>

        <!-- TABEL -->
        <div class="bg-white rounded-3xl shadow overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-orange-500 text-white">

                        <tr>
                            <th class="p-4 text-left">Foto</th>
                            <th class="p-4 text-left">Nama</th>
                            <th class="p-4 text-left">Username</th>
                            <th class="p-4 text-left">Email</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php while($u = mysqli_fetch_assoc($karyawan)): ?>

                    <tr class="border-b hover:bg-orange-50">

                        <td class="p-4">

                            <?php if(!empty($u['foto'])): ?>

                                <img
                                    src="../uploads/<?= $u['foto'] ?>"
                                    class="w-14 h-14 rounded-full object-cover">

                            <?php else: ?>

                                <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center text-2xl">
                                    👤
                                </div>

                            <?php endif; ?>

                        </td>

                        <td class="p-4 font-medium">
                            <?= htmlspecialchars($u['nama_lengkap']) ?>
                        </td>

                        <td class="p-4">
                            <?= htmlspecialchars($u['username']) ?>
                        </td>

                        <td class="p-4">
                            <?= htmlspecialchars($u['email']) ?>
                        </td>

                        <td class="p-4">

                            <?php if($u['status']=='aktif'): ?>

                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                Aktif
                            </span>

                            <?php else: ?>

                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                                Nonaktif
                            </span>

                            <?php endif; ?>

                        </td>

                        <td class="p-4">

                            <div class="flex justify-center gap-2">

                                <a
                                    href="edit_karyawan.php?id=<?= $u['id'] ?>"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg">

                                    Edit

                                </a>

                                <a
                                    href="hapus_karyawan.php?id=<?= $u['id'] ?>"
                                    onclick="return confirm('Yakin ingin menghapus karyawan ini?')"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg">

                                    Hapus

                                </a>

                            </div>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>