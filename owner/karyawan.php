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
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#16a34a">

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
}

/* ==========================
   OVERLAY FORM TAMBAH
========================== */
#overlayTambah {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(2px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 50;
    padding: 1rem;
}

#overlayTambah.flex {
    display: flex;
}

#formTambah {
    background: #fff;
    border-radius: 1.5rem;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.92) translateY(16px);
    opacity: 0;
    transition: all 0.25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}

#overlayTambah.show #formTambah {
    transform: scale(1) translateY(0);
    opacity: 1;
}

#previewFoto {
    display: none;
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #f97316;
    margin-top: 8px;
}

/* ==========================
   OVERLAY FORM EDIT
========================== */
#overlayEdit {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(2px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 50;
    padding: 1rem;
}

#overlayEdit.flex {
    display: flex;
}

#formEdit {
    background: #fff;
    border-radius: 1.5rem;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.92) translateY(16px);
    opacity: 0;
    transition: all 0.25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}

#overlayEdit.show #formEdit {
    transform: scale(1) translateY(0);
    opacity: 1;
}

#previewFotoEdit {
    display: none;
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #3b82f6;
    margin-top: 8px;
}
</style>

</head>
<body class="bg-slate-100">

<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 shadow">
        <h1 class="text-3xl font-bold">Manajemen Karyawan 👨‍💼</h1>
        <p class="text-orange-100">Kelola data karyawan Pojok Kafe</p>
    </div>

    <div class="p-6">

        <!-- ALERT SUKSES -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-xl mb-5 flex items-center gap-2">
            <span>✅</span>
            <span><?= $_SESSION['success'] ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- ALERT ERROR -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-5 flex items-center gap-2">
            <span>❌</span>
            <span><?= $_SESSION['error'] ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- STATISTIK -->
        <div class="grid md:grid-cols-3 gap-5 mb-6">

            <div class="bg-white rounded-3xl shadow p-5 flex items-center gap-4">
                <div class="bg-orange-100 text-orange-600 rounded-2xl p-3 text-2xl">👥</div>
                <div>
                    <p class="text-gray-500 text-sm">Total Karyawan</p>
                    <h2 class="text-3xl font-bold text-orange-600"><?= $total ?></h2>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow p-5 flex items-center gap-4">
                <div class="bg-green-100 text-green-600 rounded-2xl p-3 text-2xl">✅</div>
                <div>
                    <p class="text-gray-500 text-sm">Karyawan Aktif</p>
                    <h2 class="text-3xl font-bold text-green-600"><?= $aktif ?></h2>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow p-5 flex items-center gap-4">
                <div class="bg-red-100 text-red-600 rounded-2xl p-3 text-2xl">🚫</div>
                <div>
                    <p class="text-gray-500 text-sm">Karyawan Nonaktif</p>
                    <h2 class="text-3xl font-bold text-red-600"><?= $nonaktif ?></h2>
                </div>
            </div>

        </div>

        <!-- SEARCH + TOMBOL TAMBAH -->
        <div class="bg-white rounded-3xl shadow p-5 mb-6">

            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">

                <!-- Form Pencarian -->
                <form method="GET" class="flex gap-2 w-full md:w-auto">
                    <input
                        type="text"
                        name="cari"
                        value="<?= htmlspecialchars($cari) ?>"
                        placeholder="Cari nama atau username..."
                        class="border border-gray-300 rounded-xl px-4 py-3 w-full md:w-80 focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <button
                        type="submit"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-5 rounded-xl font-medium transition">
                        🔍 Cari
                    </button>
                    <?php if ($cari): ?>
                    <a href="karyawan.php"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-3 rounded-xl font-medium transition">
                        ✕
                    </a>
                    <?php endif; ?>
                </form>

                <!-- Tombol Toggle Form Tambah -->
                <button
                    id="btnToggle"
                    onclick="toggleFormTambah()"
                    class="bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl font-medium transition flex items-center gap-2 whitespace-nowrap">
                    <span id="btnIcon">➕</span>
                    <span id="btnText">Tambah Karyawan</span>
                </button>

            </div>

        </div>

        <!-- ============================================
             OVERLAY FORM TAMBAH KARYAWAN
        ============================================ -->
        <div id="overlayTambah" onclick="if(event.target===this) toggleFormTambah()">
            <div id="formTambah">

                <div class="p-6">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-orange-500 font-semibold text-lg">
                            ➕ Tambah Akun Karyawan
                        </h3>
                        <button
                            type="button"
                            onclick="toggleFormTambah()"
                            class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                            &times;
                        </button>
                    </div>

                    <form method="POST" action="tambah_karyawan.php" enctype="multipart/form-data">

                        <div class="grid md:grid-cols-2 gap-4">

                            <!-- Nama Lengkap -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="nama_lengkap"
                                    required
                                    placeholder="Nama lengkap karyawan"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-300">
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="username"
                                    required
                                    placeholder="Username untuk login"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-300">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    required
                                    placeholder="email@example.com"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-300">
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        name="password"
                                        id="passwordInput"
                                        required
                                        placeholder="Password"
                                        class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-300 pr-12">
                                    <button
                                        type="button"
                                        onclick="togglePassword()"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg">
                                        👁
                                    </button>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">Status</label>
                                <select
                                    name="status"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-300">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>

                            <!-- Foto -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">Foto Profil</label>
                                <input
                                    type="file"
                                    name="foto"
                                    accept="image/*"
                                    onchange="previewGambar(event)"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-300">
                                <img id="previewFoto" src="" alt="Preview Foto">
                            </div>

                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex gap-3 mt-5">
                            <button
                                type="submit"
                                class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-medium transition">
                                💾 Simpan Karyawan
                            </button>
                            <button
                                type="button"
                                onclick="toggleFormTambah()"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-6 py-3 rounded-xl font-medium transition">
                                Batal
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

        <!-- ============================================
             OVERLAY FORM EDIT KARYAWAN
        ============================================ -->
        <div id="overlayEdit" onclick="if(event.target===this) tutupFormEdit()">
            <div id="formEdit">

                <div class="p-6">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-blue-500 font-semibold text-lg">
                            ✏️ Edit Data Karyawan
                        </h3>
                        <button
                            type="button"
                            onclick="tutupFormEdit()"
                            class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                            &times;
                        </button>
                    </div>

                    <form method="POST" action="edit_karyawan.php" enctype="multipart/form-data">

                        <!-- Hidden ID -->
                        <input type="hidden" name="id" id="editId">

                        <div class="grid md:grid-cols-2 gap-4">

                            <!-- Nama Lengkap -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="nama_lengkap"
                                    id="editNama"
                                    required
                                    placeholder="Nama lengkap karyawan"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-300">
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="username"
                                    id="editUsername"
                                    required
                                    placeholder="Username untuk login"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-300">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="editEmail"
                                    required
                                    placeholder="email@example.com"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-300">
                            </div>

                            <!-- Password (opsional) -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">
                                    Password Baru
                                    <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        name="password"
                                        id="editPasswordInput"
                                        placeholder="Password baru"
                                        class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-300 pr-12">
                                    <button
                                        type="button"
                                        onclick="togglePasswordEdit()"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg">
                                        👁
                                    </button>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">Status</label>
                                <select
                                    name="status"
                                    id="editStatus"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>

                            <!-- Foto -->
                            <div>
                                <label class="text-sm text-gray-500 mb-1 block font-medium">Foto Profil</label>
                                <!-- Foto saat ini -->
                                <div id="fotoSaatIniWrap" class="mb-2 hidden">
                                    <p class="text-xs text-gray-400 mb-1">Foto saat ini:</p>
                                    <img id="fotoSaatIni"
                                        src=""
                                        alt="Foto Saat Ini"
                                        class="w-14 h-14 rounded-full object-cover border-2 border-blue-200">
                                </div>
                                <input
                                    type="file"
                                    name="foto"
                                    accept="image/*"
                                    onchange="previewGambarEdit(event)"
                                    class="border border-gray-300 rounded-xl px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <img id="previewFotoEdit" src="" alt="Preview Foto Baru">
                            </div>

                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex gap-3 mt-5">
                            <button
                                type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-medium transition">
                                💾 Simpan Perubahan
                            </button>
                            <button
                                type="button"
                                onclick="tutupFormEdit()"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-6 py-3 rounded-xl font-medium transition">
                                Batal
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

        <!-- TABEL KARYAWAN -->
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

                    <?php
                    $jumlahBaris = 0;
                    while ($u = mysqli_fetch_assoc($karyawan)):
                        $jumlahBaris++;
                    ?>

                    <tr class="border-b hover:bg-orange-50 transition">

                        <td class="p-4">
                            <?php if (!empty($u['foto'])): ?>
                                <img
                                    src="../uploads/<?= htmlspecialchars($u['foto']) ?>"
                                    class="w-14 h-14 rounded-full object-cover border-2 border-orange-200">
                            <?php else: ?>
                                <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center text-2xl">
                                    👤
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="p-4 font-medium text-gray-800">
                            <?= htmlspecialchars($u['nama_lengkap']) ?>
                        </td>

                        <td class="p-4 text-gray-600">
                            <?= htmlspecialchars($u['username']) ?>
                        </td>

                        <td class="p-4 text-gray-600">
                            <?= htmlspecialchars($u['email']) ?>
                        </td>

                        <td class="p-4">
                            <?php if ($u['status'] == 'aktif'): ?>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                    ✅ Aktif
                                </span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">
                                    🚫 Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="p-4">
                            <div class="flex justify-center gap-2">

                                <!-- Tombol Edit — buka form terbang -->
                                <button
                                    onclick="bukaFormEdit(
                                        <?= $u['id'] ?>,
                                        '<?= htmlspecialchars(addslashes($u['nama_lengkap'])) ?>',
                                        '<?= htmlspecialchars(addslashes($u['username'])) ?>',
                                        '<?= htmlspecialchars(addslashes($u['email'])) ?>',
                                        '<?= $u['status'] ?>',
                                        '<?= $u['foto'] ?>'
                                    )"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm transition">
                                    ✏️ Edit
                                </button>

                                <!-- Tombol Hapus -->
                                <a
                                    href="hapus_karyawan.php?id=<?= $u['id'] ?>"
                                    onclick="return confirm('Yakin ingin menghapus karyawan <?= htmlspecialchars(addslashes($u['nama_lengkap'])) ?>?')"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm transition">
                                    🗑️ Hapus
                                </a>

                            </div>
                        </td>

                    </tr>

                    <?php endwhile; ?>

                    <!-- Jika tidak ada data -->
                    <?php if ($jumlahBaris === 0): ?>
                    <tr>
                        <td colspan="6" class="p-10 text-center text-gray-400">
                            <div class="text-5xl mb-3">🔍</div>
                            <p class="font-medium">
                                <?= $cari ? "Karyawan \"" . htmlspecialchars($cari) . "\" tidak ditemukan." : "Belum ada data karyawan." ?>
                            </p>
                            <?php if ($cari): ?>
                            <a href="karyawan.php" class="text-orange-500 hover:underline text-sm mt-1 inline-block">
                                Tampilkan semua karyawan
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- Footer tabel -->
            <?php if ($jumlahBaris > 0): ?>
            <div class="px-5 py-3 bg-gray-50 text-sm text-gray-500 border-t">
                Menampilkan <strong><?= $jumlahBaris ?></strong> karyawan
                <?= $cari ? "untuk pencarian \"<strong>" . htmlspecialchars($cari) . "</strong>\"" : "" ?>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<script>
