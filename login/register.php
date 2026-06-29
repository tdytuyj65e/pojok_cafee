<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

$errors = [];
$old = [
    'username'     => '',
    'nama_lengkap' => '',
    'email'        => '',
];

/* ------------------------------------------------------------------ */
/* Cegah pendaftaran owner ganda. Hanya boleh ada SATU owner.          */
/* ------------------------------------------------------------------ */
$ownerExists = false;
$stmtCheck = $pdo->query("
    SELECT COUNT(*) AS jumlah
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE r.name = 'owner'
");
if ($stmtCheck->fetch()['jumlah'] > 0) {
    $ownerExists = true;
}

/* ------------------------------------------------------------------ */
/* Proses form                                                         */
/* ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$ownerExists) {

    $old['username']     = trim($_POST['username'] ?? '');
    $old['nama_lengkap']  = trim($_POST['nama_lengkap'] ?? '');
    $old['email']         = trim($_POST['email'] ?? '');
    $password             = $_POST['password'] ?? '';
    $confirmPassword      = $_POST['confirm_password'] ?? '';

    if ($old['nama_lengkap'] === '') {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi.';
    }

    if ($old['username'] === '') {
        $errors['username'] = 'Username wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.]{4,50}$/', $old['username'])) {
        $errors['username'] = 'Username 4-50 karakter, hanya huruf, angka, titik, dan underscore.';
    }

    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    if (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Konfirmasi password tidak sama.';
    }

    // Cek username sudah dipakai
    if (empty($errors['username'])) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$old['username']]);
        if ($stmt->fetch()) {
            $errors['username'] = 'Username sudah digunakan, pilih yang lain.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'owner' LIMIT 1");
            $roleStmt->execute();
            $role = $roleStmt->fetch();

            if (!$role) {
                throw new Exception("Role 'owner' tidak ditemukan di tabel roles.");
            }

            $insert = $pdo->prepare("
                INSERT INTO users (role_id, username, password, nama_lengkap, email, status)
                VALUES (:role_id, :username, :password, :nama_lengkap, :email, 'aktif')
            ");
            $insert->execute([
                ':role_id'      => $role['id'],
                ':username'     => $old['username'],
                ':password'     => password_hash($password, PASSWORD_DEFAULT),
                ':nama_lengkap' => $old['nama_lengkap'],
                ':email'        => $old['email'] !== '' ? $old['email'] : null,
            ]);

            $pdo->commit();

            $_SESSION['register_success'] = 'Akun owner berhasil dibuat. Silakan login.';
            header('Location: login.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['umum'] = 'Pendaftaran gagal: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Owner — Pojok Kafe</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          paper:   '#FBF4E8',
          ink:     '#2A1A12',
          copper:  '#C8772E',
          copperd: '#A75F1F',
          sage:    '#6B7860',
          muted:   '#8A7A6B',
        },
        fontFamily: {
          display: ['"Space Grotesk"', 'sans-serif'],
          body: ['Inter', 'sans-serif'],
          mono: ['"Space Mono"', 'monospace'],
        },
      },
    },
  };
</script>
<style>
  body { background-color: #2A1A12; }

  .perforation {
    background-image: radial-gradient(circle, #2A1A12 3px, transparent 3.5px);
    background-size: 18px 100%;
    background-repeat: repeat-x;
    background-position: 0 center;
    height: 8px;
  }

  .stamp {
    transform: rotate(-7deg);
    border: 2px solid #C8772E;
    color: #C8772E;
  }

  .steam {
    stroke: #C8772E;
    stroke-width: 2;
    fill: none;
    stroke-linecap: round;
    opacity: 0.55;
  }

  .field-input {
    background-color: #FBF4E8;
    border: 1.5px solid #D9C9B0;
  }
  .field-input:focus {
    outline: none;
    border-color: #C8772E;
    box-shadow: 0 0 0 3px rgba(200,119,46,0.18);
  }
</style>
</head>
<body class="min-h-screen font-body text-ink">

<div class="min-h-screen flex items-center justify-center p-4 sm:p-8">
  <div class="w-full max-w-4xl grid md:grid-cols-2 rounded-2xl overflow-hidden shadow-2xl">

    <!-- PANEL KIRI -->
    <div class="relative bg-ink text-paper px-8 sm:px-10 py-12 flex flex-col justify-between">
      <svg class="absolute top-8 right-8 w-16 h-20" viewBox="0 0 60 80" aria-hidden="true">
        <path class="steam" d="M20 70 C 10 55, 30 50, 20 35 C 10 20, 30 15, 22 2"/>
        <path class="steam" d="M38 70 C 28 55, 48 50, 38 35 C 28 20, 48 15, 40 2" opacity="0.3"/>
      </svg>

      <div>
        <span class="inline-block font-mono text-xs tracking-widest text-copper/80 mb-6">POJOK KAFE &middot; SISTEM KASIR</span>
        <h1 class="font-display font-bold text-3xl sm:text-4xl leading-tight mb-4">
          Buka kafemu,<br/>kelola dari sini.
        </h1>
        <p class="text-paper/70 text-sm leading-relaxed max-w-xs">
          Akun owner adalah kunci utama: mengatur produk, karyawan, dan seluruh transaksi kafemu.
        </p>
      </div>

      <div class="stamp inline-flex self-start items-center gap-2 px-3 py-1.5 rounded-sm font-mono text-xs tracking-widest uppercase mt-10">
        Owner Access
      </div>
    </div>

    <!-- PANEL KANAN: FORM -->
    <div class="bg-paper">
      <div class="perforation"></div>

      <div class="px-8 sm:px-10 py-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="font-display font-bold text-xl text-ink">Daftar Owner</h2>
          <span class="font-mono text-[11px] text-muted">NO. 001</span>
        </div>

        <?php if ($ownerExists): ?>
          <div class="border border-sage/40 bg-sage/10 text-sage rounded-lg p-4 text-sm leading-relaxed">
            Akun owner untuk Pojok Kafe sudah terdaftar.<br>
            Silakan <a href="login.php" class="font-semibold underline">login di sini</a>.
          </div>

        <?php else: ?>

          <?php if (!empty($errors['umum'])): ?>
            <div class="border border-red-300 bg-red-50 text-red-700 rounded-lg p-3 text-sm mb-5">
              <?= htmlspecialchars($errors['umum']) ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="register.php" class="space-y-4" novalidate>

            <div>
              <label for="nama_lengkap" class="block text-xs font-mono uppercase tracking-wide text-muted mb-1.5">Nama Lengkap</label>
              <input
                type="text" id="nama_lengkap" name="nama_lengkap"
                value="<?= htmlspecialchars($old['nama_lengkap']) ?>"
                class="field-input w-full rounded-lg px-3.5 py-2.5 text-sm text-ink placeholder:text-muted/60"
                placeholder="cth. Andi Pratama">
              <?php if (!empty($errors['nama_lengkap'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($errors['nama_lengkap']) ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label for="username" class="block text-xs font-mono uppercase tracking-wide text-muted mb-1.5">Username</label>
              <input
                type="text" id="username" name="username"
                value="<?= htmlspecialchars($old['username']) ?>"
                class="field-input w-full rounded-lg px-3.5 py-2.5 text-sm text-ink placeholder:text-muted/60"
                placeholder="cth. owner_kafe">
              <?php if (!empty($errors['username'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($errors['username']) ?></p>
              <?php endif; ?>
            </div>

            <div>
              <label for="email" class="block text-xs font-mono uppercase tracking-wide text-muted mb-1.5">Email <span class="text-muted/60">(opsional)</span></label>
              <input
                type="email" id="email" name="email"
                value="<?= htmlspecialchars($old['email']) ?>"
                class="field-input w-full rounded-lg px-3.5 py-2.5 text-sm text-ink placeholder:text-muted/60"
                placeholder="owner@pojokkafe.com">
              <?php if (!empty($errors['email'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($errors['email']) ?></p>
              <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label for="password" class="block text-xs font-mono uppercase tracking-wide text-muted mb-1.5">Password</label>
                <input
                  type="password" id="password" name="password"
                  class="field-input w-full rounded-lg px-3.5 py-2.5 text-sm text-ink"
                  placeholder="min. 6 karakter">
                <?php if (!empty($errors['password'])): ?>
                  <p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($errors['password']) ?></p>
                <?php endif; ?>
              </div>
              <div>
                <label for="confirm_password" class="block text-xs font-mono uppercase tracking-wide text-muted mb-1.5">Ulangi Password</label>
                <input
                  type="password" id="confirm_password" name="confirm_password"
                  class="field-input w-full rounded-lg px-3.5 py-2.5 text-sm text-ink"
                  placeholder="ulangi password">
                <?php if (!empty($errors['confirm_password'])): ?>
                  <p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($errors['confirm_password']) ?></p>
                <?php endif; ?>
              </div>
            </div>

            <button
              type="submit"
              class="w-full bg-copper hover:bg-copperd text-paper font-display font-semibold text-sm py-3 rounded-lg transition-colors mt-2">
              Daftar sebagai Owner
            </button>

            <p class="text-center text-xs text-muted pt-1">
              Sudah punya akun? <a href="login.php" class="text-copper font-medium hover:underline">Login</a>
            </p>
          </form>

        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

</body>
</html>