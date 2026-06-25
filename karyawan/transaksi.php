<?php
session_start();
include "../koneksi.php";

/* ===== CEK LOGIN ===== */
$user_id = $_SESSION['id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

/* ===== UBAH QTY ===== */
if (isset($_POST['update_qty'])) {
    $id   = (int) $_POST['id'];
    $aksi = $_POST['aksi'];

    if (isset($_SESSION['cart'][$id])) {

        if ($aksi === 'tambah') {
            // Cek stok aktual sebelum tambah
            $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM products WHERE id=$id"));
            $stokMax = $r ? (int)$r['stok'] : 0;
            if ($_SESSION['cart'][$id]['qty'] < $stokMax) {
                $_SESSION['cart'][$id]['qty']++;
            }
        } else {
            $_SESSION['cart'][$id]['qty']--;
        }

        if ($_SESSION['cart'][$id]['qty'] <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['subtotal'] =
                $_SESSION['cart'][$id]['qty'] * $_SESSION['cart'][$id]['harga'];
        }
    }
    header("Location: transaksi.php");
    exit;
}

/* ===== HAPUS ITEM ===== */
if (isset($_POST['hapus_item'])) {
    $id = (int) $_POST['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: transaksi.php");
    exit;
}

/* ===== KOSONGKAN ===== */
if (isset($_POST['kosongkan'])) {
    unset($_SESSION['cart']);
    header("Location: transaksi.php");
    exit;
}

/* ===== PROSES BAYAR ===== */
$errorBayar = "";

if (isset($_POST['bayar'])) {

    if (empty($_SESSION['cart'])) {
        $errorBayar = "Keranjang masih kosong!";
    } else {

        $totalBayar = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalBayar += $item['subtotal'];
        }

        $uangTunai = (float) ($_POST['uang_tunai'] ?? 0);
        $kembalian = $uangTunai - $totalBayar;

        if ($uangTunai < $totalBayar) {
            $errorBayar = "Uang tunai kurang dari total belanja!";
        } else {

            // Cek stok
            foreach ($_SESSION['cart'] as $pid => $item) {

                $cekProduk = mysqli_fetch_assoc(
                    mysqli_query(
                        $conn,
                        "SELECT stok,nama_produk FROM products WHERE id=$pid"
                    )
                );

                if (!$cekProduk || $cekProduk['stok'] < $item['qty']) {
                    $errorBayar =
                        "Stok " .
                        htmlspecialchars($cekProduk['nama_produk'] ?? 'Produk') .
                        " tidak mencukupi!";
                    break;
                }
            }

            if (empty($errorBayar)) {

                mysqli_begin_transaction($conn);

                try {

                    // Kode transaksi
                    $kode =
                        'TRX-' .
                        date('Ymd') .
                        '-' .
                        strtoupper(substr(uniqid(), -5));

                    // Simpan transaksi
                    $stmtTrx = mysqli_prepare(
                        $conn,
                        "INSERT INTO transactions
                        (kode_transaksi,user_id,total,uang_diterima,kembalian,tanggal)
                        VALUES (?,?,?,?,?,NOW())"
                    );

                    mysqli_stmt_bind_param(
                        $stmtTrx,
                        "siddd",
                        $kode,
                        $user_id,
                        $totalBayar,
                        $uangTunai,
                        $kembalian
                    );

                    mysqli_stmt_execute($stmtTrx);

                    $trxId = mysqli_insert_id($conn);

                    // Simpan detail
                    foreach ($_SESSION['cart'] as $pid => $item) {

                        $qty      = (int) $item['qty'];
                        $harga    = (float) $item['harga'];
                        $subtotal = (float) $item['subtotal'];

                        // Ambil stok lama
                        $produk = mysqli_fetch_assoc(
                            mysqli_query(
                                $conn,
                                "SELECT stok FROM products WHERE id=$pid"
                            )
                        );

                        $stokLama = (int) $produk['stok'];

                        // Simpan detail transaksi
                        $stmtDet = mysqli_prepare(
                            $conn,
                            "INSERT INTO transaction_details
                            (transaction_id,product_id,qty,harga_satuan,subtotal)
                            VALUES (?,?,?,?,?)"
                        );

                        mysqli_stmt_bind_param(
                            $stmtDet,
                            "iiddd",
                            $trxId,
                            $pid,
                            $qty,
                            $harga,
                            $subtotal
                        );

                        mysqli_stmt_execute($stmtDet);

                        // Update stok
                        mysqli_query(
                            $conn,
                            "UPDATE products
                             SET stok = stok - $qty
                             WHERE id = $pid"
                        );

                        // Ambil stok baru
                        $produkBaru = mysqli_fetch_assoc(
                            mysqli_query(
                                $conn,
                                "SELECT stok FROM products WHERE id=$pid"
                            )
                        );

                        $stokBaru = (int) $produkBaru['stok'];

                        // Simpan log stok keluar
                        $jenis = "keluar";
                        $ket   = "Terjual via transaksi $kode";

                        $stmtLog = mysqli_prepare(
                            $conn,
                            "INSERT INTO stock_logs
                            (product_id,jenis,qty,stok_lama,stok_baru,keterangan)
                            VALUES (?,?,?,?,?,?)"
                        );

                        mysqli_stmt_bind_param(
                            $stmtLog,
                            "isiiis",
                            $pid,
                            $jenis,
                            $qty,
                            $stokLama,
                            $stokBaru,
                            $ket
                        );

                        if (!mysqli_stmt_execute($stmtLog)) {
                            throw new Exception(mysqli_stmt_error($stmtLog));
                        }
                    }

                    mysqli_commit($conn);

                    $_SESSION['trx_sukses'] = [
                        'kode'       => $kode,
                        'total'      => $totalBayar,
                        'bayar'      => $uangTunai,
                        'kembalian'  => $kembalian,
                        'items'      => count($_SESSION['cart'])
                    ];

                    unset($_SESSION['cart']);

                    header("Location: transaksi.php");
                    exit;

                } catch (Exception $e) {

                    mysqli_rollback($conn);

                    $errorBayar =
                        "Transaksi gagal: " .
                        $e->getMessage();
                }
            }
        }
    }
}
/* ===== FLASH SUKSES ===== */
$trxSukses = $_SESSION['trx_sukses'] ?? null;
unset($_SESSION['trx_sukses']);

/* ===== DATA KERANJANG ===== */
$cart = $_SESSION['cart'] ?? [];
$subtotalBelanja = 0;
foreach ($cart as $item) $subtotalBelanja += $item['subtotal'];
$totalBelanja = $subtotalBelanja; // bisa ditambah diskon nanti

/* ===== SARAN NOMINAL ===== */
function pk_roundUp($val, $nearest) {
    if ($val <= 0) return $nearest;
    return (int)(ceil($val / $nearest) * $nearest);
}
$saranUang = [];
if ($totalBelanja > 0) {
    $saranUang[] = $totalBelanja;
    foreach ([5000, 10000, 50000, 100000] as $k) {
        $b = pk_roundUp($totalBelanja, $k);
        if ($b > $totalBelanja) $saranUang[] = $b;
    }
    $saranUang = array_slice(array_values(array_unique($saranUang)), 0, 4);
    sort($saranUang);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title>Keranjang | Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,500,1,0" rel="stylesheet"/>
<style>
* { font-family:'Plus Jakarta Sans',sans-serif; }
.material-symbols-rounded { font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24; user-select:none; }
body { background:#fff8f5; min-height:100dvh; }

.header-bg {
    background:linear-gradient(135deg,#7c3a08 0%,#8e4a0e 40%,#a9632c 100%);
    position:relative; overflow:hidden;
}
.header-bg::before {
    content:''; position:absolute; width:160px; height:160px;
    background:rgba(255,255,255,0.05); border-radius:50%;
    top:-40px; right:-30px;
}

.qty-btn { transition:transform .12s ease; }
.qty-btn:active { transform:scale(0.85); }

.chip-btn { transition:all .15s ease; }
.chip-btn.active { background:#fcddc1; border-color:#8e4a0e; color:#8e4a0e; font-weight:700; }

/* Item card swipe-to-delete hint (visual only) */
.item-card { transition:opacity .15s ease; }

/* Overlay backdrop */
#successOverlay { backdrop-filter:blur(4px); }

/* Input */
.input-field:focus { outline:none; border-color:#8e4a0e; box-shadow:0 0 0 3px rgba(142,74,14,.1); }
</style>
</head>
<body class="pb-40">

<!-- ====== HEADER ====== -->
<header class="header-bg text-white">
  <div class="relative z-10 max-w-md mx-auto px-4 pt-10 pb-5">

    <div class="flex items-center justify-between mb-1">
      <a href="produk.php" class="w-9 h-9 flex items-center justify-center rounded-full bg-white/15 hover:bg-white/25 transition">
        <span class="material-symbols-rounded text-xl">arrow_back</span>
      </a>

      <h1 class="font-extrabold text-lg">
        Keranjang <?php if (count($cart) > 0): ?><span class="text-sm font-normal opacity-70">(<?= count($cart) ?> item)</span><?php endif; ?>
      </h1>

      <?php if (!empty($cart)): ?>
      <form method="POST" onsubmit="return confirm('Kosongkan semua item?')">
        <button type="submit" name="kosongkan" class="w-9 h-9 flex items-center justify-center rounded-full bg-white/15 hover:bg-red-400/40 transition">
          <span class="material-symbols-rounded text-xl">delete_sweep</span>
        </button>
      </form>
      <?php else: ?>
      <div class="w-9"></div>
      <?php endif; ?>
    </div>

  </div>
</header>

<!-- ====== MAIN ====== -->
<main class="max-w-md mx-auto px-4 pt-4 space-y-4">

<?php if (empty($cart)): ?>

  <!-- EMPTY STATE -->
  <div class="flex flex-col items-center justify-center text-center py-20 space-y-3 mt-4">
    <div class="w-24 h-24 rounded-full bg-orange-100 flex items-center justify-center">
      <span class="material-symbols-rounded text-5xl text-[#8e4a0e]">shopping_cart</span>
    </div>
    <h2 class="text-lg font-bold text-slate-700">Keranjang Kosong</h2>
    <p class="text-sm text-slate-400 max-w-xs">Pilih produk dari halaman Produk untuk mulai transaksi.</p>
    <a href="produk.php"
       class="mt-2 px-6 py-3 rounded-2xl bg-[#8e4a0e] text-white text-sm font-bold shadow-lg shadow-orange-900/20">
      Pilih Produk
    </a>
  </div>

<?php else: ?>

  <!-- ====== DAFTAR ITEM ====== -->
  <section class="space-y-3">
    <?php foreach ($cart as $id => $item): ?>
    <div class="item-card bg-white rounded-2xl p-4 flex items-center gap-3 shadow-sm border border-orange-50">

      <!-- Icon produk -->
      <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
        <span class="material-symbols-rounded text-[#8e4a0e] text-xl">fastfood</span>
      </div>

      <!-- Info -->
      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-bold text-slate-800 truncate"><?= htmlspecialchars($item['nama']) ?></h3>
        <p class="text-xs text-slate-400 mt-0.5">Rp <?= number_format($item['harga'], 0, ',', '.') ?> / item</p>
        <p class="text-sm font-extrabold text-[#8e4a0e] mt-0.5">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
      </div>

      <!-- Qty control -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <form method="POST">
          <input type="hidden" name="id" value="<?= (int)$id ?>">
          <input type="hidden" name="aksi" value="kurang">
          <button type="submit" name="update_qty"
                  class="qty-btn w-8 h-8 rounded-full <?= $item['qty'] <= 1 ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-[#8e4a0e]' ?> flex items-center justify-center">
            <span class="material-symbols-rounded text-lg"><?= $item['qty'] <= 1 ? 'delete' : 'remove' ?></span>
          </button>
        </form>

        <span class="text-sm font-bold text-slate-800 min-w-[20px] text-center"><?= $item['qty'] ?></span>

        <form method="POST">
          <input type="hidden" name="id" value="<?= (int)$id ?>">
          <input type="hidden" name="aksi" value="tambah">
          <button type="submit" name="update_qty"
                  class="qty-btn w-8 h-8 rounded-full bg-orange-100 text-[#8e4a0e] flex items-center justify-center">
            <span class="material-symbols-rounded text-lg">add</span>
          </button>
        </form>
      </div>

    </div>
    <?php endforeach; ?>
  </section>

  <!-- ====== RINGKASAN ====== -->
  <section class="bg-white rounded-2xl p-4 shadow-sm border border-orange-50 space-y-3">
    <h2 class="text-sm font-extrabold text-slate-700">Ringkasan Pesanan</h2>

    <div class="flex justify-between items-center text-sm text-slate-600">
      <span>Subtotal (<?= array_sum(array_column($cart, 'qty')) ?> item)</span>
      <span>Rp <?= number_format($subtotalBelanja, 0, ',', '.') ?></span>
    </div>
    <div class="flex justify-between items-center text-sm text-slate-400">
      <span>Diskon</span>
      <span>–</span>
    </div>
    <div class="pt-3 border-t border-orange-100 flex justify-between items-center">
      <span class="font-bold text-slate-800">Total</span>
      <span class="text-lg font-extrabold text-[#8e4a0e]" id="totalBelanjaText">Rp <?= number_format($totalBelanja, 0, ',', '.') ?></span>
    </div>
  </section>

  <!-- ====== PEMBAYARAN ====== -->
  <form method="POST" id="formBayar" class="space-y-4">
    <input type="hidden" name="bayar" value="1">

    <div>
      <h2 class="text-sm font-extrabold text-slate-700 mb-3">Pembayaran Tunai</h2>

      <?php if (!empty($errorBayar)): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2 mb-3">
        <span class="material-symbols-rounded text-base">error</span>
        <?= htmlspecialchars($errorBayar) ?>
      </div>
      <?php endif; ?>

      <!-- Input uang tunai -->
      <div class="relative bg-white rounded-2xl border-2 border-[#8e4a0e] overflow-hidden shadow">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-[#8e4a0e] text-sm">Rp</span>
        <input id="uangTunai" name="uang_tunai" type="number" min="0"
               value="<?= $totalBelanja ?>"
               placeholder="0"
               class="input-field w-full py-4 pl-12 pr-4 text-right text-lg font-extrabold text-[#8e4a0e] bg-transparent border-0 focus:ring-0">
      </div>

      <!-- Chip nominal saran -->
      <?php if (!empty($saranUang)): ?>
      <div class="flex gap-2 flex-wrap mt-3" id="chipGroup">
        <?php foreach ($saranUang as $nom): ?>
        <button type="button" data-amount="<?= $nom ?>"
                class="chip-btn flex-1 min-w-[70px] h-10 rounded-2xl border border-orange-200 bg-white text-slate-600 text-xs font-semibold transition">
          Rp <?= number_format($nom, 0, ',', '.') ?>
        </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Kembalian -->
      <div class="mt-3 bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3 flex justify-between items-center">
        <span class="text-sm font-semibold text-slate-600">Kembalian</span>
        <span class="text-lg font-extrabold text-[#8e4a0e]" id="kembalianText">Rp 0</span>
      </div>
    </div>

    <!-- Sticky Footer -->
    <footer class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur border-t border-orange-100 px-4 pb-6 pt-3 z-40 max-w-none">
      <div class="max-w-md mx-auto">
        <button id="btnSelesai" type="submit"
                class="w-full h-14 bg-[#8e4a0e] hover:bg-[#7a3e0b] text-white rounded-2xl font-extrabold text-sm active:scale-[.98] transition-all flex items-center justify-center gap-2 shadow-xl shadow-orange-900/20">
          <span class="material-symbols-rounded text-xl">check_circle</span>
          SELESAIKAN TRANSAKSI
        </button>
        <p class="text-center text-[10px] text-slate-400 mt-2">Pastikan jumlah sudah benar sebelum melanjutkan</p>
      </div>
    </footer>

  </form>

<?php endif; ?>
</main>

<!-- ====== OVERLAY SUKSES ====== -->
<?php if ($trxSukses): ?>
<div id="successOverlay" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[60] px-4">
  <div class="bg-white rounded-3xl p-6 w-full max-w-sm text-center space-y-4 shadow-2xl">

    <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center">
      <span class="material-symbols-rounded text-5xl text-green-600">check_circle</span>
    </div>

    <div>
      <h2 class="text-lg font-extrabold text-slate-800">Transaksi Berhasil!</h2>
      <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($trxSukses['kode']) ?></p>
    </div>

    <div class="bg-orange-50 rounded-2xl p-4 text-left space-y-2.5">
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">Total Belanja</span>
        <span class="font-bold">Rp <?= number_format($trxSukses['total'],0,',','.') ?></span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">Uang Diterima</span>
        <span class="font-bold">Rp <?= number_format($trxSukses['bayar'],0,',','.') ?></span>
      </div>
      <div class="flex justify-between text-sm border-t border-orange-200 pt-2.5">
        <span class="font-bold text-slate-700">Kembalian</span>
        <span class="text-lg font-extrabold text-[#8e4a0e]">Rp <?= number_format($trxSukses['kembalian'],0,',','.') ?></span>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <a href="dashboard.php"
         class="h-12 rounded-2xl border border-orange-200 text-[#8e4a0e] font-bold text-sm flex items-center justify-center">
        Dashboard
      </a>
      <a href="produk.php"
         class="h-12 rounded-2xl bg-[#8e4a0e] text-white font-bold text-sm flex items-center justify-center shadow">
        Transaksi Baru
      </a>
    </div>

  </div>
</div>
<?php endif; ?>

<?php include "navbar_karyawan.php"; ?>

<script>
const totalBelanja = <?= (int) $totalBelanja ?>;
const uangTunai    = document.getElementById('uangTunai');
const kembalianEl  = document.getElementById('kembalianText');
const btnSelesai   = document.getElementById('btnSelesai');
const chipGroup    = document.getElementById('chipGroup');

function formatRp(n) {
    return 'Rp ' + Math.max(0, n).toLocaleString('id-ID');
}

function hitungKembalian() {
    if (!uangTunai) return;
    const nilai = parseFloat(uangTunai.value || '0');
    const selisih = nilai - totalBelanja;

    kembalianEl.textContent = formatRp(selisih);
    kembalianEl.style.color = selisih < 0 ? '#dc2626' : '#8e4a0e';

    if (btnSelesai) {
        const kurang = selisih < 0;
        btnSelesai.disabled = kurang;
        btnSelesai.classList.toggle('opacity-50', kurang);
        btnSelesai.classList.toggle('cursor-not-allowed', kurang);
    }

    chipGroup?.querySelectorAll('.chip-btn').forEach(c => {
        c.classList.toggle('active', parseFloat(c.dataset.amount) === nilai);
    });
}

uangTunai?.addEventListener('input', hitungKembalian);
hitungKembalian();

chipGroup?.querySelectorAll('.chip-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        uangTunai.value = btn.dataset.amount;
        hitungKembalian();
    });
});

// Tutup overlay sukses bila klik luar
document.getElementById('successOverlay')?.addEventListener('click', function(e){
    if (e.target === this) this.remove();
});
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>