<?php
session_start();
include "../koneksi.php";

/* ===== CEK LOGIN ===== */
$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

/* ===== TAMBAH PRODUK ===== */
if (isset($_POST['tambah'])) {

    $nama  = mysqli_real_escape_string($conn, trim($_POST['nama_produk']));
    $harga = (int) $_POST['harga'];
    $stok  = (int) $_POST['stok'];
    $stok_min = (int) ($_POST['stok_minimum'] ?? 5);

    $newName = null;
    if (!empty($_FILES['foto']['name'])) {
        $folder  = "../uploads/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        $ext     = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed) && move_uploaded_file($_FILES['foto']['tmp_name'], $folder . ($newName = uniqid() . "_" . time() . "." . $ext)));
    }

    $stmtP = mysqli_prepare($conn,
        "INSERT INTO products (nama_produk, harga, stok, stok_minimum, foto) VALUES (?,?,?,?,?)"
    );
    mysqli_stmt_bind_param($stmtP, "siiss", $nama, $harga, $stok, $stok_min, $newName);
    mysqli_stmt_execute($stmtP);

    header("Location: produk.php");
    exit;
}

/* ===== HAPUS PRODUK ===== */
if (isset($_POST['hapus_produk'])) {
    $pid = (int) $_POST['pid'];
    // Ambil nama foto dulu untuk dihapus dari disk
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM products WHERE id=$pid"));
    if (!empty($r['foto']) && file_exists("../uploads/" . $r['foto'])) {
        unlink("../uploads/" . $r['foto']);
    }
    mysqli_query($conn, "DELETE FROM products WHERE id=$pid");
    header("Location: produk.php");
    exit;
}

/* ===== ADD TO CART ===== */
if (isset($_POST['add_cart'])) {
    $id = (int) $_POST['id'];
    $stmtC = mysqli_prepare($conn, "SELECT * FROM products WHERE id=? AND stok > 0");
    mysqli_stmt_bind_param($stmtC, "i", $id);
    mysqli_stmt_execute($stmtC);
    $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC));

    if ($p) {
        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = [
                'id'    => $p['id'],
                'nama'  => $p['nama_produk'],
                'harga' => (float) $p['harga'],
                'qty'   => 0,
            ];
        }
        // Jangan melebihi stok
        $stokMax = (int) $p['stok'];
        if ($_SESSION['cart'][$id]['qty'] < $stokMax) {
            $_SESSION['cart'][$id]['qty']++;
            $_SESSION['cart'][$id]['subtotal'] = $_SESSION['cart'][$id]['qty'] * (float) $p['harga'];
        }
    }

    // Jika AJAX return JSON, jika POST biasa redirect ke transaksi
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        $total = array_sum(array_column($_SESSION['cart'], 'qty'));
        echo json_encode(['cartCount' => $total, 'ok' => (bool) $p]);
        exit;
    }

    header("Location: transaksi.php");
    exit;
}

/* ===== DATA PRODUK ===== */
$search    = trim($_GET['q'] ?? '');
$kategori  = (int) ($_GET['cat'] ?? 0);

$sql = "SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE 1=1";
if ($search !== '') $sql .= " AND p.nama_produk LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
if ($kategori > 0)  $sql .= " AND p.category_id = $kategori";
$sql .= " ORDER BY p.id DESC";
$query = mysqli_query($conn, $sql);

// Kategori untuk filter
$qKat = mysqli_query($conn, "SELECT * FROM categories ORDER BY nama_kategori ASC");

/* ===== USER ===== */
$stmtU = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmtU, "i", $user_id);
mysqli_stmt_execute($stmtU);
$user    = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtU));
$fotoUser = (!empty($user['foto']) && file_exists("../uploads/" . $user['foto']))
    ? "../uploads/" . $user['foto']
    : "https://ui-avatars.com/api/?name=" . urlencode($user['nama_lengkap']) . "&background=8e4a0e&color=fff";

