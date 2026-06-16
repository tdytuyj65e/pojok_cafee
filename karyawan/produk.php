<?php
session_start();
include "../koneksi.php";

/* =========================
   DATA PRODUK
   ========================= */
$query = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

/* =========================
   TAMBAH PRODUK
   ========================= */
if (isset($_POST['tambah'])) {

    $nama  = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga = (int) $_POST['harga'];
    $stok  = (int) $_POST['stok'];

    $foto = $_FILES['foto']['name'] ?? '';
    $tmp  = $_FILES['foto']['tmp_name'] ?? '';

    $folder = "../uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $newName = null;

    if (!empty($foto) && $tmp != "") {

        $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            die("❌ Format gambar tidak valid!");
        }

        $newName = uniqid() . "_" . time() . "." . $ext;

        if (!move_uploaded_file($tmp, $folder . $newName)) {
            die("❌ Upload gagal!");
        }
    }

    $sql = "INSERT INTO products (nama_produk, harga, stok, foto)
            VALUES ('$nama', $harga, $stok, " .
            ($newName ? "'$newName'" : "NULL") . ")";

    mysqli_query($conn, $sql);

    header("Location: produk.php");
    exit;
}

/* =========================
   ADD CART (POS)
   ========================= */
if (isset($_POST['add_cart'])) {

    $id = $_POST['id'];

    $q = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $p = mysqli_fetch_assoc($q);

    if ($p) {

        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = [
                'id' => $p['id'],
                'nama' => $p['nama_produk'],
                'harga' => $p['harga'],
                'qty' => 0
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
   USER
   ========================= */
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users LIMIT 1"));

$fotoUser = !empty($user['foto'])
    ? "../uploads/" . $user['foto']
    : "https://via.placeholder.com/100";
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>

<title>Pojok Kafe POS</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

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
        body {
            background-color: #fff8f5;
            -webkit-tap-highlight-color: transparent;
        }
        .custom-shadow {
            box-shadow: 0px 2px 12px rgba(200, 119, 58, 0.12);
        }
        .primary-shadow {
            box-shadow: 0px 4px 12px rgba(200, 119, 58, 0.30);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>

</head>

<body>

<!-- ================= HEADER ================= -->
<header class="fixed top-0 w-full z-50 bg-primary-container text-white h-[56px] flex items-center justify-between px-4 shadow-md">

  <div class="flex items-center gap-2">
    <img src="<?= $fotoUser ?>" class="w-8 h-8 rounded-full object-cover border">
    <h1 class="font-bold">Pojok Kafe</h1>
  </div>

  <span class="material-symbols-outlined">notifications</span>
</header>

<!-- ================= MAIN ================= -->
<main class="pt-[72px] pb-[90px] px-4 space-y-4">

<div class="flex justify-center items-center">
  <h2 class="text-lg font-bold text-center w-full">
    Manajemen Produk
  </h2>
</div>
    
  </div>

  <!-- SEARCH -->
<div class="flex justify-center gap-3">

  <input class="h-[42px] w-[400px] px-3 text-sm rounded-xl border bg-white"
  placeholder="Cari produk...">

  <button onclick="openModal()"
    class="bg-primary text-white px-4 py-2 rounded-lg flex items-center gap-2">

    <span class="material-symbols-outlined text-[18px]">add</span>

  </button>

</div>

  <!-- FILTER -->
  <div class="flex justify-center gap-2">
    <button class="px-4 py-2 rounded-full bg-primary text-white">Semua</button>
    <button class="px-4 py-2 rounded-full bg-white border">Minuman</button>
    <button class="px-4 py-2 rounded-full bg-white border">Makanan</button>
    
  </div>

  <!-- LIST PRODUK -->
  <div class="space-y-3">

    <?php while($row = mysqli_fetch_assoc($query)) : ?>

    <form method="POST" action="transaksi.php"
          class="bg-white p-3 rounded-xl flex items-center gap-3 shadow hover:scale-[1.01] transition">

      <input type="hidden" name="id" value="<?= $row['id'] ?>">

      <button type="submit" name="add_cart"
              class="flex items-center gap-3 w-full text-left">

        <img src="../uploads/<?= $row['foto'] ?>"
             class="w-[56px] h-[56px] rounded-lg object-cover">

        <div class="flex-1">
          <h3 class="font-semibold"><?= $row['nama_produk'] ?></h3>
          <p class="text-primary font-bold">
            Rp <?= number_format($row['harga'],0,',','.') ?>
          </p>
        </div>

        <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">
          <?= $row['stok'] ?> Stok
        </span>

      </button>

    </form>

    <?php endwhile; ?>

  </div>
</main>

<!-- ================= FLOATING BUTTON ================= -->
<!-- FLOATING CHART (SAFE POSITION) -->

<a href="transaksi.php"
   class="fixed top-[72px] left-4 z-40 w-[70px] h-[70px] bg-white shadow-xl rounded-xl flex flex-col items-center justify-center border hover:scale-105 transition">

    <span class="material-symbols-outlined text-[#8e4a0e] text-[26px]">
        shopping_cart
    </span>

    <p class="text-[9px] font-semibold text-gray-600">
        Cart
    </p>

</a>
<!-- ================= MODAL ================= -->
<div id="modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">

  <div class="bg-white w-[90%] max-w-md p-4 rounded-xl space-y-3">

    <div class="flex justify-between">
      <h3 class="font-bold">Tambah Produk</h3>
      <button onclick="closeModal()">✕</button>
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-3">

      <input name="nama_produk" placeholder="Nama Produk"
        class="w-full border px-3 py-2 rounded-lg text-sm">

      <input name="harga" type="number" placeholder="Harga"
        class="w-full border px-3 py-2 rounded-lg text-sm">

      <input name="stok" type="number" placeholder="Stok"
        class="w-full border px-3 py-2 rounded-lg text-sm">

      <input type="file" name="foto"
        class="w-full border px-3 py-2 rounded-lg text-sm">

      <button name="tambah"
        class="w-full bg-primary text-white py-2 rounded-lg">
        Simpan
      </button>

    </form>

  </div>
</div>
<!-- NAVBAR -->
<?php include "navbar_karyawan.php"; ?>

<!-- ================= SCRIPT ================= -->
<script>
function openModal(){
  document.getElementById('modal').classList.remove('hidden');
}
function closeModal(){
  document.getElementById('modal').classList.add('hidden');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('miniChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_reverse($labels ?? [])) ?>,
        datasets: [{
            data: <?= json_encode(array_reverse($data ?? [])) ?>,
            borderColor: '#8e4a0e',
            backgroundColor: 'rgba(142,74,14,0.2)',
            tension: 0.4,
            fill: true,
            pointRadius: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { display: false },
            y: { display: false }
        }
    }
});
</script>


</body>
</html>