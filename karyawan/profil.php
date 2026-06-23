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
   AMBIL DATA USER (awal)
========================= */
$stmt = mysqli_prepare($conn, "
    SELECT u.*, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

/* =========================
   HANDLE UPDATE PROFIL
========================= */
$success_msg = '';
$error_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';

    /* ================= FOTO ================= */
    if (!empty($_FILES['foto']['name'])) {

        $uploadDir = "../uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext     = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {

            $namaFile = "user_" . $user_id . "_" . time() . "." . $ext;
            $target   = $uploadDir . $namaFile;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {

                // Hapus foto lama jika ada
                if (!empty($user['foto']) && file_exists($uploadDir . $user['foto'])) {
                    unlink($uploadDir . $user['foto']);
                }

                $stmtFoto = mysqli_prepare($conn, "UPDATE users SET foto=? WHERE id=?");
                mysqli_stmt_bind_param($stmtFoto, "si", $namaFile, $user_id);
                mysqli_stmt_execute($stmtFoto);
            }
        } else {
            $error_msg = "Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.";
        }
    }

    /* ================= PASSWORD ================= */
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $error_msg = "Password minimal 6 karakter.";
        } else {
            $hash     = password_hash($password, PASSWORD_DEFAULT);
            $stmtPass = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($stmtPass, "si", $hash, $user_id);
            mysqli_stmt_execute($stmtPass);
        }
    }

    /* ================= UPDATE DATA UTAMA ================= */
    if (empty($error_msg)) {
        $stmtUpdate = mysqli_prepare($conn, "
            UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmtUpdate, "ssi", $nama, $email, $user_id);
        mysqli_stmt_execute($stmtUpdate);

        $success_msg = "Profil berhasil diperbarui.";
    }
}

/* =========================
   REFRESH DATA USER
========================= */
$stmt = mysqli_prepare($conn, "
    SELECT u.*, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

/* =========================
   FOTO DEFAULT / UPLOAD
========================= */
$foto = (!empty($user['foto']) && file_exists("../uploads/" . $user['foto']))
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']) . "&background=f97316&color=fff&size=200";

/* Inisial untuk avatar placeholder */
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user['nama_lengkap']), 0, 2)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

<style>
  * { font-family: 'Plus Jakarta Sans', sans-serif; }

  .material-symbols-rounded {
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
  }

  /* Subtle animated gradient for header */
  .header-bg {
    background: linear-gradient(135deg, #ea580c 0%, #f97316 50%, #fb923c 100%);
    position: relative;
    overflow: hidden;
  }
  .header-bg::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
    top: -80px; right: -60px;
  }
  .header-bg::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    bottom: -60px; left: -40px;
  }

  /* Photo ring animation */
  .photo-ring {
    background: conic-gradient(from 0deg, #f97316, #fb923c, #fdba74, #f97316);
    padding: 3px;
    border-radius: 9999px;
    animation: spin-slow 6s linear infinite;
  }
  @keyframes spin-slow {
    to { transform: rotate(360deg); }
  }
  .photo-inner {
    border-radius: 9999px;
    overflow: hidden;
    background: white;
    padding: 3px;
  }

  /* Input focus glow */
  .field-input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
  }

  /* File input custom */
  .file-btn input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
  }

  /* Toast slide-in */
  @keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
  }
  .toast { animation: slideUp .35s ease both; }

  /* Card shadow */
  .main-card {
    box-shadow: 0 20px 60px -10px rgba(234,88,12,0.13), 0 4px 20px rgba(0,0,0,0.06);
  }

  /* Divider */
  .section-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #fed7aa, transparent);
  }
</style>
</head>
<body class="bg-slate-50 min-h-screen pb-28">

<!-- ===================== HEADER ===================== -->
<div class="header-bg h-52">
  <div class="relative z-10 max-w-md mx-auto px-5 pt-10 flex items-center justify-between text-white">
    <div>
      <p class="text-orange-200 text-sm font-medium tracking-wide uppercase">Akun Saya</p>
      <h1 class="text-2xl font-extrabold mt-0.5">Profil</h1>
    </div>
    <span class="material-symbols-rounded text-orange-200 text-4xl">manage_accounts</span>
  </div>
</div>