/* ===== CART COUNT ===== */
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $c) $cartCount += $c['qty'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title>Produk | Pojok Kafe</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,500,1,0" rel="stylesheet"/>
<style>
* { font-family:'Plus Jakarta Sans',sans-serif; }
.material-symbols-rounded { font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24; user-select:none; }
body { background:#fff8f5; min-height:100dvh; }

/* Header */
.header-bg {
    background:linear-gradient(135deg,#7c3a08 0%,#8e4a0e 40%,#a9632c 100%);
    position:relative; overflow:hidden;
}
.header-bg::before {
    content:''; position:absolute; width:180px; height:180px;
    background:rgba(255,255,255,0.05); border-radius:50%;
    top:-50px; right:-40px;
}

/* Cards */
.product-card { transition:transform .12s ease, box-shadow .12s ease; }
.product-card:active { transform:scale(0.97); }

/* Input focus */
.input-field:focus { outline:none; border-color:#8e4a0e; box-shadow:0 0 0 3px rgba(142,74,14,.1); }

/* Filter chip */
.filter-chip { transition:all .15s ease; }
.filter-chip.active { background:#8e4a0e; color:#fff; border-color:#8e4a0e; }

/* Add to cart button bounce */
@keyframes bounce-once { 0%,100%{transform:scale(1)} 50%{transform:scale(0.85)} }
.bounce { animation:bounce-once .2s ease; }

/* Toast */
@keyframes slideUp { from{transform:translateY(10px);opacity:0} to{transform:translateY(0);opacity:1} }
.toast { animation:slideUp .3s ease both; }
</style>
</head>
<body class="pb-28">

<!-- ====== HEADER ====== -->
<header class="header-bg text-white">
  <div class="relative z-10 max-w-md mx-auto px-4 pt-10 pb-5">

    <div class="flex items-center justify-between mb-4">
      <a href="dashboard.php" class="w-9 h-9 flex items-center justify-center rounded-full bg-white/15 hover:bg-white/25 transition">
        <span class="material-symbols-rounded text-xl">arrow_back</span>
      </a>
      <h1 class="font-extrabold text-lg">Pilih Produk</h1>

      <!-- Cart button -->
      <a href="transaksi.php" class="relative w-9 h-9 flex items-center justify-center rounded-full bg-white/15 hover:bg-white/25 transition" id="cartBtn">
        <span class="material-symbols-rounded text-xl">shopping_cart</span>
        <span id="cartBadge"
              class="<?= $cartCount > 0 ? '' : 'hidden' ?> absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
          <?= $cartCount ?>
        </span>
      </a>
    </div>

    <!-- Search -->
    <div class="relative">
      <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-orange-300 text-lg">search</span>
      <input
        id="searchInput"
        type="text"
        placeholder="Cari produk..."
        value="<?= htmlspecialchars($search) ?>"
        class="w-full bg-white/15 backdrop-blur text-white placeholder-orange-200 border border-white/20 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:bg-white/25 transition">
    </div>

  </div>
</header>

<!-- ====== FILTER KATEGORI ====== -->
<div class="max-w-md mx-auto px-4 pt-3 pb-1 flex gap-2 overflow-x-auto hide-sb">
  <a href="produk.php"
     class="filter-chip flex-shrink-0 px-4 py-1.5 rounded-full border text-xs font-semibold transition <?= $kategori === 0 && $search === '' ? 'active' : 'bg-white border-orange-200 text-slate-600' ?>">
    Semua
  </a>
  <?php while ($kat = mysqli_fetch_assoc($qKat)): ?>
  <a href="produk.php?cat=<?= $kat['id'] ?>"
     class="filter-chip flex-shrink-0 px-4 py-1.5 rounded-full border text-xs font-semibold transition <?= $kategori === (int)$kat['id'] ? 'active' : 'bg-white border-orange-200 text-slate-600' ?>">
    <?= htmlspecialchars($kat['nama_kategori']) ?>
  </a>
  <?php endwhile; ?>
</div>

<!-- ====== MAIN ====== -->
<main class="max-w-md mx-auto px-4 pt-3 space-y-3" id="productList">

  <?php
  $jumlahProduk = 0;
  while ($row = mysqli_fetch_assoc($query)):
    $jumlahProduk++;
    $fotoProduk = !empty($row['foto']) ? "../uploads/" . htmlspecialchars($row['foto']) : "https://via.placeholder.com/100x100?text=No+Img";
    $stok = (int) $row['stok'];
    $habis = $stok === 0;
    $stokBadge = $stok === 0
        ? 'bg-red-100 text-red-700'
        : ($stok <= (int)$row['stok_minimum'] ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700');
    $stokLabel = $stok === 0 ? 'Habis' : "Stok $stok";
  ?>

  <div class="product-card bg-white rounded-2xl flex items-center gap-3 p-3 shadow-sm border border-orange-50"
       data-name="<?= htmlspecialchars(mb_strtolower($row['nama_produk'])) ?>">

    <!-- Foto -->
    <img src="<?= $fotoProduk ?>"
         class="w-16 h-16 rounded-xl object-cover bg-orange-50 flex-shrink-0"
         onerror="this.src='https://via.placeholder.com/100'">

    <!-- Info -->
    <div class="flex-1 min-w-0">
      <h3 class="text-sm font-bold text-slate-800 truncate"><?= htmlspecialchars($row['nama_produk']) ?></h3>
      <?php if (!empty($row['nama_kategori'])): ?>
      <p class="text-[10px] text-slate-400 mt-0.5"><?= htmlspecialchars($row['nama_kategori']) ?></p>
      <?php endif; ?>
      <p class="text-sm font-extrabold text-[#8e4a0e] mt-1">Rp <?= number_format($row['harga'], 0, ',', '.') ?></p>
    </div>

    <!-- Kanan: Stok + Tombol -->
    <div class="flex flex-col items-end gap-2 flex-shrink-0">
      <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full <?= $stokBadge ?>"><?= $stokLabel ?></span>

      <?php if (!$habis): ?>
      <form method="POST" class="add-cart-form">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <button type="submit" name="add_cart"
                class="add-btn w-9 h-9 rounded-full bg-[#8e4a0e] text-white flex items-center justify-center active:scale-90 transition shadow"
                aria-label="Tambah ke keranjang">
          <span class="material-symbols-rounded text-lg">add_shopping_cart</span>
        </button>
      </form>
      <?php else: ?>
      <button disabled class="w-9 h-9 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center cursor-not-allowed">
        <span class="material-symbols-rounded text-lg">remove_shopping_cart</span>
      </button>
      <?php endif; ?>
    </div>

  </div>

  <?php endwhile; ?>

  <?php if ($jumlahProduk === 0): ?>
  <div class="bg-white border border-dashed border-orange-200 rounded-2xl py-14 text-center mt-4">
    <span class="material-symbols-rounded text-5xl text-orange-200">inventory_2</span>
    <p class="text-sm font-semibold text-slate-600 mt-3">Produk tidak ditemukan</p>
    <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau tambah produk baru.</p>
    <button onclick="openModal()" class="mt-4 px-5 py-2 bg-[#8e4a0e] text-white text-xs font-bold rounded-xl">+ Tambah Produk</button>
  </div>
  <?php endif; ?>

  <p id="noResult" class="hidden text-center text-xs text-slate-400 py-6">Produk tidak ditemukan.</p>

</main>

<!-- ====== FAB TAMBAH PRODUK ====== -->
<button onclick="openModal()"
        class="fixed bottom-24 right-4 z-40 w-14 h-14 bg-[#8e4a0e] text-white rounded-2xl flex items-center justify-center shadow-xl active:scale-95 transition">
  <span class="material-symbols-rounded text-2xl">add</span>
</button>

<!-- ====== TOAST ====== -->
<div id="toast"
     class="toast hidden fixed top-20 left-1/2 -translate-x-1/2 z-50 bg-[#8e4a0e] text-white text-xs font-semibold px-5 py-2.5 rounded-full shadow-lg flex items-center gap-2">
  <span class="material-symbols-rounded text-base">check_circle</span>
  <span id="toastMsg">Ditambahkan ke keranjang</span>
</div>

<!-- ====== MODAL TAMBAH PRODUK ====== -->
<div id="modal" class="hidden fixed inset-0 bg-black/50 flex items-end justify-center z-[60] px-0"
     onclick="if(event.target===this)closeModal()">
  <div class="bg-white w-full max-w-md rounded-t-3xl p-6 pb-24 space-y-4 max-h-[90vh] overflow-y-auto">

    <div class="flex items-center justify-between">
      <h3 class="text-base font-extrabold text-slate-800">Tambah Produk</h3>
      <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
        <span class="material-symbols-rounded text-slate-500 text-lg">close</span>
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

      <div>
        <label class="text-xs font-semibold text-slate-500 mb-1 block">Nama Produk *</label>
        <input name="nama_produk" required placeholder="Contoh: Kopi Susu Gula Aren"
               class="input-field w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm transition">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-semibold text-slate-500 mb-1 block">Harga (Rp) *</label>
          <input name="harga" type="number" min="0" required placeholder="0"
                 class="input-field w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm transition">
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-500 mb-1 block">Stok *</label>
          <input name="stok" type="number" min="0" required placeholder="0"
                 class="input-field w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm transition">
        </div>
      </div>

      <div>
        <label class="text-xs font-semibold text-slate-500 mb-1 block">Stok Minimum</label>
        <input name="stok_minimum" type="number" min="0" value="5"
               class="input-field w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm transition">
      </div>

      <div>
        <label class="text-xs font-semibold text-slate-500 mb-1 block">Foto Produk</label>
        <label for="fotoInput"
               class="flex items-center gap-3 border-2 border-dashed border-orange-200 bg-orange-50/50 rounded-xl px-4 py-3 cursor-pointer hover:bg-orange-100/40 transition">
          <img id="fotoPreview" class="w-10 h-10 rounded-lg object-cover hidden">
          <span class="material-symbols-rounded text-orange-300 text-2xl" id="fotoIcon">image</span>
          <span id="fotoLabel" class="text-xs text-slate-400">Pilih gambar (jpg, png, webp)</span>
        </label>
        <input id="fotoInput" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" class="hidden">
      </div>

      <button name="tambah" class="w-full bg-[#8e4a0e] hover:bg-[#7a3e0b] text-white py-3.5 rounded-2xl font-bold text-sm transition shadow-lg shadow-orange-900/20">
        Simpan Produk
      </button>

    </form>
  </div>
</div>

<?php include "navbar_karyawan.php"; ?>

<script>
// ===== Modal =====
function openModal(){ document.getElementById('modal').classList.remove('hidden'); }
function closeModal(){ document.getElementById('modal').classList.add('hidden'); }

// ===== Preview foto =====
document.getElementById('fotoInput').addEventListener('change', function(){
  const file = this.files[0];
  if (!file) return;
  document.getElementById('fotoLabel').textContent = file.name;
  document.getElementById('fotoIcon').classList.add('hidden');
  const reader = new FileReader();
  reader.onload = e => {
    const prev = document.getElementById('fotoPreview');
    prev.src = e.target.result;
    prev.classList.remove('hidden');
  };
  reader.readAsDataURL(file);
});

// ===== Search client-side =====
document.getElementById('searchInput').addEventListener('input', function(){
  const kw = this.value.trim().toLowerCase();
  const cards = document.querySelectorAll('#productList [data-name]');
  let visible = 0;
  cards.forEach(c => {
    const match = c.dataset.name.includes(kw);
    c.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('noResult').classList.toggle('hidden', visible > 0 || cards.length === 0);
});

// ===== AJAX Add to Cart =====
let cartCount = <?= $cartCount ?>;

document.querySelectorAll('.add-cart-form').forEach(form => {
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    const btn = this.querySelector('.add-btn');
    btn.classList.add('bounce');
    setTimeout(() => btn.classList.remove('bounce'), 300);

    const fd = new FormData(this);
    fd.append('add_cart', '1');

    const res = await fetch('produk.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd
    });
    const data = await res.json();

    // Update badge
    cartCount = data.cartCount;
    const badge = document.getElementById('cartBadge');
    badge.textContent = cartCount;
    badge.classList.toggle('hidden', cartCount === 0);

    // Toast
    showToast('Ditambahkan ke keranjang');
  });
});

function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.remove('hidden');
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => t.classList.add('hidden'), 2000);
}
</script>
</body>
</html>