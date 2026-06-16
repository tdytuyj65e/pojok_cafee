<?php
session_start();
include "../koneksi.php";

/* ==========================
   CEK LOGIN OWNER
========================== */

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
SELECT
    p.*,
    c.nama_kategori
FROM products p
LEFT JOIN categories c
ON p.category_id = c.id
WHERE p.nama_produk LIKE CONCAT('%', ?, '%')
ORDER BY p.id DESC
");

$stmt->bind_param("s", $cari);
$stmt->execute();

$query = $stmt->get_result();

$total_produk = $query->num_rows;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Produk - Pojok Kafe</title>

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
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 text-white shadow">

        <h1 class="text-3xl font-bold">
            Manajemen Produk ☕
        </h1>

        <p class="text-orange-100 mt-1">
            Kelola seluruh produk Pojok Kafe
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

        <!-- TOP BAR -->
        <div class="bg-white rounded-3xl shadow p-5 mb-6">

            <div class="flex flex-col md:flex-row gap-4 justify-between items-center">

                <form method="GET" class="flex gap-2 w-full md:w-auto">

                    <input
                        type="text"
                        name="cari"
                        value="<?= htmlspecialchars($cari) ?>"
                        placeholder="Cari produk..."
                        class="border border-gray-300 rounded-xl px-4 py-3 w-full md:w-80">

                    <button
                        type="submit"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-5 rounded-xl">

                        Cari

                    </button>

                </form>

                <a
                    href="tambah_produk.php"
                    class="bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl">

                    + Tambah Produk

                </a>

            </div>

        </div>

        <!-- INFO -->
        <div class="mb-6">

            <span class="bg-orange-100 text-orange-700 px-4 py-2 rounded-xl font-medium">
                Total Produk : <?= $total_produk ?>
            </span>

        </div>

        <!-- PRODUK -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">

        <?php while($p = mysqli_fetch_assoc($query)): ?>

            <div class="bg-white rounded-3xl shadow hover:shadow-xl transition overflow-hidden">

                <!-- FOTO -->
                <?php if(!empty($p['foto'])): ?>

                    <img
                    src="../uploads/<?= htmlspecialchars($p['foto']) ?>"
                    alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                    class="w-full h-52 object-cover">

                <?php else: ?>

                    <div class="h-52 bg-gray-200 flex items-center justify-center text-6xl">
                        ☕
                    </div>

                <?php endif; ?>

                <!-- CONTENT -->
                <div class="p-5">

                    <div class="flex justify-between items-start">

                        <div>

                            <h2 class="font-bold text-lg">
                                <?= htmlspecialchars($p['nama_produk']) ?>
                            </h2>

                            <p class="text-gray-500 text-sm">
                                <?= htmlspecialchars($p['nama_kategori'] ?? '-') ?>
                            </p>

                        </div>

                        <?php if($p['stok'] <= $p['stok_minimum']): ?>

                        <span class="bg-red-100 text-red-600 px-2 py-1 rounded-lg text-xs">
                            Stok Menipis
                        </span>

                        <?php endif; ?>

                    </div>

                    <div class="mt-4 space-y-2">

                        <div class="flex justify-between">

                            <span class="text-gray-500">
                                Harga
                            </span>

                            <span class="font-bold text-green-600">
                                Rp <?= number_format($p['harga'],0,',','.') ?>
                            </span>

                        </div>

                        <div class="flex justify-between">

                            <span class="text-gray-500">
                                Stok
                            </span>

                            <span class="font-semibold">
                                <?= $p['stok'] ?>
                            </span>

                        </div>

                    </div>

                    <!-- BUTTON -->
                    <div class="grid grid-cols-2 gap-3 mt-5">

                        <a
                        href="edit_produk.php?id=<?= $p['id'] ?>"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-center py-2 rounded-xl">

                            Edit

                        </a>

                        <a
                        href="hapus_produk.php?id=<?= $p['id'] ?>"
                        onclick="return confirm('Yakin ingin menghapus produk ini?')"
                        class="bg-red-500 hover:bg-red-600 text-white text-center py-2 rounded-xl">

                            Hapus

                        </a>

                    </div>

                </div>

            </div>

        <?php endwhile; ?>

        </div>

        <?php if($total_produk == 0): ?>

        <div class="bg-white rounded-3xl shadow p-10 text-center mt-6">

            <div class="text-6xl mb-4">
                📦
            </div>

            <h3 class="text-xl font-bold text-gray-700">
                Produk Tidak Ditemukan
            </h3>

            <p class="text-gray-500 mt-2">
                Belum ada produk yang sesuai dengan pencarian.
            </p>

        </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>