<!-- ===================== MAIN CARD ===================== -->
<div class="max-w-md mx-auto px-4 -mt-24 relative z-10">

  <div class="main-card bg-white rounded-3xl overflow-hidden">

    <!-- Avatar Section -->
    <div class="flex flex-col items-center pt-8 pb-6 px-6 bg-gradient-to-b from-orange-50/70 to-white">

      <div class="photo-ring mb-1">
        <div class="photo-inner w-28 h-28">
          <img
            id="preview-foto"
            src="<?= $foto ?>"
            class="w-full h-full object-cover"
            alt="Foto Profil">
        </div>
      </div>

      <!-- Tombol ganti foto kecil -->
      <label class="file-btn relative mt-3 inline-flex items-center gap-1.5 bg-orange-50 hover:bg-orange-100 border border-orange-200 text-orange-700 text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer transition">
        <span class="material-symbols-rounded text-sm" style="font-size:15px">photo_camera</span>
        Ganti Foto
        <input type="file" name="foto" accept="image/*" form="form-profil" onchange="previewFoto(this)">
      </label>
      <p id="file-name" class="text-xs text-slate-400 mt-1 truncate max-w-[180px] text-center"></p>

      <h2 class="text-xl font-bold text-gray-800 mt-3">
        <?= htmlspecialchars($user['nama_lengkap']) ?>
      </h2>

      <span class="mt-1.5 inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
        <span class="material-symbols-rounded" style="font-size:13px;font-variation-settings:'FILL' 1">badge</span>
        <?= htmlspecialchars($user['role_name']) ?>
      </span>

      <!-- Info kecil -->
      <div class="flex gap-4 mt-4 text-center">
        <div class="px-4 py-2 bg-slate-50 rounded-2xl">
          <p class="text-xs text-slate-400">Email</p>
          <p class="text-xs font-semibold text-slate-700 mt-0.5 max-w-[120px] truncate"><?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="px-4 py-2 bg-slate-50 rounded-2xl">
          <p class="text-xs text-slate-400">Status</p>
          <p class="text-xs font-semibold text-green-600 mt-0.5 flex items-center gap-0.5">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Aktif
          </p>
        </div>
      </div>

    </div>

    <div class="section-divider"></div>

    <!-- ===================== FORM ===================== -->
    <?php if ($success_msg): ?>
    <div class="toast mx-6 mt-5 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl text-sm font-medium">
      <span class="material-symbols-rounded text-green-500" style="font-size:18px">check_circle</span>
      <?= htmlspecialchars($success_msg) ?>
    </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
    <div class="toast mx-6 mt-5 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium">
      <span class="material-symbols-rounded text-red-400" style="font-size:18px">error</span>
      <?= htmlspecialchars($error_msg) ?>
    </div>
    <?php endif; ?>

    <form id="form-profil" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">

      <!-- Nama Lengkap -->
      <div class="space-y-1.5">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <span class="material-symbols-rounded" style="font-size:14px">person</span>
          Nama Lengkap
        </label>
        <input
          type="text"
          name="nama_lengkap"
          value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
          placeholder="Masukkan nama lengkap"
          class="field-input w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm text-slate-800 font-medium transition"
          required>
      </div>

      <!-- Email -->
      <div class="space-y-1.5">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <span class="material-symbols-rounded" style="font-size:14px">mail</span>
          Email
        </label>
        <input
          type="email"
          name="email"
          value="<?= htmlspecialchars($user['email']) ?>"
          placeholder="Masukkan email"
          class="field-input w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm text-slate-800 font-medium transition"
          required>
      </div>

      <div class="section-divider my-2"></div>

      <!-- Password -->
      <div class="space-y-1.5">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <span class="material-symbols-rounded" style="font-size:14px">lock</span>
          Ganti Password
        </label>
        <div class="relative">
          <input
            type="password"
            id="input-password"
            name="password"
            placeholder="Kosongkan jika tidak ingin mengganti"
            class="field-input w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 pr-11 text-sm text-slate-800 font-medium transition">
          <button
            type="button"
            onclick="togglePassword()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-orange-500 transition">
            <span id="eye-icon" class="material-symbols-rounded" style="font-size:20px">visibility</span>
          </button>
        </div>
        <p class="text-xs text-slate-400 pl-1">Minimal 6 karakter</p>
      </div>

      <!-- Tombol Simpan -->
      <div class="pt-2 space-y-3">
        <button
          type="submit"
          class="w-full bg-orange-600 hover:bg-orange-700 active:scale-[.98] text-white py-3.5 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-200">
          <span class="material-symbols-rounded" style="font-size:18px;font-variation-settings:'FILL' 1">save</span>
          Simpan Perubahan
        </button>

        <a href="dashboard.php"
          class="w-full border border-slate-200 hover:bg-slate-50 text-slate-500 py-3 rounded-2xl font-semibold text-sm transition flex items-center justify-center gap-2">
          <span class="material-symbols-rounded" style="font-size:16px">arrow_back</span>
          Kembali ke Dashboard
        </a>
      </div>

    </form>

  </div>

  <!-- Info keamanan -->
  <div class="flex items-center justify-center gap-1.5 mt-4 mb-2">
    <span class="material-symbols-rounded text-slate-300" style="font-size:14px">shield</span>
    <p class="text-xs text-slate-400">Data Anda dienkripsi & aman</p>
  </div>

</div>

<?php include 'navbar_karyawan.php'; ?>

<!-- ===================== SCRIPTS ===================== -->
<script>
  // Preview foto sebelum upload
  function previewFoto(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('preview-foto').src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
      document.getElementById('file-name').textContent = input.files[0].name;
    }
  }

  // Toggle show/hide password
  function togglePassword() {
    const inp  = document.getElementById('input-password');
    const icon = document.getElementById('eye-icon');
    if (inp.type === 'password') {
      inp.type   = 'text';
      icon.textContent = 'visibility_off';
    } else {
      inp.type   = 'password';
      icon.textContent = 'visibility';
    }
  }

  // Auto-dismiss toast
  setTimeout(() => {
    document.querySelectorAll('.toast').forEach(el => {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    });
  }, 3500);
</script>
</body>
</html>