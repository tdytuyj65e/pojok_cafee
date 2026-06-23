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
<title>Profil Saya | Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

<style>
  :root{
    --espresso:#3C2415;
    --terracotta:#ea580c;
    --terracotta-light:#fb923c;
    --cream:#FFF9F4;
  }

  *{ box-sizing:border-box; }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background-color: var(--cream);
    background-image: radial-gradient(circle at 1px 1px, rgba(60,36,21,0.05) 1px, transparent 0);
    background-size: 20px 20px;
  }

  .font-display{ font-family:'Fredoka', sans-serif; }

  .material-symbols-rounded {
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
  }

  /* ===== Header ===== */
  .header-bg {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 50%, #fb923c 110%);
    position: relative;
    overflow: hidden;
  }
  .header-bg::before {
    content: '';
    position: absolute;
    width: 280px; height: 280px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
    top: -90px; right: -70px;
  }
  .header-bg::after {
    content: '';
    position: absolute;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    bottom: -70px; left: -40px;
  }
  .bean-float{
    position:absolute;
    opacity:0.14;
    color:#fff;
  }
  .back-btn {
    background: rgba(255,255,255,0.16);
    backdrop-filter: blur(4px);
    transition: background .15s ease, transform .15s ease;
  }
  .back-btn:hover{ background: rgba(255,255,255,0.26); }
  .back-btn:active{ transform: scale(0.92); }

  /* ===== Photo ring ===== */
  .photo-ring {
    background: conic-gradient(from 0deg, #ea580c, #fb923c, #fed7aa, #ea580c);
    padding: 3px;
    border-radius: 9999px;
    animation: spin-slow 7s linear infinite;
    box-shadow: 0 12px 28px -8px rgba(234,88,12,0.45);
  }
  @keyframes spin-slow { to { transform: rotate(360deg); } }
  .photo-inner {
    border-radius: 9999px;
    overflow: hidden;
    background: white;
    padding: 3px;
  }
  .photo-cam-btn{
    box-shadow: 0 6px 16px -4px rgba(234,88,12,0.55);
    transition: transform .15s ease;
  }
  .photo-cam-btn:hover{ transform: scale(1.08); }
  .photo-cam-btn:active{ transform: scale(0.95); }

  /* ===== Tabs ===== */
  .tab-btn {
    transition: all .2s ease;
    position: relative;
  }
  .tab-btn.active {
    color: #c2410c;
  }
  .tab-btn.active::after {
    content:'';
    position:absolute;
    bottom:-1px; left:50%;
    transform: translateX(-50%);
    width: 28px; height: 3px;
    background: #ea580c;
    border-radius: 4px 4px 0 0;
  }
  .tab-panel { display:none; }
  .tab-panel.active { display:block; animation: fadeIn .25s ease; }
  @keyframes fadeIn { from{opacity:0; transform:translateY(4px);} to{opacity:1; transform:translateY(0);} }

  /* ===== Input ===== */
  .field-input {
    transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
  }
  .field-input:focus {
    outline: none;
    border-color: #ea580c;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(234,88,12,0.12);
  }

  /* ===== Strength meter ===== */
  .strength-bar{
    height:5px;
    border-radius:4px;
    background:#F0DDC8;
    overflow:hidden;
  }
  .strength-fill{
    height:100%;
    width:0%;
    border-radius:4px;
    transition: width .25s ease, background-color .25s ease;
  }

  /* ===== File input ===== */
  .file-btn input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
  }

  /* ===== Toast ===== */
  @keyframes slideUp {
    from { transform: translateY(14px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
  }
  .toast { animation: slideUp .35s ease both; }

  /* ===== Card ===== */
  .main-card {
    box-shadow: 0 24px 60px -12px rgba(234,88,12,0.15), 0 4px 20px rgba(0,0,0,0.05);
  }

  .stat-pill{
    background: linear-gradient(145deg,#fff7ed,#ffedd5);
    border: 1px solid #fed7aa;
  }

  /* ===== Save button ===== */
  .btn-save {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    box-shadow: 0 12px 24px -8px rgba(234,88,12,0.5);
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .btn-save:hover{ transform: translateY(-1px); box-shadow: 0 16px 28px -8px rgba(234,88,12,0.55); }
  .btn-save:active{ transform: translateY(0); }
  .btn-save:disabled{ opacity:.7; cursor:not-allowed; transform:none; }

  .spinner{
    width:16px; height:16px;
    border:2.5px solid rgba(255,255,255,0.45);
    border-top-color:#fff;
    border-radius:50%;
    animation: spin .7s linear infinite;
  }
  @keyframes spin{ to{ transform: rotate(360deg); } }

  .divider-soft{
    height:1px;
    background: linear-gradient(90deg, transparent, #fed7aa, transparent);
  }
</style>
</head>
<body class="min-h-screen pb-28">

<!-- ===================== HEADER ===================== -->
<div class="header-bg h-48">
  <svg class="bean-float" style="top:14px; left:14%; width:22px;" viewBox="0 0 24 24" fill="currentColor"><ellipse cx="12" cy="12" rx="9" ry="11" transform="rotate(20 12 12)"/></svg>
  <svg class="bean-float" style="top:30px; right:18%; width:16px;" viewBox="0 0 24 24" fill="currentColor"><ellipse cx="12" cy="12" rx="9" ry="11" transform="rotate(-25 12 12)"/></svg>

  <div class="relative z-10 max-w-md mx-auto px-5 pt-6 flex items-center justify-between text-white">
    <a href="dashboard.php" class="back-btn w-10 h-10 rounded-full flex items-center justify-center">
      <span class="material-symbols-rounded text-xl">arrow_back</span>
    </a>
    <div class="text-center">
      <p class="text-orange-100 text-[11px] font-semibold tracking-[0.18em] uppercase">Akun Saya</p>
      <h1 class="font-display text-xl font-semibold mt-0.5">Profil</h1>
    </div>
    <div class="w-10"></div>
  </div>
</div>

<!-- ===================== MAIN CARD ===================== -->
<div class="max-w-md mx-auto px-4 -mt-20 relative z-10">

  <div class="main-card bg-white rounded-3xl overflow-hidden">

    <!-- Avatar Section -->
    <div class="flex flex-col items-center pt-7 pb-6 px-6 bg-gradient-to-b from-orange-50/60 to-white">

      <div class="relative">
        <div class="photo-ring">
          <div class="photo-inner w-28 h-28">
            <img
              id="preview-foto"
              src="<?= $foto ?>"
              class="w-full h-full object-cover rounded-full"
              alt="Foto Profil">
          </div>
        </div>

        <!-- Tombol kamera mengambang -->
        <label class="file-btn photo-cam-btn absolute bottom-0 right-0 w-9 h-9 bg-orange-600 rounded-full flex items-center justify-center cursor-pointer border-2 border-white">
          <span class="material-symbols-rounded text-white" style="font-size:17px">photo_camera</span>
          <input type="file" name="foto" accept="image/*" form="form-profil" onchange="previewFoto(this)">
        </label>
      </div>

      <p id="file-name" class="text-xs text-orange-500 font-medium mt-2 truncate max-w-[200px] text-center"></p>

      <h2 class="text-xl font-bold text-gray-800 mt-3">
        <?= htmlspecialchars($user['nama_lengkap']) ?>
      </h2>

      <span class="mt-1.5 inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
        <span class="material-symbols-rounded" style="font-size:13px">badge</span>
        <?= htmlspecialchars($user['role_name']) ?>
      </span>

      <!-- Info kecil -->
      <div class="flex gap-3 mt-5 w-full">
        <div class="stat-pill flex-1 px-3 py-2.5 rounded-2xl text-center">
          <p class="text-[10px] text-orange-400 font-semibold uppercase tracking-wide">Email</p>
          <p class="text-xs font-bold text-slate-700 mt-0.5 truncate"><?= htmlspecialchars($user['email'] ?: '—') ?></p>
        </div>
        <div class="stat-pill flex-1 px-3 py-2.5 rounded-2xl text-center">
          <p class="text-[10px] text-orange-400 font-semibold uppercase tracking-wide">Status</p>
          <p class="text-xs font-bold text-green-600 mt-0.5 flex items-center justify-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Aktif
          </p>
        </div>
      </div>

    </div>

    <!-- ===================== TABS ===================== -->
    <div class="flex border-b border-slate-100 px-6">
      <button type="button" onclick="switchTab('info')" id="tabBtn-info"
        class="tab-btn active flex-1 py-3.5 text-sm font-semibold text-slate-400 flex items-center justify-center gap-1.5">
        <span class="material-symbols-rounded" style="font-size:17px">person</span>
        Info Profil
      </button>
      <button type="button" onclick="switchTab('security')" id="tabBtn-security"
        class="tab-btn flex-1 py-3.5 text-sm font-semibold text-slate-400 flex items-center justify-center gap-1.5">
        <span class="material-symbols-rounded" style="font-size:17px">lock</span>
        Keamanan
      </button>
    </div>

    <!-- ===================== ALERTS ===================== -->
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

    <!-- ===================== FORM ===================== -->
    <form id="form-profil" method="POST" enctype="multipart/form-data" class="p-6">

      <!-- TAB: INFO PROFIL -->
      <div class="tab-panel active space-y-4" id="tabPanel-info">

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
            class="field-input w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm text-slate-800 font-medium"
            required>
        </div>

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
            class="field-input w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm text-slate-800 font-medium"
            required>
        </div>

        <div class="bg-orange-50/70 border border-orange-100 rounded-2xl px-4 py-3 flex items-start gap-2.5 mt-2">
          <span class="material-symbols-rounded text-orange-400 flex-shrink-0" style="font-size:18px">info</span>
          <p class="text-xs text-orange-600 leading-relaxed">Nama dan email ini akan tampil di seluruh transaksi yang kamu buat.</p>
        </div>

      </div>

      <!-- TAB: KEAMANAN -->
      <div class="tab-panel space-y-4" id="tabPanel-security">

        <div class="space-y-1.5">
          <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
            <span class="material-symbols-rounded" style="font-size:14px">key</span>
            Password Baru
          </label>
          <div class="relative">
            <input
              type="password"
              id="input-password"
              name="password"
              placeholder="Kosongkan jika tidak ingin mengganti"
              class="field-input w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 pr-11 text-sm text-slate-800 font-medium">
            <button
              type="button"
              onclick="togglePassword()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-orange-500 transition">
              <span id="eye-icon" class="material-symbols-rounded" style="font-size:20px">visibility</span>
            </button>
          </div>

          <div class="strength-bar mt-2">
            <div class="strength-fill" id="strengthFill"></div>
          </div>
          <p class="text-xs text-slate-400 pl-1" id="strengthLabel">Minimal 6 karakter.</p>
        </div>

        <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 flex items-start gap-2.5 mt-2">
          <span class="material-symbols-rounded text-slate-400 flex-shrink-0" style="font-size:18px">shield</span>
          <p class="text-xs text-slate-500 leading-relaxed">Gunakan kombinasi huruf, angka, dan simbol agar akunmu lebih aman.</p>
        </div>

      </div>

      <!-- Tombol Simpan -->
      <div class="pt-5 space-y-3">
        <button
          type="submit"
          id="submitBtn"
          class="btn-save w-full text-white py-3.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2">
          <span class="material-symbols-rounded" style="font-size:18px" id="saveIcon">save</span>
          <span id="btnLabel">Simpan Perubahan</span>
        </button>
      </div>

    </form>

  </div>

  <!-- Info keamanan -->
  <div class="flex items-center justify-center gap-1.5 mt-5 mb-2">
    <span class="material-symbols-rounded text-slate-300" style="font-size:14px">verified_user</span>
    <p class="text-xs text-slate-400">Data Anda dienkripsi & aman</p>
  </div>

</div>

<?php include 'navbar_karyawan.php'; ?>

<!-- ===================== SCRIPTS ===================== -->
<script>
  // ===== Tab switch =====
  function switchTab(name) {
    ['info', 'security'].forEach(t => {
      document.getElementById('tabBtn-' + t).classList.toggle('active', t === name);
      document.getElementById('tabPanel-' + t).classList.toggle('active', t === name);
    });
  }

  // ===== Preview foto sebelum upload =====
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

  // ===== Toggle show/hide password =====
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

  // ===== Indikator kekuatan password =====
  const passInput = document.getElementById('input-password');
  const strengthFill = document.getElementById('strengthFill');
  const strengthLabel = document.getElementById('strengthLabel');

  passInput.addEventListener('input', () => {
    const val = passInput.value;
    let score = 0;

    if (val.length >= 6) score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
      { width: '0%',   color: '#F0DDC8', label: 'Minimal 6 karakter.' },
      { width: '20%',  color: '#E0664D', label: 'Lemah — tambahkan karakter lagi.' },
      { width: '40%',  color: '#E0664D', label: 'Lemah — tambahkan karakter lagi.' },
      { width: '60%',  color: '#ea580c', label: 'Cukup — bisa lebih kuat lagi.' },
      { width: '80%',  color: '#C9A227', label: 'Kuat.' },
      { width: '100%', color: '#3FA34D', label: 'Sangat kuat!' },
    ];

    const lvl = val.length === 0 ? levels[0] : levels[score];
    strengthFill.style.width = lvl.width;
    strengthFill.style.backgroundColor = lvl.color;
    strengthLabel.textContent = lvl.label;
  });

  // ===== Submit state =====
  const form = document.getElementById('form-profil');
  const submitBtn = document.getElementById('submitBtn');
  const btnLabel = document.getElementById('btnLabel');
  const saveIcon = document.getElementById('saveIcon');

  form.addEventListener('submit', () => {
    submitBtn.disabled = true;
    saveIcon.remove();
    btnLabel.textContent = 'Menyimpan...';
    submitBtn.insertAdjacentHTML('afterbegin', '<span class="spinner"></span>');
  });

  // ===== Auto-dismiss toast =====
  setTimeout(() => {
    document.querySelectorAll('.toast').forEach(el => {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    });
  }, 3500);
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>