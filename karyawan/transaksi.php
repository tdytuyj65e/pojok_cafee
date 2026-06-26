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
    unset($_SESSION['cart'][(int)$_POST['id']]);
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

        $totalBayar    = 0;
        foreach ($_SESSION['cart'] as $item) $totalBayar += $item['subtotal'];

        $metode        = $_POST['metode_pembayaran'] ?? 'cash';
        $uangTunai     = (float)($_POST['uang_tunai'] ?? 0);
        $kembalian     = ($metode === 'bon') ? 0 : ($uangTunai - $totalBayar);
        $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
        $no_hp         = trim($_POST['no_hp'] ?? '');
        $alamat        = trim($_POST['alamat'] ?? '');

        // Validasi
        if ($metode === 'cash' && $uangTunai < $totalBayar) {
            $errorBayar = "Uang tunai kurang dari total belanja!";
        } elseif ($metode === 'bon' && empty($nama_pelanggan)) {
            $errorBayar = "Nama pelanggan wajib diisi untuk transaksi bon!";
        } else {

            // Cek stok semua item
            foreach ($_SESSION['cart'] as $pid => $item) {
                $cek = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT stok, nama_produk FROM products WHERE id=$pid"));
                if (!$cek || $cek['stok'] < $item['qty']) {
                    $errorBayar = "Stok " . htmlspecialchars($cek['nama_produk'] ?? 'Produk') . " tidak mencukupi!";
                    break;
                }
            }

            if (empty($errorBayar)) {

                mysqli_begin_transaction($conn);

                try {

                    $kode = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

                    /* --- Simpan customer jika bon --- */
                    $customer_id = null;
                    if ($metode === 'bon') {
                        $stmtCust = mysqli_prepare($conn,
                            "INSERT INTO customers (nama, no_hp, alamat) VALUES (?,?,?)");
                        mysqli_stmt_bind_param($stmtCust, "sss", $nama_pelanggan, $no_hp, $alamat);
                        mysqli_stmt_execute($stmtCust);
                        $customer_id = mysqli_insert_id($conn);
                    }

                    /* --- Simpan transaksi --- */
                    $uang_diterima = ($metode === 'bon') ? 0 : $uangTunai;
                    $kem           = ($metode === 'bon') ? 0 : $kembalian;

                    $stmtTrx = mysqli_prepare($conn,
                        "INSERT INTO transactions
                         (kode_transaksi, user_id, total, uang_diterima, kembalian, metode_pembayaran, tanggal)
                         VALUES (?,?,?,?,?,?,NOW())");
                    mysqli_stmt_bind_param($stmtTrx, "siddds",
                        $kode, $user_id, $totalBayar, $uang_diterima, $kem, $metode);
                    mysqli_stmt_execute($stmtTrx);
                    $trxId = mysqli_insert_id($conn);

                    /* --- Detail & stok --- */
                    foreach ($_SESSION['cart'] as $pid => $item) {
                        $qty      = (int)$item['qty'];
                        $harga    = (float)$item['harga'];
                        $subtotal = (float)$item['subtotal'];

                        $produk   = mysqli_fetch_assoc(mysqli_query($conn,
                            "SELECT stok FROM products WHERE id=$pid"));
                        $stokLama = (int)$produk['stok'];

                        $stmtDet = mysqli_prepare($conn,
                            "INSERT INTO transaction_details
                             (transaction_id, product_id, qty, harga_satuan, subtotal)
                             VALUES (?,?,?,?,?)");
                        mysqli_stmt_bind_param($stmtDet, "iiddd", $trxId, $pid, $qty, $harga, $subtotal);
                        mysqli_stmt_execute($stmtDet);

                        mysqli_query($conn,
                            "UPDATE products SET stok = stok - $qty WHERE id = $pid");

                        $produkBaru = mysqli_fetch_assoc(mysqli_query($conn,
                            "SELECT stok FROM products WHERE id=$pid"));
                        $stokBaru = (int)$produkBaru['stok'];

                        $jenis = "keluar";
                        $ket   = "Terjual via transaksi $kode";
                        $stmtLog = mysqli_prepare($conn,
                            "INSERT INTO stock_logs
                             (product_id, jenis, qty, stok_lama, stok_baru, keterangan)
                             VALUES (?,?,?,?,?,?)");
                        mysqli_stmt_bind_param($stmtLog, "isiiis",
                            $pid, $jenis, $qty, $stokLama, $stokBaru, $ket);
                        if (!mysqli_stmt_execute($stmtLog))
                            throw new Exception(mysqli_stmt_error($stmtLog));
                    }

                    /* --- Hutang jika bon --- */
                    if ($metode === 'bon' && $customer_id) {
                        $stmtDebt = mysqli_prepare($conn,
                            "INSERT INTO debts
                             (customer_id, transaction_id, total_hutang, sisa_hutang, status)
                             VALUES (?,?,?,?,'belum_lunas')");
                        mysqli_stmt_bind_param($stmtDebt, "iidd",
                            $customer_id, $trxId, $totalBayar, $totalBayar);
                        mysqli_stmt_execute($stmtDebt);
                    }

                    mysqli_commit($conn);

                    $_SESSION['trx_sukses'] = [
                        'kode'       => $kode,
                        'total'      => $totalBayar,
                        'bayar'      => $uang_diterima,
                        'kembalian'  => $kem,
                        'metode'     => $metode,
                        'pelanggan'  => $nama_pelanggan,
                        'items'      => count($_SESSION['cart']),
                    ];

                    unset($_SESSION['cart']);
                    header("Location: transaksi.php");
                    exit;

                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $errorBayar = "Transaksi gagal: " . $e->getMessage();
                }
            }
        }
    }
}

/* ===== FLASH ===== */
$trxSukses = $_SESSION['trx_sukses'] ?? null;
unset($_SESSION['trx_sukses']);

/* ===== KERANJANG ===== */
$cart            = $_SESSION['cart'] ?? [];
$subtotalBelanja = array_sum(array_column($cart, 'subtotal'));
$totalBelanja    = $subtotalBelanja;

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
<title>Transaksi | Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#1a1a2e">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
:root {
    --kopi: #2c1810;
    --espresso: #4a2c1a;
    --caramel: #c8813a;
    --cream: #f5ede4;
    --foam: #faf6f2;
    --latte: #e8d5c4;
    --steam: #6b4c3b;
    --chalk: #f9f5f1;
}

* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

body {
    background: var(--chalk);
    min-height: 100dvh;
    /* navbar karyawan ≈ 65px + pay-footer ≈ 94px + safe area */
    padding-bottom: 180px;
}

/* ── TOP RAIL ── */
.top-rail {
    background: var(--kopi);
    padding: 0 16px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 30;
}
.top-rail h1 { color: #fff; font-weight: 800; font-size: 17px; letter-spacing: -.3px; }

/* ── CART ITEM ── */
.cart-item {
    display: grid;
    grid-template-columns: 44px 1fr auto;
    gap: 12px;
    align-items: center;
    background: #fff;
    border-radius: 16px;
    padding: 12px 14px;
    box-shadow: 0 1px 3px rgba(44,24,16,.07);
    border: 1px solid var(--latte);
}
.cart-icon {
    width: 44px; height: 44px;
    background: var(--cream);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.item-name { font-size: 13px; font-weight: 700; color: var(--kopi); line-height: 1.3; }
.item-price { font-size: 11px; color: var(--steam); margin-top: 1px; }
.item-sub { font-size: 14px; font-weight: 800; color: var(--caramel); margin-top: 2px; }

.qty-ctrl { display: flex; align-items: center; gap: 8px; }
.qty-btn {
    width: 30px; height: 30px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700;
    border: none; cursor: pointer;
    transition: transform .1s, background .1s;
}
.qty-btn:active { transform: scale(.85); }
.qty-btn.minus { background: #fde8e8; color: #c53030; }
.qty-btn.minus.soft { background: var(--cream); color: var(--caramel); }
.qty-val { font-size: 15px; font-weight: 800; color: var(--kopi); min-width: 20px; text-align: center; }

/* ── SECTION CARD ── */
.section-card {
    background: #fff;
    border-radius: 20px;
    padding: 18px;
    box-shadow: 0 1px 4px rgba(44,24,16,.06);
    border: 1px solid var(--latte);
}
.section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--steam);
    margin-bottom: 14px;
}

/* ── METODE TABS ── */
.metode-tabs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.metode-tab {
    padding: 10px 6px;
    border-radius: 12px;
    border: 2px solid var(--latte);
    background: #fff;
    cursor: pointer;
    text-align: center;
    transition: all .18s;
}
.metode-tab .tab-icon { font-size: 20px; display: block; margin-bottom: 3px; }
.metode-tab .tab-label { font-size: 11px; font-weight: 700; color: var(--steam); }
.metode-tab.active { border-color: var(--caramel); background: var(--cream); }
.metode-tab.active .tab-label { color: var(--espresso); }

/* ── INPUT ── */
.field-group { margin-bottom: 12px; }
.field-label { font-size: 11px; font-weight: 700; color: var(--steam); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .5px; }
.field-input {
    width: 100%;
    border: 1.5px solid var(--latte);
    border-radius: 12px;
    padding: 11px 14px;
    font-size: 14px;
    font-weight: 500;
    color: var(--kopi);
    background: var(--foam);
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.field-input:focus {
    border-color: var(--caramel);
    box-shadow: 0 0 0 3px rgba(200,129,58,.15);
    background: #fff;
}
.field-input::placeholder { color: #bbb; font-weight: 400; }

/* ── NOMINAL UANG ── */
.uang-wrapper {
    background: var(--kopi);
    border-radius: 16px;
    padding: 16px;
    position: relative;
}
.uang-prefix { font-size: 13px; font-weight: 700; color: var(--caramel); margin-bottom: 4px; }
.uang-input {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.5px;
}
.uang-input::placeholder { color: rgba(255,255,255,.25); }

/* ── CHIP ── */
.chip-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
.chip {
    flex: 1; min-width: 68px;
    padding: 8px 4px;
    border-radius: 10px;
    border: 1.5px solid rgba(255,255,255,.15);
    background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.7);
    font-size: 11px; font-weight: 700;
    text-align: center;
    cursor: pointer;
    transition: all .15s;
}
.chip:active { transform: scale(.93); }
.chip.active { background: var(--caramel); border-color: var(--caramel); color: #fff; }

/* ── KEMBALIAN ── */
.kembalian-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--cream);
    border-radius: 12px;
    padding: 12px 14px;
    margin-top: 12px;
}
.kembalian-label { font-size: 12px; font-weight: 700; color: var(--steam); }
.kembalian-val { font-size: 18px; font-weight: 800; color: var(--caramel); }
.kembalian-val.kurang { color: #e53e3e; }

/* ── SUMMARY ── */
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; }
.summary-row .lbl { font-size: 13px; color: var(--steam); }
.summary-row .val { font-size: 13px; font-weight: 600; color: var(--kopi); }
.summary-divider { height: 1px; background: var(--latte); margin: 8px 0; }
.summary-total .lbl { font-size: 15px; font-weight: 700; color: var(--kopi); }
.summary-total .val { font-size: 20px; font-weight: 800; color: var(--caramel); }

/* ── BON AREA ── */
.bon-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: #fff3cd; border: 1.5px solid #f6c94e;
    border-radius: 10px; padding: 6px 12px;
    font-size: 11px; font-weight: 700; color: #7d5a00;
    margin-bottom: 14px;
}

/* ── FOOTER BTN ── */
.pay-footer {
    position: fixed; bottom: 65px; left: 0; right: 0;
    background: #fff;
    padding: 10px 16px 12px;
    box-shadow: 0 -4px 20px rgba(44,24,16,.1);
    z-index: 40;
    border-top: 1px solid var(--latte);
}
.pay-btn {
    width: 100%;
    height: 54px;
    background: var(--caramel);
    border: none;
    border-radius: 16px;
    color: #fff;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    letter-spacing: .3px;
    transition: opacity .15s, transform .1s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.pay-btn:active { transform: scale(.98); }
.pay-btn:disabled { opacity: .45; cursor: not-allowed; }
.pay-btn svg { width: 20px; height: 20px; flex-shrink: 0; }

/* ── EMPTY ── */
.empty-wrap {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; padding: 60px 20px;
    gap: 12px;
}
.empty-icon {
    width: 90px; height: 90px;
    background: var(--cream);
    border-radius: 28px;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
}
.empty-title { font-size: 17px; font-weight: 800; color: var(--kopi); }
.empty-sub { font-size: 13px; color: var(--steam); max-width: 220px; line-height: 1.5; }
.empty-btn {
    background: var(--caramel); color: #fff;
    padding: 13px 28px; border-radius: 14px;
    font-size: 14px; font-weight: 700;
    text-decoration: none; display: inline-block; margin-top: 4px;
}

/* ── OVERLAY SUKSES ── */
#successOverlay {
    position: fixed; inset: 0; background: rgba(20,10,5,.65);
    backdrop-filter: blur(6px);
    display: flex; align-items: flex-end; justify-content: center;
    z-index: 60; padding: 0 0 0 0;
}
.success-sheet {
    background: #fff;
    border-radius: 28px 28px 0 0;
    width: 100%; max-width: 480px;
    padding: 8px 20px 20px;
    /* sit above navbar */
    margin-bottom: 65px;
    border-radius: 28px;
    animation: slideUp .35s cubic-bezier(.22,1,.36,1);
    max-height: calc(100dvh - 80px);
    overflow-y: auto;
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to   { transform: translateY(0); }
}
.sheet-handle { width: 36px; height: 4px; background: #e2e8f0; border-radius: 2px; margin: 10px auto 20px; }
.success-icon-wrap {
    width: 72px; height: 72px;
    background: #d1fae5;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.success-icon-wrap svg { width: 36px; height: 36px; color: #059669; }
.success-title { text-align: center; font-size: 18px; font-weight: 800; color: var(--kopi); }
.success-kode { text-align: center; font-size: 12px; color: var(--steam); margin-top: 3px; }

.receipt-card {
    background: var(--chalk);
    border-radius: 16px;
    padding: 14px 16px;
    margin: 16px 0;
    border: 1.5px dashed var(--latte);
}
.receipt-row { display: flex; justify-content: space-between; padding: 5px 0; }
.receipt-row .r-lbl { font-size: 12px; color: var(--steam); font-weight: 500; }
.receipt-row .r-val { font-size: 12px; font-weight: 700; color: var(--kopi); }
.receipt-row.total { border-top: 1.5px dashed var(--latte); margin-top: 4px; padding-top: 10px; }
.receipt-row.total .r-lbl { font-size: 14px; font-weight: 700; color: var(--kopi); }
.receipt-row.total .r-val { font-size: 18px; font-weight: 800; color: var(--caramel); }

.receipt-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 8px;
    font-size: 11px; font-weight: 700;
}
.badge-cash { background: #d1fae5; color: #059669; }
.badge-qris { background: #e0f2fe; color: #0284c7; }
.badge-bon  { background: #fff3cd; color: #7d5a00; }

.success-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 4px; }
.act-btn {
    height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer;
    border: none;
}
.act-outline { border: 1.5px solid var(--latte); color: var(--steam); background: #fff; }
.act-fill { background: var(--caramel); color: #fff; }

/* ── ERROR ── */
.error-bar {
    background: #fff5f5; border: 1.5px solid #fed7d7;
    border-radius: 12px; padding: 12px 14px;
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600; color: #c53030;
    margin-bottom: 14px;
}
.error-bar svg { width: 16px; height: 16px; flex-shrink: 0; }

/* Section divider */
.section-gap { height: 10px; }
</style>
</head>
<body>

<!-- TOP RAIL -->
<div class="top-rail">
    <a href="produk.php" style="width:36px;height:36px;border-radius:12px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1>Keranjang<?php if (!empty($cart)): ?> <span style="opacity:.55;font-weight:500;font-size:14px;">(<?= count($cart) ?>)</span><?php endif; ?></h1>
    <?php if (!empty($cart)): ?>
    <form method="POST" onsubmit="return confirm('Kosongkan keranjang?')">
        <button type="submit" name="kosongkan" style="width:36px;height:36px;border-radius:12px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </form>
    <?php else: ?>
    <div style="width:36px;"></div>
    <?php endif; ?>
</div>

<!-- MAIN -->
<div style="max-width:480px;margin:0 auto;padding:16px 14px 0;">

<?php if (empty($cart)): ?>
<!-- EMPTY STATE -->
<div class="empty-wrap">
    <div class="empty-icon">🛒</div>
    <p class="empty-title">Keranjang Kosong</p>
    <p class="empty-sub">Pilih produk dari menu untuk mulai transaksi baru.</p>
    <a href="produk.php" class="empty-btn">Lihat Menu →</a>
</div>

<?php else: ?>

<!-- ── ITEM LIST ── -->
<div class="section-title" style="margin-bottom:10px;">Pesanan</div>
<div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
<?php foreach ($cart as $id => $item): ?>
<div class="cart-item">
    <div class="cart-icon">☕</div>
    <div style="min-width:0;">
        <div class="item-name"><?= htmlspecialchars($item['nama']) ?></div>
        <div class="item-price">Rp <?= number_format($item['harga'],0,',','.') ?> / pcs</div>
        <div class="item-sub">Rp <?= number_format($item['subtotal'],0,',','.') ?></div>
    </div>
    <div class="qty-ctrl">
        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <input type="hidden" name="aksi" value="kurang">
            <button type="submit" name="update_qty" class="qty-btn <?= $item['qty']<=1 ? 'minus' : 'minus soft' ?>">
                <?= $item['qty']<=1 ? '🗑' : '−' ?>
            </button>
        </form>
        <span class="qty-val"><?= $item['qty'] ?></span>
        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <input type="hidden" name="aksi" value="tambah">
            <button type="submit" name="update_qty" class="qty-btn" style="background:var(--cream);color:var(--caramel);">+</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ── RINGKASAN ── -->
<div class="section-card" style="margin-bottom:10px;">
    <div class="section-title">Ringkasan</div>
    <div class="summary-row">
        <span class="lbl">Subtotal (<?= array_sum(array_column($cart,'qty')) ?> item)</span>
        <span class="val">Rp <?= number_format($subtotalBelanja,0,',','.') ?></span>
    </div>
    <div class="summary-row">
        <span class="lbl">Diskon</span>
        <span class="val" style="color:#aaa;">—</span>
    </div>
    <div class="summary-divider"></div>
    <div class="summary-row summary-total">
        <span class="lbl">Total</span>
        <span class="val" id="totalDisplay">Rp <?= number_format($totalBelanja,0,',','.') ?></span>
    </div>
</div>

<!-- ── FORM BAYAR ── -->
<form method="POST" id="formBayar">
<input type="hidden" name="bayar" value="1">
<input type="hidden" name="metode_pembayaran" id="inputMetode" value="cash">

<?php if (!empty($errorBayar)): ?>
<div class="error-bar">
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
    <?= htmlspecialchars($errorBayar) ?>
</div>
<?php endif; ?>

<!-- Metode Pembayaran -->
<div class="section-card" style="margin-bottom:10px;">
    <div class="section-title">Metode Pembayaran</div>
    <div class="metode-tabs">
        <div class="metode-tab active" data-metode="cash" onclick="setMetode('cash',this)">
            <span class="tab-icon">💵</span>
            <span class="tab-label">Cash</span>
        </div>
        <div class="metode-tab" data-metode="qris" onclick="setMetode('qris',this)">
            <span class="tab-icon">📱</span>
            <span class="tab-label">QRIS</span>
        </div>
        <div class="metode-tab" data-metode="bon" onclick="setMetode('bon',this)">
            <span class="tab-icon">📋</span>
            <span class="tab-label">Bon / Hutang</span>
        </div>
    </div>
</div>

<!-- Panel Cash -->
<div id="panelCash" class="section-card" style="margin-bottom:10px;">
    <div class="section-title">Pembayaran Tunai</div>
    <div class="uang-wrapper">
        <div class="uang-prefix">Rp</div>
        <input id="uangTunai" name="uang_tunai" type="number" min="0"
               value="<?= $totalBelanja ?>"
               placeholder="0"
               class="uang-input"
               autocomplete="off"
               inputmode="numeric">
        <?php if (!empty($saranUang)): ?>
        <div class="chip-row" id="chipGroup">
            <?php foreach ($saranUang as $nom): ?>
            <div class="chip" data-amount="<?= $nom ?>"
                 onclick="setChip(<?= $nom ?>, this)">
                Rp <?= number_format($nom,0,',','.') ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="kembalian-row">
        <span class="kembalian-label">💰 Kembalian</span>
        <span class="kembalian-val" id="kembalianVal">Rp 0</span>
    </div>
</div>

<!-- Panel QRIS -->
<div id="panelQris" class="section-card" style="margin-bottom:10px;display:none;">
    <div class="section-title">Pembayaran QRIS</div>
    <div style="text-align:center;padding:20px 0;">
        <div style="font-size:48px;margin-bottom:8px;">📱</div>
        <p style="font-size:13px;color:var(--steam);font-weight:600;">Arahkan kamera ke QR Code kasir</p>
        <p style="font-size:20px;font-weight:800;color:var(--caramel);margin-top:10px;">
            Rp <?= number_format($totalBelanja,0,',','.') ?>
        </p>
        <div style="background:var(--cream);border-radius:12px;padding:10px 14px;margin-top:12px;font-size:12px;color:var(--steam);font-weight:600;">
            ✅ Konfirmasi pembayaran setelah transfer berhasil
        </div>
    </div>
    <!-- field hidden agar form tetap valid -->
    <input type="hidden" id="qrisUang" value="<?= $totalBelanja ?>">
</div>

<!-- Panel Bon -->
<div id="panelBon" class="section-card" style="margin-bottom:10px;display:none;">
    <div class="bon-badge">
        <span>📋</span> Transaksi Bon / Hutang
    </div>
    <p style="font-size:12px;color:var(--steam);margin-bottom:14px;line-height:1.5;">
        Pelanggan membawa barang sekarang, bayar nanti. Hutang akan tercatat di sistem.
    </p>

    <div class="field-group">
        <div class="field-label">Nama Pelanggan <span style="color:#e53e3e;">*</span></div>
        <input type="text" name="nama_pelanggan" id="namaPelanggan"
               placeholder="Masukkan nama pelanggan"
               class="field-input" autocomplete="off">
    </div>
    <div class="field-group">
        <div class="field-label">No. HP</div>
        <input type="tel" name="no_hp"
               placeholder="08xxxxxxxxxx (opsional)"
               class="field-input" inputmode="tel">
    </div>
    <div class="field-group" style="margin-bottom:0;">
        <div class="field-label">Alamat</div>
        <input type="text" name="alamat"
               placeholder="Alamat (opsional)"
               class="field-input">
    </div>

    <div style="background:#fff3cd;border-radius:12px;padding:12px 14px;margin-top:14px;display:flex;gap:8px;align-items:flex-start;">
        <span style="font-size:16px;flex-shrink:0;">⚠️</span>
        <p style="font-size:12px;color:#7d5a00;font-weight:600;line-height:1.5;margin:0;">
            Total hutang <strong>Rp <?= number_format($totalBelanja,0,',','.') ?></strong> akan dicatat atas nama pelanggan.
        </p>
    </div>
    <!-- hidden untuk metode bon -->
    <input type="hidden" id="bonUang" value="0">
</div>

</form>

<!-- STICKY FOOTER -->
<div class="pay-footer">
    <button id="btnBayar" class="pay-btn" onclick="submitForm()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="btnLabel">Bayar Sekarang</span>
    </button>
</div>

<?php endif; // end if cart not empty ?>
</div><!-- /main wrapper -->

<!-- ══ OVERLAY SUKSES ══ -->
<?php if ($trxSukses): ?>
<div id="successOverlay">
    <div class="success-sheet">
        <div class="sheet-handle"></div>

        <div class="success-icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="success-title">
            <?= $trxSukses['metode']==='bon' ? 'Bon Tercatat!' : 'Transaksi Berhasil!' ?>
        </div>
        <div class="success-kode"><?= htmlspecialchars($trxSukses['kode']) ?></div>

        <div class="receipt-card">
            <div class="receipt-row">
                <span class="r-lbl">Metode</span>
                <span class="r-val">
                    <?php
                    $badgeClass = ['cash'=>'badge-cash','qris'=>'badge-qris','bon'=>'badge-bon'];
                    $badgeLabel = ['cash'=>'💵 Cash','qris'=>'📱 QRIS','bon'=>'📋 Bon'];
                    $m = $trxSukses['metode'];
                    ?>
                    <span class="receipt-badge <?= $badgeClass[$m] ?? '' ?>">
                        <?= $badgeLabel[$m] ?? strtoupper($m) ?>
                    </span>
                </span>
            </div>
            <?php if ($trxSukses['metode']==='bon' && !empty($trxSukses['pelanggan'])): ?>
            <div class="receipt-row">
                <span class="r-lbl">Pelanggan</span>
                <span class="r-val"><?= htmlspecialchars($trxSukses['pelanggan']) ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row">
                <span class="r-lbl">Item dibeli</span>
                <span class="r-val"><?= $trxSukses['items'] ?> produk</span>
            </div>
            <?php if ($trxSukses['metode']==='cash'): ?>
            <div class="receipt-row">
                <span class="r-lbl">Uang diterima</span>
                <span class="r-val">Rp <?= number_format($trxSukses['bayar'],0,',','.') ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row total">
                <span class="r-lbl">
                    <?= $trxSukses['metode']==='bon' ? 'Total Hutang' : ($trxSukses['metode']==='cash' ? 'Kembalian' : 'Total Bayar') ?>
                </span>
                <span class="r-val">
                    Rp <?= number_format(
                        $trxSukses['metode']==='bon' ? $trxSukses['total'] :
                        ($trxSukses['metode']==='cash' ? $trxSukses['kembalian'] : $trxSukses['total'])
                    ,0,',','.') ?>
                </span>
            </div>
        </div>

        <div class="success-actions">
            <a href="dashboard.php" class="act-btn act-outline">Dashboard</a>
            <a href="produk.php" class="act-btn act-fill">Transaksi Baru</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include "navbar_karyawan.php"; ?>

<script>
const TOTAL  = <?= (int)$totalBelanja ?>;
let metodeAktif = 'cash';

/* ── Metode Tab ── */
function setMetode(m, el) {
    metodeAktif = m;
    document.getElementById('inputMetode').value = m;

    document.querySelectorAll('.metode-tab').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');

    document.getElementById('panelCash').style.display = m === 'cash'  ? '' : 'none';
    document.getElementById('panelQris').style.display = m === 'qris'  ? '' : 'none';
    document.getElementById('panelBon').style.display  = m === 'bon'   ? '' : 'none';

    // label tombol
    const labels = { cash:'Bayar Sekarang', qris:'Konfirmasi QRIS', bon:'Catat Bon / Hutang' };
    document.getElementById('btnLabel').textContent = labels[m] || 'Bayar';

    hitungKembalian();
}

/* ── Kembalian ── */
const uangInput = document.getElementById('uangTunai');
const kemEl     = document.getElementById('kembalianVal');
const btnBayar  = document.getElementById('btnBayar');

function formatRp(n) {
    return 'Rp ' + Math.max(0,n).toLocaleString('id-ID');
}

function hitungKembalian() {
    if (metodeAktif !== 'cash') {
        if (btnBayar) { btnBayar.disabled = false; btnBayar.style.opacity = '1'; }
        return;
    }
    const val = parseFloat(uangInput?.value || '0');
    const selisih = val - TOTAL;
    if (kemEl) {
        kemEl.textContent = formatRp(selisih);
        kemEl.className   = 'kembalian-val' + (selisih < 0 ? ' kurang' : '');
    }
    if (btnBayar) {
        const kurang = selisih < 0;
        btnBayar.disabled     = kurang;
        btnBayar.style.opacity = kurang ? '0.4' : '1';
    }
    // chip highlight
    document.querySelectorAll('.chip').forEach(c => {
        c.classList.toggle('active', parseFloat(c.dataset.amount) === val);
    });
}

uangInput?.addEventListener('input', hitungKembalian);
hitungKembalian();

/* ── Chip ── */
function setChip(amount, el) {
    if (uangInput) uangInput.value = amount;
    hitungKembalian();
}

/* ── Submit ── */
function submitForm() {
    const form = document.getElementById('formBayar');
    if (!form) return;

    if (metodeAktif === 'bon') {
        const nama = document.getElementById('namaPelanggan')?.value?.trim();
        if (!nama) {
            document.getElementById('namaPelanggan')?.focus();
            return;
        }
    }
    form.submit();
}

/* ── Tutup overlay ── */
document.getElementById('successOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>
</body>
</html>