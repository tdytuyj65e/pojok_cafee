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
   PROSES KATEGORI
========================== */

// Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_kategori') {
    $nama_kat = trim($_POST['nama_kategori'] ?? '');
    if ($nama_kat !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama_kat);
        $stmt->execute()
            ? $_SESSION['success'] = "Kategori <strong>$nama_kat</strong> berhasil ditambahkan."
            : $_SESSION['error']   = "Gagal menambahkan kategori.";
    } else {
        $_SESSION['error'] = "Nama kategori tidak boleh kosong.";
    }
    header("Location: produk.php?tab=kategori"); exit;
}

// Edit Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_kategori') {
    $kid      = (int)$_POST['id'];
    $nama_kat = trim($_POST['nama_kategori'] ?? '');
    if ($nama_kat !== '') {
        $stmt = $conn->prepare("UPDATE categories SET nama_kategori=? WHERE id=?");
        $stmt->bind_param("si", $nama_kat, $kid);
        $stmt->execute()
            ? $_SESSION['success'] = "Kategori berhasil diperbarui."
            : $_SESSION['error']   = "Gagal memperbarui kategori.";
    }
    header("Location: produk.php?tab=kategori"); exit;
}

// Hapus Kategori
if (isset($_GET['hapus_kategori'])) {
    $kid = (int)$_GET['hapus_kategori'];
    // Set category_id = NULL untuk produk yang pakai kategori ini
    $conn->query("UPDATE products SET category_id = NULL WHERE category_id = $kid");
    $conn->query("DELETE FROM categories WHERE id = $kid");
    $_SESSION['success'] = "Kategori berhasil dihapus.";
    header("Location: produk.php?tab=kategori"); exit;
}

/* ==========================
   PROSES PRODUK
========================== */

