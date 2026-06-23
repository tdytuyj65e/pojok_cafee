<?php
session_start();
include "../koneksi.php";

/* =========================
   CEK LOGIN
   ========================= */
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['id'];

/* =========================
   UBAH JUMLAH ITEM (+/-)
   ========================= */
if (isset($_POST['update_qty'])) {

    $id   = $_POST['id'];
    $aksi = $_POST['aksi']; // 'tambah' | 'kurang'

    if (isset($_SESSION['cart'][$id])) {

        if ($aksi === 'tambah') {
            $_SESSION['cart'][$id]['qty'] += 1;
        } else {
            $_SESSION['cart'][$id]['qty'] -= 1;
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

/* =========================
   HAPUS SATU ITEM
   ========================= */
if (isset($_POST['hapus_item'])) {
    $id = $_POST['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: transaksi.php");
    exit;
}

/* =========================
   KOSONGKAN KERANJANG
   ========================= */
if (isset($_POST['kosongkan'])) {
    unset($_SESSION['cart']);
    header("Location: transaksi.php");
    exit;
}

/* =========================
   PROSES PEMBAYARAN
   NOTE: sesuaikan nama kolom di bawah ini
   dengan struktur tabel transactions /
   transaction_details di database kamu.
   ========================= */
$errorBayar = "";

if (isset($_POST['bayar'])) {

    if (empty($_SESSION['cart'])) {
        $errorBayar = "Keranjang masih kosong!";
    } else {

        $totalBayar = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalBayar += $item['subtotal'];
        }

        $uangTunai = isset($_POST['uang_tunai']) ? (int) $_POST['uang_tunai'] : 0;

        if ($uangTunai < $totalBayar) {
            $errorBayar = "Uang tunai kurang dari total belanja!";
        } else {

            $tanggal = date('Y-m-d H:i:s');

            mysqli_query($conn, "
                INSERT INTO transactions (user_id, total, tanggal)
                VALUES ('$userId', '$totalBayar', '$tanggal')
            ");
            $transactionId = mysqli_insert_id($conn);

            foreach ($_SESSION['cart'] as $item) {
                $pid      = (int) $item['id'];
                $qty      = (int) $item['qty'];
                $harga    = (int) $item['harga'];
                $subtotal = (int) $item['subtotal'];

                mysqli_query($conn, "
                    INSERT INTO transaction_details (transaction_id, product_id, qty, harga, subtotal)
                    VALUES ('$transactionId', '$pid', '$qty', '$harga', '$subtotal')
                ");

                mysqli_query($conn, "
                    UPDATE products SET stok = stok - $qty WHERE id = '$pid'
                ");
            }

            $_SESSION['trx_sukses'] = [
                'total'     => $totalBayar,
                'bayar'     => $uangTunai,
                'kembalian' => $uangTunai - $totalBayar,
            ];

            unset($_SESSION['cart']);

            header("Location: transaksi.php");
            exit;
        }
    }
}

/* =========================
   FLASH SUKSES (tampil sekali setelah bayar)
   ========================= */
$trxSukses = $_SESSION['trx_sukses'] ?? null;
unset($_SESSION['trx_sukses']);

/* =========================
   DATA KERANJANG UNTUK TAMPILAN
   ========================= */
$cart = $_SESSION['cart'] ?? [];

$subtotalBelanja = 0;
foreach ($cart as $item) {
    $subtotalBelanja += $item['subtotal'];
}
$diskon       = 0; // belum ada fitur diskon
$totalBelanja = $subtotalBelanja - $diskon;

/* saran nominal uang tunai (kelipatan wajar di atas total) */
function pk_roundUp($value, $nearest) {
    if ($value <= 0) return $nearest;
    return (int) (ceil($value / $nearest) * $nearest);
}

$saranUang = [];
if ($totalBelanja > 0) {
    $saranUang[] = $totalBelanja;
    foreach ([5000, 10000, 50000, 100000] as $kelipatan) {
        $bulat = pk_roundUp($totalBelanja, $kelipatan);
        if ($bulat > $totalBelanja || $kelipatan == 5000) {
            $saranUang[] = $bulat;
        }
    }
    $saranUang = array_values(array_unique($saranUang));
    sort($saranUang);
    $saranUang = array_slice($saranUang, 0, 4);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>

<title>Keranjang | Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "on-tertiary-fixed-variant": "#713700",
                      "tertiary-container": "#a9632c",
                      "inverse-surface": "#402c1f",
                      "on-primary-fixed-variant": "#723600",
                      "surface-container-lowest": "#ffffff",
                      "on-secondary": "#ffffff",
                      "on-secondary-fixed": "#281807",
                      "inverse-on-surface": "#ffede4",
                      "error": "#ba1a1a",
                      "on-secondary-fixed-variant": "#57432e",
                      "surface-dim": "#f5d3c0",
                      "on-primary-fixed": "#311400",
                      "on-primary": "#ffffff",
                      "primary-fixed-dim": "#ffb786",
                      "on-tertiary-container": "#fffbff",
                      "on-surface-variant": "#544339",
                      "on-surface": "#29170c",
                      "secondary-fixed-dim": "#dec1a6",
                      "on-error-container": "#93000a",
                      "tertiary": "#8b4b15",
                      "inverse-primary": "#ffb786",
                      "on-tertiary": "#ffffff",
                      "on-background": "#29170c",
                      "outline-variant": "#d9c2b5",
                      "surface-bright": "#fff8f5",
                      "secondary-container": "#fcddc1",
                      "primary": "#8e4a0e",
                      "surface-container": "#ffeadf",
                      "on-error": "#ffffff",
                      "surface": "#fff8f5",
                      "surface-container-highest": "#fedcc8",
                      "surface-container-high": "#ffe3d3",
                      "secondary-fixed": "#fcddc1",
                      "primary-container": "#ad6126",
                      "tertiary-fixed-dim": "#ffb784",
                      "background": "#fff8f5",
                      "on-tertiary-fixed": "#301400",
                      "error-container": "#ffdad6",
                      "on-secondary-container": "#77604a",
                      "surface-container-low": "#fff1ea",
                      "tertiary-fixed": "#ffdcc5",
                      "on-primary-container": "#fffbff",
                      "outline": "#867368",
                      "surface-variant": "#fedcc8",
                      "secondary": "#705a44",
                      "surface-tint": "#914c10",
                      "primary-fixed": "#ffdcc6"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "xl": "32px",
                      "lg": "24px",
                      "xs": "4px",
                      "md": "16px",
                      "sm": "8px"
              },
              "fontFamily": {
                      "headline-md": ["Plus Jakarta Sans"],
                      "headline-lg": ["Plus Jakarta Sans"],
                      "button-text": ["Plus Jakarta Sans"],
                      "body-md": ["Plus Jakarta Sans"],
                      "label-md": ["Plus Jakarta Sans"]
              },
              "fontSize": {
                      "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                      "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                      "button-text": ["14px", {"lineHeight": "20px", "fontWeight": "600"}],
                      "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                      "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "500"}]
              }
            },
          },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .card-shadow {
            box-shadow: 0px 2px 12px rgba(200, 119, 58, 0.12);
        }
        .button-shadow {
            box-shadow: 0px 4px 12px rgba(200, 119, 58, 0.30);
        }
        body {
            min-height: max(884px, 100dvh);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .qty-btn { transition: transform .12s ease; }
        .qty-btn:active { transform: scale(0.88); }
        .chip-btn { transition: background .15s ease, border-color .15s ease, color .15s ease; }
        .chip-btn.active {
            background: theme('colors.secondary-container');
            border-color: theme('colors.primary');
            color: theme('colors.primary');
        }
        #successOverlay { transition: opacity .2s ease; }
        .item-card { transition: opacity .15s ease, transform .15s ease; }
    </style>
</head>

<body class="bg-background font-body-md text-on-background min-h-screen pb-40">

<!-- Top AppBar -->
<header class="bg-primary-container text-on-primary-container shadow-md fixed top-0 w-full z-50 h-[56px] flex items-center justify-between px-md">
    <a href="produk.php" class="active:scale-95 transition-transform" aria-label="Kembali">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    <h1 class="font-headline-md text-headline-md font-bold">
        Keranjang
        <?php if (count($cart) > 0): ?>
            <span class="text-sm font-normal opacity-80">(<?= count($cart) ?>)</span>
        <?php endif; ?>
    </h1>

    <form method="POST" onsubmit="return confirm('Kosongkan semua item di keranjang?');">
        <button type="submit" name="kosongkan" class="active:scale-95 transition-transform" aria-label="Kosongkan keranjang">
            <span class="material-symbols-outlined">delete</span>
        </button>
    </form>
</header>

<main class="pt-[72px] px-md space-y-md">

<?php if (empty($cart)): ?>

    <!-- ===== EMPTY STATE ===== -->
    <section class="flex flex-col items-center justify-center text-center py-20 space-y-3">
        <span class="material-symbols-outlined text-[56px] text-outline-variant">shopping_cart</span>
        <h2 class="font-headline-md text-[16px] text-on-surface">Keranjang masih kosong</h2>
        <p class="text-on-surface-variant font-body-md text-sm max-w-xs">
            Pilih produk dulu di halaman Produk untuk mulai transaksi.
        </p>
        <a href="produk.php" class="mt-2 px-5 h-11 rounded-xl bg-primary text-on-primary font-button-text flex items-center button-shadow">
            Pilih Produk
        </a>
    </section>

<?php else: ?>

    <!-- Cart Items List -->
    <section class="space-y-sm">

        <?php foreach ($cart as $id => $item):
            $namaItem = htmlspecialchars($item['nama']);
        ?>
        <div class="item-card bg-surface-container-lowest card-shadow rounded-xl p-md flex items-center gap-md">

            <div class="flex-1 min-w-0">
                <h3 class="font-headline-md text-[16px] text-on-surface truncate"><?= $namaItem ?></h3>
                <p class="text-on-surface-variant font-label-md">
                    Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                    <span class="opacity-60">x <?= $item['qty'] ?></span>
                </p>
                <p class="font-button-text text-primary mt-0.5">
                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                </p>
            </div>

            <div class="flex items-center gap-sm flex-shrink-0">

                <form method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="aksi" value="kurang">
                    <button type="submit" name="update_qty"
                        class="qty-btn w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary"
                        aria-label="Kurangi jumlah">
                        <span class="material-symbols-outlined text-[18px]">
                            <?= $item['qty'] <= 1 ? 'delete' : 'remove' ?>
                        </span>
                    </button>
                </form>

                <span class="font-button-text min-w-[20px] text-center"><?= $item['qty'] ?></span>

                <form method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="aksi" value="tambah">
                    <button type="submit" name="update_qty"
                        class="qty-btn w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary"
                        aria-label="Tambah jumlah">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                    </button>
                </form>

            </div>
        </div>
        <?php endforeach; ?>

    </section>

    <!-- Order Summary Card -->
    <section class="bg-surface-container-lowest card-shadow rounded-xl p-md space-y-sm">
        <h2 class="font-headline-md text-[16px] text-on-surface-variant">Ringkasan Pesanan</h2>

        <div class="flex justify-between items-center text-on-surface">
            <span class="font-body-md">Subtotal</span>
            <span class="font-body-md">Rp <?= number_format($subtotalBelanja, 0, ',', '.') ?></span>
        </div>

        <div class="flex justify-between items-center text-on-surface">
            <span class="font-body-md">Diskon</span>
            <span class="font-body-md"><?= $diskon > 0 ? '-Rp '.number_format($diskon,0,',','.') : '-' ?></span>
        </div>

        <div class="pt-sm border-t border-outline-variant flex justify-between items-center">
            <span class="font-headline-md text-[16px] text-on-surface">Total</span>
            <span class="font-headline-lg text-headline-lg text-primary" id="totalBelanjaText">
                Rp <?= number_format($totalBelanja, 0, ',', '.') ?>
            </span>
        </div>
    </section>

    <!-- Payment Section -->
    <form method="POST" id="formBayar" class="space-y-md">

        <input type="hidden" name="bayar" value="1">

        <section class="space-y-md">
            <h2 class="font-headline-md text-[16px] text-on-surface">Pembayaran Tunai</h2>

            <?php if (!empty($errorBayar)): ?>
            <div class="bg-error-container text-on-error-container px-md py-2.5 rounded-xl text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <?= htmlspecialchars($errorBayar) ?>
            </div>
            <?php endif; ?>

            <div class="relative group">
                <span class="absolute left-md top-1/2 -translate-y-1/2 text-primary font-headline-md">Rp</span>
                <input id="uangTunai" name="uang_tunai"
                    class="w-full h-[56px] rounded-xl border-2 border-primary bg-surface text-right px-md font-headline-lg text-primary focus:ring-0 focus:outline-none transition-all"
                    placeholder="0" type="number" min="0"
                    value="<?= $totalBelanja ?>"/>
            </div>

            <?php if (!empty($saranUang)): ?>
            <div class="flex flex-wrap gap-sm" id="chipGroup">
                <?php foreach ($saranUang as $nominal): ?>
                <button type="button" data-amount="<?= $nominal ?>"
                    class="chip-btn px-md h-10 rounded-full border border-outline text-on-surface-variant font-label-md">
                    Rp <?= number_format($nominal, 0, ',', '.') ?>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="bg-surface-container rounded-xl p-md flex justify-between items-center border border-primary-container/30">
                <span class="font-headline-md text-[16px] text-on-primary-container">Kembalian</span>
                <span class="font-headline-lg text-headline-lg text-on-primary-container" id="kembalianText">
                    Rp 0
                </span>
            </div>
        </section>

        <!-- Bottom Sticky Area -->
        <footer class="fixed bottom-0 left-0 w-full bg-surface px-md pb-md pt-sm border-t border-outline-variant space-y-sm z-40">
            <button id="btnSelesai" type="submit"
                class="w-full h-[56px] bg-primary text-on-primary rounded-xl font-headline-md button-shadow active:scale-95 transition-all flex items-center justify-center">
                SELESAIKAN TRANSAKSI
            </button>
            <p class="text-center text-on-surface-variant font-label-md opacity-70">
                Pastikan jumlah sudah benar sebelum menyelesaikan transaksi.
            </p>
        </footer>

    </form>

<?php endif; ?>

</main>

<!-- ===== OVERLAY SUKSES ===== -->
<?php if ($trxSukses): ?>
<div id="successOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] px-md">
    <div class="bg-surface-container-lowest rounded-2xl p-lg w-full max-w-sm text-center space-y-3">

        <div class="w-16 h-16 mx-auto rounded-full bg-secondary-container flex items-center justify-center">
            <span class="material-symbols-outlined text-[32px] text-primary">check_circle</span>
        </div>

        <h2 class="font-headline-md text-[18px] text-on-surface">Transaksi Berhasil</h2>

        <div class="text-left bg-surface-container rounded-xl p-md space-y-1.5 text-sm">
            <div class="flex justify-between"><span class="text-on-surface-variant">Total</span><span class="font-semibold">Rp <?= number_format($trxSukses['total'],0,',','.') ?></span></div>
            <div class="flex justify-between"><span class="text-on-surface-variant">Tunai</span><span class="font-semibold">Rp <?= number_format($trxSukses['bayar'],0,',','.') ?></span></div>
            <div class="flex justify-between border-t border-outline-variant pt-1.5 mt-1"><span class="text-on-surface-variant">Kembalian</span><span class="font-bold text-primary">Rp <?= number_format($trxSukses['kembalian'],0,',','.') ?></span></div>
        </div>

        <a href="produk.php" class="block w-full h-12 rounded-xl bg-primary text-on-primary font-button-text flex items-center justify-center button-shadow">
            Transaksi Baru
        </a>
    </div>
</div>
<?php endif; ?>

<script>
// micro-interaction tombol
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('touchstart', () => button.classList.add('opacity-80'));
    button.addEventListener('touchend', () => button.classList.remove('opacity-80'));
});

// hitung kembalian secara real-time + validasi tombol bayar
const totalBelanja = <?= (int) $totalBelanja ?>;
const uangTunai = document.getElementById('uangTunai');
const kembalianText = document.getElementById('kembalianText');
const btnSelesai = document.getElementById('btnSelesai');
const chipGroup = document.getElementById('chipGroup');

function formatRupiah(num) {
    return 'Rp ' + Math.max(0, num).toLocaleString('id-ID');
}

function hitungKembalian() {
    if (!uangTunai) return;
    const nilai = parseInt(uangTunai.value || '0', 10);
    const kembalian = nilai - totalBelanja;

    kembalianText.textContent = formatRupiah(kembalian);

    if (btnSelesai) {
        btnSelesai.disabled = kembalian < 0;
        btnSelesai.classList.toggle('opacity-50', kembalian < 0);
    }

    if (chipGroup) {
        chipGroup.querySelectorAll('.chip-btn').forEach(chip => {
            chip.classList.toggle('active', parseInt(chip.dataset.amount, 10) === nilai);
        });
    }
}

if (uangTunai) {
    uangTunai.addEventListener('input', hitungKembalian);
    hitungKembalian();
}

if (chipGroup) {
    chipGroup.querySelectorAll('.chip-btn').forEach(chip => {
        chip.addEventListener('click', () => {
            uangTunai.value = chip.dataset.amount;
            hitungKembalian();
        });
    });
}

// tutup overlay sukses kalau diklik area luar kartu
const overlay = document.getElementById('successOverlay');
if (overlay) {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.remove();
    });
}
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>