/* ==============================
   FORM TAMBAH KARYAWAN
============================== */
function toggleFormTambah() {
    const overlay = document.getElementById('overlayTambah');
    const icon    = document.getElementById('btnIcon');
    const text    = document.getElementById('btnText');
    const btn     = document.getElementById('btnToggle');

    const isClosed = !overlay.classList.contains('flex');

    if (isClosed) {
        overlay.classList.add('flex');
        requestAnimationFrame(() => overlay.classList.add('show'));
        document.body.style.overflow = 'hidden';
        icon.textContent = '✕';
        text.textContent = 'Tutup Form';
        btn.classList.replace('bg-green-500', 'bg-gray-500');
        btn.classList.replace('hover:bg-green-600', 'hover:bg-gray-600');
    } else {
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        icon.textContent = '➕';
        text.textContent = 'Tambah Karyawan';
        btn.classList.replace('bg-gray-500', 'bg-green-500');
        btn.classList.replace('hover:bg-gray-600', 'hover:bg-green-600');
        setTimeout(() => overlay.classList.remove('flex'), 250);
    }
}

function togglePassword() {
    const input = document.getElementById('passwordInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function previewGambar(event) {
    const preview = document.getElementById('previewFoto');
    const file    = event.target.files[0];
    if (file) {
        preview.src           = URL.createObjectURL(file);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

/* ==============================
   FORM EDIT KARYAWAN
============================== */
function bukaFormEdit(id, nama, username, email, status, foto) {
    // Isi field dengan data karyawan yang dipilih
    document.getElementById('editId').value       = id;
    document.getElementById('editNama').value     = nama;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value    = email;
    document.getElementById('editStatus').value   = status;

    // Reset password
    document.getElementById('editPasswordInput').value = '';
    document.getElementById('editPasswordInput').type  = 'password';

    // Tampilkan foto saat ini jika ada
    const fotoWrap    = document.getElementById('fotoSaatIniWrap');
    const fotoImg     = document.getElementById('fotoSaatIni');
    const previewEdit = document.getElementById('previewFotoEdit');

    if (foto && foto.trim() !== '') {
        fotoImg.src = '../uploads/' + foto;
        fotoWrap.classList.remove('hidden');
    } else {
        fotoWrap.classList.add('hidden');
    }

    // Reset preview foto baru
    previewEdit.style.display = 'none';
    previewEdit.src = '';

    // Buka overlay
    const overlay = document.getElementById('overlayEdit');
    overlay.classList.add('flex');
    requestAnimationFrame(() => overlay.classList.add('show'));
    document.body.style.overflow = 'hidden';
}

function tutupFormEdit() {
    const overlay = document.getElementById('overlayEdit');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => overlay.classList.remove('flex'), 250);
}

function togglePasswordEdit() {
    const input = document.getElementById('editPasswordInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function previewGambarEdit(event) {
    const preview = document.getElementById('previewFotoEdit');
    const file    = event.target.files[0];
    if (file) {
        preview.src           = URL.createObjectURL(file);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

/* ==============================
   TUTUP DENGAN TOMBOL ESC
============================== */
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;

    const overlayTambah = document.getElementById('overlayTambah');
    const overlayEdit   = document.getElementById('overlayEdit');

    if (overlayEdit.classList.contains('show')) {
        tutupFormEdit();
    } else if (overlayTambah.classList.contains('show')) {
        toggleFormTambah();
    }
});
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>