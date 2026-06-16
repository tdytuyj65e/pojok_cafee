<?php
session_start();
include "../koneksi.php";

/* =========================
   INIT CART
========================= */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* =========================
   ADD TO CART (klik produk)
========================= */
if (isset($_POST['add'])) {

    $id = $_POST['product_id'];

    $q = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $p = mysqli_fetch_assoc($q);

    if ($p) {

        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = [
                'id' => $p['id'],
                'nama' => $p['nama_produk'],
                'harga' => $p['harga'],
                'qty' => 0,
                'foto' => $p['foto']
            ];
        }

        $_SESSION['cart'][$id]['qty'] += 1;
        $_SESSION['cart'][$id]['subtotal'] =
            $_SESSION['cart'][$id]['qty'] * $p['harga'];
    }

    header("Location: transaksi.php");
    exit;
}

/* =========================
   REMOVE ITEM
========================= */
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: transaksi.php");
    exit;
}

/* =========================
   BAYAR LANGSUNG (tanpa uang)
========================= */
if (isset($_POST['bayar'])) {

    $total = 0;
    foreach ($_SESSION['cart'] as $c) {
        $total += $c['subtotal'];
    }

    $kode = "TRX" . date("YmdHis");

    mysqli_query($conn, "INSERT INTO transactions (kode_transaksi, user_id, total, uang_diterima, kembalian)
    VALUES ('$kode', 1, '$total', '$total', 0)");

    $trx_id = mysqli_insert_id($conn);

    foreach ($_SESSION['cart'] as $c) {

        mysqli_query($conn, "INSERT INTO transaction_details
        (transaction_id, product_id, qty, harga_satuan, subtotal)
        VALUES
        ('$trx_id', '{$c['id']}', '{$c['qty']}', '{$c['harga']}', '{$c['subtotal']}')");

        mysqli_query($conn, "UPDATE products 
        SET stok = stok - {$c['qty']}
        WHERE id = {$c['id']}");
    }

    $_SESSION['cart'] = [];

  $_SESSION['last_trx'] = [
    'kode' => $kode,
    'kasir' => $_SESSION['nama_lengkap'] ?? 'Kasir',
    'total' => $total,
    'items' => $items
];

header("Location: berhasil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<script src="https://cdn.tailwindcss.com">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
</script>

<!-- ================= TAILWIND CONFIG (TIDAK DIHAPUS) ================= -->
<script>
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
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
.card {
  box-shadow: 0px 2px 12px rgba(0,0,0,0.08);
}
</style>

</head>

<body class="bg-background">

<!-- ================= HEADER ================= -->
<header class="fixed top-0 w-full h-[56px] bg-white shadow flex items-center justify-center z-50">
    <h1 class="font-bold text-lg">Keranjang</h1>
</header>

<main class="pt-[70px] px-4 space-y-4">

<!-- ================= PRODUK LIST ================= -->
<section class="space-y-3">

<?php
$q = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
while ($p = mysqli_fetch_assoc($q)) :
?>

<form method="POST">

    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">

    <button name="add"
        class="w-full bg-white card rounded-xl p-3 flex items-center gap-3 active:scale-[0.98] transition">

        <img src="../uploads/<?= $p['foto'] ?>"
             class="w-[60px] h-[60px] rounded-lg object-cover">

        <div class="flex-1 text-left">
            <h2 class="font-semibold"><?= $p['nama_produk'] ?></h2>
            <p class="text-primary font-bold">
                Rp <?= number_format($p['harga'],0,',','.') ?>
            </p>
        </div>

        <div class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
            <?= $p['stok'] ?>
        </div>

    </button>

</form>

<?php endwhile; ?>

</section>

<!-- ================= CART ================= -->
<section class="mt-5 bg-white p-4 rounded-xl card">

<h2 class="font-bold mb-3">Keranjang</h2>

<?php $total = 0; ?>

<?php foreach ($_SESSION['cart'] as $c): ?>
    <div class="flex justify-between border-b py-2">
        <div>
            <p class="font-semibold"><?= $c['nama'] ?></p>
            <p class="text-sm text-gray-500">
                <?= $c['qty'] ?> x <?= number_format($c['harga']) ?>
            </p>
        </div>

        <div class="text-right">
            <p class="font-bold"><?= number_format($c['subtotal']) ?></p>
            <a href="?remove=<?= $c['id'] ?>" class="text-red-500 text-xs">hapus</a>
        </div>
    </div>

    <?php $total += $c['subtotal']; ?>
<?php endforeach; ?>

<hr class="my-3">

<div class="flex justify-between font-bold">
    <span>Total</span>
    <span>Rp <?= number_format($total) ?></span>
</div>

<form method="POST" class="mt-3 flex justify-center">
    <button name="bayar"
        class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm">
        BAYAR SEKARANG
    </button>
</form>

</main>
<?php include "navbar_karyawan.php"; ?>
</body>
</html>