// Tambah Produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {

    $nama     = trim($_POST['nama_produk'] ?? '');
    $harga    = (float)($_POST['harga'] ?? 0);
    $stok     = (int)($_POST['stok'] ?? 0);
    $stok_min = (int)($_POST['stok_minimum'] ?? 5);
    $cat_id   = (int)($_POST['category_id'] ?? 0) ?: null;
    $foto     = null;

    if (!empty($_FILES['foto']['name'])) {

        $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid('prod_') . '.' . strtolower($ext);

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "../uploads/" . $foto
        );
    }

    $stmt = $conn->prepare("
        INSERT INTO products
        (category_id, nama_produk, harga, stok, stok_minimum, foto)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isdiss",
        $cat_id,
        $nama,
        $harga,
        $stok,
        $stok_min,
        $foto
    );

    if ($stmt->execute()) {

        $product_id = $conn->insert_id;

        if ($stok > 0) {

            $stok_lama = 0;
            $stok_baru = $stok;
            $qty       = $stok;
            $jenis     = 'masuk';
            $ket       = 'Stok awal produk baru';

            $log = $conn->prepare("
                INSERT INTO stock_logs
                (product_id, stok_lama, stok_baru, qty, jenis, keterangan, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $log->bind_param(
                "iiiiss",
                $product_id,
                $stok_lama,
                $stok_baru,
                $qty,
                $jenis,
                $ket
            );

            $log->execute();
        }

        $_SESSION['success'] =
            "Produk <strong>$nama</strong> berhasil ditambahkan.";

    } else {

        $_SESSION['error'] =
            "Gagal menambahkan produk.";
    }

    header("Location: produk.php");
    exit;
}

// Tambah Stok (restock manual)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah_stok') {

    $id  = (int)($_POST['id'] ?? 0);
    $qty = (int)($_POST['qty_tambah'] ?? 0);

    if ($id > 0 && $qty > 0) {

        $old = $conn->query("SELECT stok, nama_produk FROM products WHERE id = $id")->fetch_assoc();

        if ($old) {
            $stok_lama = (int)$old['stok'];
            $stok_baru = $stok_lama + $qty;
            $nama_produk = $old['nama_produk'];

            $upd = $conn->prepare("UPDATE products SET stok = ? WHERE id = ?");
            $upd->bind_param("ii", $stok_baru, $id);
            $upd->execute();

            $ket = 'Penambahan stok manual';

            $log = $conn->prepare("
                INSERT INTO stock_logs
                (product_id, stok_lama, stok_baru, qty, jenis, keterangan, created_at)
                VALUES (?, ?, ?, ?, 'masuk', ?, NOW())
            ");
            $log->bind_param("iiiis", $id, $stok_lama, $stok_baru, $qty, $ket);
if(!$log->execute()){
    $_SESSION['error'] = $log->error;
} else {
    $_SESSION['success'] =
        "Sukses! Stok barang berhasil ditambahkan | " .
        "Produk: $nama_produk | " .
        "Qty: $qty | " .
        "Stok Lama: $stok_lama | " .
        "Stok Baru: $stok_baru";
}
        }
    } else {
        $_SESSION['error'] = "Jumlah stok harus lebih dari 0.";
    }

    header("Location: produk.php");
    exit;
}

// Edit Produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {

    $id       = (int)$_POST['id'];
    $nama     = trim($_POST['nama_produk'] ?? '');
    $harga    = (float)($_POST['harga'] ?? 0);
    $stok     = (int)$_POST['stok'] ?? 0;
    $stok_min = (int)$_POST['stok_minimum'] ?? 5;
    $cat_id   = (int)($_POST['category_id'] ?? 0) ?: null;

    $old = $conn->query("
        SELECT foto, stok
        FROM products
        WHERE id = $id
    ")->fetch_assoc();

    $foto      = $old['foto'];
    $stok_lama = (int)$old['stok'];

    if (!empty($_FILES['foto']['name'])) {

        $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid('prod_') . '.' . strtolower($ext);

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            "../uploads/" . $foto
        );
    }

    $stmt = $conn->prepare("
        UPDATE products
        SET
            category_id=?,
            nama_produk=?,
            harga=?,
            stok=?,
            stok_minimum=?,
            foto=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "isdiisi",
        $cat_id,
        $nama,
        $harga,
        $stok,
        $stok_min,
        $foto,
        $id
    );

    if ($stmt->execute()) {

        if ($stok != $stok_lama) {

            $selisih = abs($stok - $stok_lama);

            $jenis = ($stok > $stok_lama)
                ? 'masuk'
                : 'keluar';

            $ket = 'Penyesuaian stok produk';

            $log = $conn->prepare("
                INSERT INTO stock_logs
                (product_id, stok_lama, stok_baru, qty, jenis, keterangan, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $log->bind_param(
                "iiiiss",
                $id,
                $stok_lama,
                $stok,
                $selisih,
                $jenis,
                $ket
            );

            $log->execute();
        }

        $_SESSION['success'] =
            "Produk <strong>$nama</strong> berhasil diperbarui.";

    } else {

        $_SESSION['error'] =
            "Gagal memperbarui produk.";
    }

    header("Location: produk.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];

    mysqli_begin_transaction($conn);

    try {

        // hapus relasi dulu
        $conn->query("DELETE FROM stock_logs WHERE product_id = $id");
        $conn->query("DELETE FROM transaction_details WHERE product_id = $id");

        // baru hapus produk
        $conn->query("DELETE FROM products WHERE id = $id");

        mysqli_commit($conn);

        $_SESSION['success'] = "Produk berhasil dihapus";

    } catch (Exception $e) {

        mysqli_rollback($conn);
        $_SESSION['error'] = "Gagal hapus: " . $e->getMessage();
    }

    header("Location: produk.php");
    exit;
}

/* ==========================
   FETCH DATA
========================== */

// Kategori
$kategori_query = $conn->query("
    SELECT c.*, COUNT(p.id) jumlah_produk
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.nama_kategori ASC
");
$kategori_list = $kategori_query->fetch_all(MYSQLI_ASSOC);

// Produk (dengan filter kategori & pencarian)
$cari      = $_GET['cari']        ?? '';
$filter_kat = (int)($_GET['kategori'] ?? 0);

$where_parts = ["p.nama_produk LIKE CONCAT('%', ?, '%')"];
$bind_types  = "s";
$bind_vals   = [$cari];

if ($filter_kat > 0) {
    $where_parts[] = "p.category_id = ?";
    $bind_types   .= "i";
    $bind_vals[]   = $filter_kat;
}

$where_sql = implode(" AND ", $where_parts);

$stmt = $conn->prepare("
    SELECT p.*, c.nama_kategori
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $where_sql
    ORDER BY p.id DESC
");
$stmt->bind_param($bind_types, ...$bind_vals);
$stmt->execute();
$produk_list  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$total_produk = count($produk_list);

// Tab aktif
$tab = $_GET['tab'] ?? 'produk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Produk & Kategori – Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#16a34a">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * { font-family: 'Poppins', sans-serif; }

  .panel-overlay { visibility:hidden; opacity:0; transition:opacity .28s ease,visibility .28s ease; }
  .panel-overlay.open { visibility:visible; opacity:1; }
  .panel-drawer { transform:translateX(100%); transition:transform .32s cubic-bezier(.4,0,.2,1); }
  .panel-overlay.open .panel-drawer { transform:translateX(0); }
  .panel-drawer::-webkit-scrollbar { width:5px; }
  .panel-drawer::-webkit-scrollbar-thumb { background:#fb923c; border-radius:10px; }

  .prod-card { transition:transform .2s ease,box-shadow .2s ease; }
  .prod-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(0,0,0,.1); }

  .form-input:focus { outline:none; border-color:#fb923c; box-shadow:0 0 0 3px rgba(251,146,60,.2); }
  .form-input-blue:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.2); }
  .form-input-green:focus { outline:none; border-color:#22c55e; box-shadow:0 0 0 3px rgba(34,197,94,.2); }

  @keyframes pulse-red { 0%,100%{opacity:1} 50%{opacity:.65} }
  .badge-low { animation:pulse-red 2s infinite; }

  .tab-btn { transition:all .2s ease; }
  .tab-btn.active { background:#f97316; color:#fff; }

  .kat-card { transition:all .2s ease; }
  .kat-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.08); transform:translateY(-2px); }
</style>
</head>

<body class="bg-slate-100 min-h-screen">
<?php include "navbar_owner.php"; ?>

<div class="lg:ml-64 min-h-screen">

  <!-- HEADER -->
  <div class="bg-gradient-to-r from-orange-500 via-orange-500 to-amber-500 px-8 py-8 text-white shadow-lg">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-orange-200 text-sm font-medium uppercase tracking-widest mb-1">Pojok Kafe</p>
        <h1 class="text-3xl font-extrabold tracking-tight">Manajemen Produk ☕</h1>
        <p class="text-orange-100 mt-1 text-sm">Kelola produk dan kategori dengan mudah</p>
      </div>
      <div class="flex gap-3 flex-wrap">
        <?php if($tab !== 'kategori'): ?>
        <button onclick="bukaPanel('tambah')"
          class="flex items-center gap-2 bg-white text-orange-600 font-bold px-5 py-2.5 rounded-2xl shadow-md hover:bg-orange-50 transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Tambah Produk
        </button>
        <?php else: ?>
        <button onclick="bukaPanel('tambah-kat')"
          class="flex items-center gap-2 bg-white text-orange-600 font-bold px-5 py-2.5 rounded-2xl shadow-md hover:bg-orange-50 transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Tambah Kategori
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="p-6 max-w-[1600px]">

    <!-- ALERT -->
    <?php if(isset($_SESSION['success'])): ?>
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl mb-5 shadow-sm">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span><?= $_SESSION['success'] ?></span>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl mb-5 shadow-sm">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      <span><?= $_SESSION['error'] ?></span>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- TABS -->
    <div class="bg-white rounded-2xl shadow-sm p-1.5 mb-6 inline-flex gap-1">
      <a href="produk.php?tab=produk"
        class="tab-btn px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-orange-50 <?= $tab !== 'kategori' ? 'active' : '' ?>">
        ☕ Produk
        <span class="ml-1.5 bg-orange-100 text-orange-600 text-xs px-2 py-0.5 rounded-full <?= $tab !== 'kategori' ? '!bg-white/30 !text-white' : '' ?>">
          <?= $total_produk ?>
        </span>
      </a>
      <a href="produk.php?tab=kategori"
        class="tab-btn px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-orange-50 <?= $tab === 'kategori' ? 'active' : '' ?>">
        🏷️ Kategori
        <span class="ml-1.5 bg-orange-100 text-orange-600 text-xs px-2 py-0.5 rounded-full <?= $tab === 'kategori' ? '!bg-white/30 !text-white' : '' ?>">
          <?= count($kategori_list) ?>
        </span>
      </a>
    </div>

    <!-- ===========================
         TAB: PRODUK
    =========================== -->
    <?php if($tab !== 'kategori'): ?>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-3xl shadow-sm p-5 mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">

        <form method="GET" class="flex flex-wrap gap-2 flex-1">
          <input type="hidden" name="tab" value="produk">

          <!-- Search -->
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>"
              placeholder="Cari produk..."
              class="form-input border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm bg-gray-50 w-60">
          </div>

          <!-- Filter Kategori -->
          <select name="kategori" onchange="this.form.submit()"
            class="form-input border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50">
            <option value="0">Semua Kategori</option>
            <?php foreach($kategori_list as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $filter_kat == $k['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama_kategori']) ?>
              (<?= $k['jumlah_produk'] ?>)
            </option>
            <?php endforeach; ?>
          </select>

          <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">
            Cari
          </button>
          <?php if($cari || $filter_kat): ?>
          <a href="produk.php" class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm transition">
            Reset
          </a>
          <?php endif; ?>
        </form>

        <div class="flex gap-2 flex-wrap">
          <span class="bg-orange-50 text-orange-700 px-4 py-2 rounded-xl text-sm font-semibold">
            📦 <?= $total_produk ?> Produk
          </span>
          <?php
            $low_stock = array_filter($produk_list, fn($p) => $p['stok'] <= $p['stok_minimum']);
            if(count($low_stock) > 0):
          ?>
          <span class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-semibold badge-low">
            ⚠️ <?= count($low_stock) ?> Stok Menipis
          </span>
          <?php endif; ?>
        </div>

      </div>

      <!-- Chip filter kategori aktif -->
      <?php if(count($kategori_list) > 0): ?>
      <div class="flex gap-2 flex-wrap mt-4 pt-4 border-t border-gray-100">
        <a href="produk.php"
          class="px-3 py-1 rounded-full text-xs font-semibold transition <?= !$filter_kat ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600' ?>">
          Semua
        </a>
        <?php foreach($kategori_list as $k): ?>
        <a href="produk.php?kategori=<?= $k['id'] ?>"
          class="px-3 py-1 rounded-full text-xs font-semibold transition <?= $filter_kat == $k['id'] ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600' ?>">
          <?= htmlspecialchars($k['nama_kategori']) ?>
          <span class="opacity-70">(<?= $k['jumlah_produk'] ?>)</span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- GRID PRODUK -->
    <?php if($total_produk > 0): ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach($produk_list as $p): ?>
    <div class="prod-card bg-white rounded-3xl shadow overflow-hidden flex flex-col">

      <!-- Foto -->
      <div class="relative">
        <?php if(!empty($p['foto'])): ?>
          <img src="../uploads/<?= htmlspecialchars($p['foto']) ?>"
               alt="<?= htmlspecialchars($p['nama_produk']) ?>"
               class="w-full h-48 object-cover">
        <?php else: ?>
          <div class="h-48 bg-gradient-to-br from-orange-50 to-amber-100 flex items-center justify-center text-5xl">☕</div>
        <?php endif; ?>

        <?php if(!empty($p['nama_kategori'])): ?>
        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-orange-600 text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
          <?= htmlspecialchars($p['nama_kategori']) ?>
        </span>
        <?php endif; ?>

        <?php if($p['stok'] == 0): ?>
        <span class="absolute top-3 right-3 bg-gray-800 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
          Habis
        </span>
        <?php elseif($p['stok'] <= $p['stok_minimum']): ?>
        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm badge-low">
          Stok Tipis
        </span>
        <?php endif; ?>
      </div>

      <!-- Content -->
      <div class="p-5 flex flex-col flex-1">
        <h2 class="font-bold text-base text-gray-800 leading-snug mb-3">
          <?= htmlspecialchars($p['nama_produk']) ?>
        </h2>

        <div class="space-y-2 mb-4 flex-1">
          <div class="flex justify-between items-center">
            <span class="text-gray-400 text-sm">Harga</span>
            <span class="font-bold text-green-600">Rp <?= number_format($p['harga'],0,',','.') ?></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-400 text-sm">Stok</span>
            <span class="font-semibold <?= $p['stok'] == 0 ? 'text-gray-500' : ($p['stok'] <= $p['stok_minimum'] ? 'text-red-500' : 'text-gray-700') ?>">
              <?= $p['stok'] ?> unit
            </span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-400 text-sm">Min. Stok</span>
            <span class="text-gray-500 text-sm"><?= $p['stok_minimum'] ?> unit</span>
          </div>
        </div>

        <!-- Progress bar stok -->
        <?php
          $max_bar  = max($p['stok_minimum'] * 3, 1);
          $pct      = min(100, round($p['stok'] / $max_bar * 100));
          $bar_clr  = $p['stok'] == 0 ? 'bg-gray-300' : ($p['stok'] <= $p['stok_minimum'] ? 'bg-red-400' : 'bg-green-400');
        ?>
        <div class="mb-4">
          <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full <?= $bar_clr ?> rounded-full" style="width:<?= $pct ?>%"></div>
          </div>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-3 gap-2">
          <button onclick="bukaTambahStok(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama_produk'])) ?>', <?= $p['stok'] ?>)"
            class="flex items-center justify-center gap-1 bg-green-50 hover:bg-green-100 text-green-600 font-semibold py-2.5 rounded-xl text-sm transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Stok
          </button>

          <button onclick="bukaEdit(<?= htmlspecialchars(json_encode($p)) ?>)"
            class="flex items-center justify-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold py-2.5 rounded-xl text-sm transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
          </button>

          <a href="#"
             onclick="confirmHapus(<?= $p['id'] ?>); return false;"
             class="flex items-center justify-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 rounded-xl text-sm transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
            </svg>
            Hapus
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="bg-white rounded-3xl shadow p-16 text-center">
      <div class="text-7xl mb-4">📦</div>
      <h3 class="text-xl font-bold text-gray-700 mb-2">Produk Tidak Ditemukan</h3>
      <p class="text-gray-400 mb-6">
        <?php if($cari || $filter_kat): ?>
          Coba ubah kata kunci atau filter kategori.
        <?php else: ?>
          Mulai tambah produk pertamamu!
        <?php endif; ?>
      </p>
      <button onclick="bukaPanel('tambah')"
        class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl transition">
        + Tambah Produk
      </button>
    </div>
    <?php endif; ?>

    <!-- ===========================
         TAB: KATEGORI
    =========================== -->
    <?php else: ?>

    <!-- Info -->
    <div class="bg-blue-50 border border-blue-100 text-blue-700 rounded-2xl px-5 py-3.5 mb-6 flex items-center gap-3 text-sm">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      Menghapus kategori tidak akan menghapus produk di dalamnya — produk akan menjadi tanpa kategori.
    </div>

    <?php if(count($kategori_list) > 0): ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php
      $kat_colors = [
        ['bg-orange-50','text-orange-600','bg-orange-500'],
        ['bg-blue-50','text-blue-600','bg-blue-500'],
        ['bg-purple-50','text-purple-600','bg-purple-500'],
        ['bg-green-50','text-green-600','bg-green-500'],
        ['bg-pink-50','text-pink-600','bg-pink-500'],
        ['bg-teal-50','text-teal-600','bg-teal-500'],
        ['bg-amber-50','text-amber-600','bg-amber-500'],
        ['bg-indigo-50','text-indigo-600','bg-indigo-500'],
      ];
      foreach($kategori_list as $idx => $k):
        $clr = $kat_colors[$idx % count($kat_colors)];
    ?>
    <div class="kat-card bg-white rounded-3xl shadow p-6 flex flex-col gap-4">

      <div class="flex items-center gap-4">
        <div class="w-14 h-14 <?= $clr[0] ?> rounded-2xl flex items-center justify-center text-2xl font-bold <?= $clr[1] ?> flex-shrink-0">
          <?= strtoupper(substr($k['nama_kategori'], 0, 1)) ?>
        </div>
        <div class="min-w-0">
          <h3 class="font-bold text-gray-800 text-base truncate"><?= htmlspecialchars($k['nama_kategori']) ?></h3>
          <p class="text-sm text-gray-400 mt-0.5"><?= $k['jumlah_produk'] ?> produk</p>
        </div>
      </div>

      <!-- Bar produk -->
      <?php
        $max_kat = max(array_column($kategori_list, 'jumlah_produk') ?: [1]);
        $kat_pct = $max_kat > 0 ? min(100, round($k['jumlah_produk'] / $max_kat * 100)) : 0;
      ?>
      <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full <?= $clr[2] ?> rounded-full" style="width:<?= $kat_pct ?>%"></div>
      </div>

      <!-- Actions -->
      <div class="flex gap-2 pt-1">
        <button onclick="bukaEditKategori(<?= $k['id'] ?>, '<?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>')"
          class="flex-1 flex items-center justify-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold py-2 rounded-xl text-sm transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          Edit
        </button>
        <a href="?hapus_kategori=<?= $k['id'] ?>&tab=kategori"
          onclick="return confirm('Hapus kategori <?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>? Produk tidak akan ikut terhapus.')"
          class="flex-1 flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 rounded-xl text-sm transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
          Hapus
        </a>
        <a href="produk.php?kategori=<?= $k['id'] ?>"
          class="flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-500 p-2 rounded-xl transition"
          title="Lihat produk">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </a>
      </div>

    </div>
    <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="bg-white rounded-3xl shadow p-16 text-center">
      <div class="text-7xl mb-4">🏷️</div>
      <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Kategori</h3>
      <p class="text-gray-400 mb-6">Tambahkan kategori untuk mengorganisir produkmu.</p>
      <button onclick="bukaPanel('tambah-kat')"
        class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl transition">
        + Tambah Kategori Pertama
      </button>
    </div>
    <?php endif; ?>

    <?php endif; // end tab check ?>

  </div><!-- /p-6 -->
</div><!-- /lg:ml-64 -->


<!-- ============================================================
     PANEL: TAMBAH PRODUK
============================================================ -->
<div id="panel-tambah" class="panel-overlay fixed inset-0 z-50 flex justify-end">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" onclick="tutupPanel('tambah')"></div>
  <div class="panel-drawer relative bg-white w-full max-w-lg h-full overflow-y-auto shadow-2xl flex flex-col">

    <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
      <div>
        <p class="text-orange-100 text-xs uppercase tracking-widest">Form Baru</p>
        <h2 class="text-xl font-bold mt-0.5">Tambah Produk</h2>
      </div>
      <button onclick="tutupPanel('tambah')" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col">
      <input type="hidden" name="action" value="tambah">

      <div class="p-6 space-y-5 flex-1">

        <!-- Foto -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Produk</label>
          <label for="foto-tambah" class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer bg-gray-50 hover:border-orange-400 hover:bg-orange-50 transition overflow-hidden">
            <img id="preview-tambah" src="" alt="" class="absolute inset-0 w-full h-full object-cover opacity-0 rounded-2xl">
            <div id="placeholder-tambah" class="text-center">
              <div class="text-3xl mb-1">📷</div>
              <p class="text-sm text-gray-400">Klik untuk pilih foto</p>
              <p class="text-xs text-gray-300 mt-0.5">JPG, PNG, WEBP</p>
            </div>
          </label>
          <input type="file" id="foto-tambah" name="foto" accept="image/*" class="hidden" onchange="previewFoto(this,'preview-tambah','placeholder-tambah')">
        </div>

        <!-- Nama -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Produk <span class="text-red-400">*</span></label>
          <input type="text" name="nama_produk" required placeholder="Contoh: Kopi Susu Gula Aren"
            class="form-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
        </div>

        <!-- Kategori dengan shortcut tambah -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-sm font-semibold text-gray-700">Kategori</label>
            <button type="button" onclick="tutupPanel('tambah'); bukaPanel('tambah-kat');"
              class="text-xs text-orange-500 hover:text-orange-600 font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
              Buat kategori baru
            </button>
          </div>
          <select name="category_id" class="form-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
            <option value="">— Tanpa Kategori —</option>
            <?php foreach($kategori_list as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?> (<?= $k['jumlah_produk'] ?> produk)</option>
            <?php endforeach; ?>
          </select>
          <?php if(count($kategori_list) === 0): ?>
          <p class="text-xs text-amber-500 mt-1.5">💡 Belum ada kategori. <button type="button" onclick="tutupPanel('tambah'); bukaPanel('tambah-kat');" class="underline font-semibold">Buat sekarang</button></p>
          <?php endif; ?>
        </div>

        <!-- Harga -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Harga (Rp) <span class="text-red-400">*</span></label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
            <input type="number" name="harga" required min="0" step="500" placeholder="0"
              class="form-input w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm bg-gray-50 transition">
          </div>
        </div>

        <!-- Stok -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok Awal</label>
            <input type="number" name="stok" min="0" value="0"
              class="form-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok Minimum</label>
            <input type="number" name="stok_minimum" min="0" value="5"
              class="form-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
          </div>
        </div>
        <p class="text-xs text-gray-400">Notifikasi muncul saat stok ≤ stok minimum.</p>

      </div>

      <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex gap-3 flex-shrink-0">
        <button type="button" onclick="tutupPanel('tambah')" class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-100 transition text-sm">Batal</button>
        <button type="submit" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition text-sm shadow-sm">Simpan Produk</button>
      </div>
    </form>
  </div>
</div>


<!-- ============================================================
     PANEL: EDIT PRODUK
============================================================ -->
<div id="panel-edit" class="panel-overlay fixed inset-0 z-50 flex justify-end">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" onclick="tutupPanel('edit')"></div>
  <div class="panel-drawer relative bg-white w-full max-w-lg h-full overflow-y-auto shadow-2xl flex flex-col">

    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
      <div>
        <p class="text-blue-100 text-xs uppercase tracking-widest">Perbarui Data</p>
        <h2 class="text-xl font-bold mt-0.5">Edit Produk</h2>
      </div>
      <button onclick="tutupPanel('edit')" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit-id">

      <div class="p-6 space-y-5 flex-1">

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Produk</label>
          <label for="foto-edit" class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer bg-gray-50 hover:border-blue-400 hover:bg-blue-50 transition overflow-hidden">
            <img id="preview-edit" src="" alt="" class="absolute inset-0 w-full h-full object-cover rounded-2xl" style="opacity:0">
            <div id="placeholder-edit" class="text-center">
              <div class="text-3xl mb-1">📷</div>
              <p class="text-sm text-gray-400">Klik untuk ganti foto</p>
              <p class="text-xs text-gray-300 mt-0.5">Kosongkan untuk pertahankan foto lama</p>
            </div>
          </label>
          <input type="file" id="foto-edit" name="foto" accept="image/*" class="hidden" onchange="previewFoto(this,'preview-edit','placeholder-edit')">
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Produk <span class="text-red-400">*</span></label>
          <input type="text" name="nama_produk" id="edit-nama" required class="form-input-blue w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-sm font-semibold text-gray-700">Kategori</label>
            <button type="button" onclick="tutupPanel('edit'); bukaPanel('tambah-kat');"
              class="text-xs text-blue-500 hover:text-blue-600 font-semibold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
              Buat kategori baru
            </button>
          </div>
          <select name="category_id" id="edit-kategori" class="form-input-blue w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
            <option value="">— Tanpa Kategori —</option>
            <?php foreach($kategori_list as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?> (<?= $k['jumlah_produk'] ?> produk)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Harga (Rp) <span class="text-red-400">*</span></label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
            <input type="number" name="harga" id="edit-harga" required min="0" step="500"
              class="form-input-blue w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm bg-gray-50 transition">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok</label>
            <input type="number" name="stok" id="edit-stok" min="0" class="form-input-blue w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stok Minimum</label>
            <input type="number" name="stok_minimum" id="edit-stok-min" min="0" class="form-input-blue w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
          </div>
        </div>
        <p class="text-xs text-gray-400">Pakai field ini hanya untuk koreksi data. Untuk menambah stok harian, gunakan tombol <strong>"Stok"</strong> di kartu produk.</p>

      </div>

      <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex gap-3 flex-shrink-0">
        <button type="button" onclick="tutupPanel('edit')" class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-100 transition text-sm">Batal</button>
        <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition text-sm shadow-sm">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>


<!-- ============================================================
     PANEL: TAMBAH STOK
============================================================ -->
<div id="panel-tambah-stok" class="panel-overlay fixed inset-0 z-50 flex justify-end">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" onclick="tutupPanel('tambah-stok')"></div>
  <div class="panel-drawer relative bg-white w-full max-w-md h-full overflow-y-auto shadow-2xl flex flex-col">

    <div class="bg-gradient-to-r from-green-500 to-teal-500 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
      <div>
        <p class="text-green-100 text-xs uppercase tracking-widest">Restock</p>
        <h2 class="text-xl font-bold mt-0.5">Tambah Stok</h2>
      </div>
      <button onclick="tutupPanel('tambah-stok')" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" class="flex-1 flex flex-col">
      <input type="hidden" name="action" value="tambah_stok">
      <input type="hidden" name="id" id="stok-id">

      <div class="p-6 flex-1 space-y-5">

        <div class="bg-gray-50 rounded-2xl p-4 text-center">
          <p class="text-sm text-gray-400">Produk</p>
          <p id="stok-nama-produk" class="font-bold text-gray-800 text-lg">-</p>
          <p class="text-sm text-gray-400 mt-2">Stok saat ini</p>
          <p id="stok-sekarang" class="font-bold text-green-600 text-2xl">0 unit</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Jumlah Stok yang Ditambahkan <span class="text-red-400">*</span>
          </label>
          <input type="number" name="qty_tambah" id="stok-qty" required min="1" placeholder="Contoh: 20"
            class="form-input-green w-full border border-gray-200 rounded-xl px-4 py-3 text-base font-semibold bg-gray-50 transition">
          <p class="text-xs text-gray-400 mt-1.5">Masukkan jumlah barang masuk, bukan total stok akhir.</p>
        </div>

        <div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-sm text-green-700">
          Stok akan otomatis ditambahkan ke jumlah yang ada dan tercatat sebagai <strong>barang masuk</strong> di laporan.
        </div>

      </div>

      <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex gap-3 flex-shrink-0">
        <button type="button" onclick="tutupPanel('tambah-stok')" class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-100 transition text-sm">Batal</button>
        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition text-sm shadow-sm">Tambah Stok</button>
      </div>
    </form>
  </div>
</div>


<!-- ============================================================
     PANEL: TAMBAH KATEGORI
============================================================ -->
<div id="panel-tambah-kat" class="panel-overlay fixed inset-0 z-50 flex justify-end">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" onclick="tutupPanel('tambah-kat')"></div>
  <div class="panel-drawer relative bg-white w-full max-w-md h-full overflow-y-auto shadow-2xl flex flex-col">

    <div class="bg-gradient-to-r from-green-500 to-teal-500 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
      <div>
        <p class="text-green-100 text-xs uppercase tracking-widest">Organisasi Produk</p>
        <h2 class="text-xl font-bold mt-0.5">Tambah Kategori</h2>
      </div>
      <button onclick="tutupPanel('tambah-kat')" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" class="flex-1 flex flex-col">
      <input type="hidden" name="action" value="tambah_kategori">

      <div class="p-6 flex-1 space-y-5">

        <!-- Ikon/preview inisial -->
        <div class="flex justify-center">
          <div id="kat-preview-icon"
            class="w-20 h-20 rounded-2xl bg-green-100 flex items-center justify-center text-3xl font-bold text-green-600 tracking-tight">
            ?
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-400">*</span></label>
          <input type="text" name="nama_kategori" id="input-nama-kat" required
            placeholder="Contoh: Minuman Panas, Makanan Ringan..."
            oninput="updateKatPreview(this.value)"
            class="form-input-green w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
          <p class="text-xs text-gray-400 mt-1.5">Nama kategori harus unik.</p>
        </div>

        <!-- Daftar kategori yang ada -->
        <?php if(count($kategori_list) > 0): ?>
        <div>
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Kategori yang sudah ada</p>
          <div class="flex flex-wrap gap-2">
            <?php foreach($kategori_list as $k): ?>
            <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1.5 rounded-full font-medium">
              <?= htmlspecialchars($k['nama_kategori']) ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex gap-3 flex-shrink-0">
        <button type="button" onclick="tutupPanel('tambah-kat')" class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-100 transition text-sm">Batal</button>
        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition text-sm shadow-sm">Buat Kategori</button>
      </div>
    </form>
  </div>
</div>


<!-- ============================================================
     PANEL: EDIT KATEGORI
============================================================ -->
<div id="panel-edit-kat" class="panel-overlay fixed inset-0 z-50 flex justify-end">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" onclick="tutupPanel('edit-kat')"></div>
  <div class="panel-drawer relative bg-white w-full max-w-md h-full overflow-y-auto shadow-2xl flex flex-col">

    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
      <div>
        <p class="text-purple-100 text-xs uppercase tracking-widest">Perbarui Kategori</p>
        <h2 class="text-xl font-bold mt-0.5">Edit Kategori</h2>
      </div>
      <button onclick="tutupPanel('edit-kat')" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" class="flex-1 flex flex-col">
      <input type="hidden" name="action" value="edit_kategori">
      <input type="hidden" name="id" id="edit-kat-id">

      <div class="p-6 flex-1 space-y-5">

        <div class="flex justify-center">
          <div id="kat-edit-icon"
            class="w-20 h-20 rounded-2xl bg-purple-100 flex items-center justify-center text-3xl font-bold text-purple-600">
            ?
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-400">*</span></label>
          <input type="text" name="nama_kategori" id="edit-kat-nama" required
            oninput="updateKatEditPreview(this.value)"
            class="form-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-50 transition">
        </div>

      </div>

      <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex gap-3 flex-shrink-0">
        <button type="button" onclick="tutupPanel('edit-kat')" class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-100 transition text-sm">Batal</button>
        <button type="submit" class="flex-1 bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 rounded-xl transition text-sm shadow-sm">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-hapus" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">

    <h2 class="text-lg font-bold text-red-600 mb-2">⚠️ Hapus Produk</h2>
    <p class="text-gray-600 text-sm mb-5">
      Produk yang dihapus tidak dapat dikembalikan. Apakah Anda yakin?
    </p>

    <div class="flex gap-3">
      <button onclick="tutupHapus()" class="flex-1 py-2 rounded-xl border hover:bg-gray-100">
        Batal
      </button>
      <a id="btn-hapus" href="#" class="flex-1 py-2 rounded-xl bg-red-500 text-white text-center hover:bg-red-600">
        Ya, Hapus
      </a>
    </div>

  </div>
</div>

<script>
/* ===== Panel ===== */
function bukaPanel(name) {
  document.getElementById('panel-' + name).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function tutupPanel(name) {
  document.getElementById('panel-' + name).classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    ['tambah','edit','tambah-kat','edit-kat','tambah-stok'].forEach(tutupPanel);
  }
});

/* ===== Foto Preview ===== */
function previewFoto(input, previewId, placeholderId) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById(previewId);
    const ph  = document.getElementById(placeholderId);
    img.src = e.target.result;
    img.style.opacity = '1';
    ph.style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}

/* ===== Edit Produk ===== */
function bukaEdit(p) {
  document.getElementById('edit-id').value       = p.id;
  document.getElementById('edit-nama').value     = p.nama_produk;
  document.getElementById('edit-harga').value    = p.harga;
  document.getElementById('edit-stok').value     = p.stok;
  document.getElementById('edit-stok-min').value = p.stok_minimum;

  const sel = document.getElementById('edit-kategori');
  for (let i = 0; i < sel.options.length; i++)
    sel.options[i].selected = (sel.options[i].value == p.category_id);

  const img = document.getElementById('preview-edit');
  const ph  = document.getElementById('placeholder-edit');
  if (p.foto) {
    img.src = '../uploads/' + p.foto;
    img.style.opacity = '1';
    ph.style.display = 'none';
  } else {
    img.src = '';
    img.style.opacity = '0';
    ph.style.display = '';
  }
  document.getElementById('foto-edit').value = '';
  bukaPanel('edit');
}

/* ===== Tambah Stok ===== */
function bukaTambahStok(id, nama, stokSekarang) {
  document.getElementById('stok-id').value = id;
  document.getElementById('stok-nama-produk').textContent = nama;
  document.getElementById('stok-sekarang').textContent = stokSekarang + ' unit';
  document.getElementById('stok-qty').value = '';
  bukaPanel('tambah-stok');
}

/* ===== Edit Kategori ===== */
function bukaEditKategori(id, nama) {
  document.getElementById('edit-kat-id').value   = id;
  document.getElementById('edit-kat-nama').value = nama;
  updateKatEditPreview(nama);
  bukaPanel('edit-kat');
}

/* ===== Preview inisial ===== */
function updateKatPreview(val) {
  const el = document.getElementById('kat-preview-icon');
  el.textContent = val.trim() ? val.trim().charAt(0).toUpperCase() : '?';
}
function updateKatEditPreview(val) {
  const el = document.getElementById('kat-edit-icon');
  el.textContent = val.trim() ? val.trim().charAt(0).toUpperCase() : '?';
}

function confirmHapus(id) {
  document.getElementById('btn-hapus').href = '?hapus=' + id;
  document.getElementById('modal-hapus').classList.remove('hidden');
  document.getElementById('modal-hapus').classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function tutupHapus() {
  document.getElementById('modal-hapus').classList.add('hidden');
  document.getElementById('modal-hapus').classList.remove('flex');
  document.body.style.overflow = '';
}
</script>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>