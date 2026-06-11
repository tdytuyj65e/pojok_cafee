<?php
include "../koneksi.php";

/* =========================
   DATA PRODUK
   ========================= */
$query = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

/* =========================
   TAMBAH PRODUK
   ========================= */
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];

    if ($foto != "") {
        move_uploaded_file($tmp, "../uploads/".$foto);
    }

    mysqli_query($conn, "INSERT INTO products (nama_produk, harga, stok, foto)
    VALUES ('$nama','$harga','$stok','$foto')");

    header("Location: produk.php");
    exit;
}

/* =========================
   USER
   ========================= */
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users LIMIT 1"));
$fotoUser = !empty($user['foto']) ? "../uploads/".$user['foto'] : "https://via.placeholder.com/100";
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>

<title>Pojok Kafe POS</title>

<!-- TAILWIND (TIDAK DIUBAH) -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<!-- 🔴 CONFIG TAILWIND ASLI KAMU (TIDAK DIUBAH SAMA SEKALI) -->
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
body{background:#fff8f5}
</style>
</head>

<body>

<!-- ================= HEADER ================= -->
<header class="fixed top-0 w-full z-50 bg-primary-container text-on-primary-container h-[56px] flex items-center justify-between px-md shadow-md">

  <div class="flex items-center gap-sm">
    <img src="<?= $fotoUser ?>" class="w-8 h-8 rounded-full object-cover border">
    <h1 class="font-headline-md font-bold">Pojok Kafe</h1>
  </div>

  <span class="material-symbols-outlined">notifications</span>
</header>

<!-- ================= MAIN ================= -->
<main class="pt-[72px] pb-[90px] px-md space-y-md">

  <div class="flex justify-between items-center">
    <h2 class="text-lg font-bold">Manajemen Produk</h2>

    <!-- BUTTON TAMBAH -->
    <button onclick="openModal()"
      class="bg-primary text-white px-4 py-2 rounded-lg flex items-center gap-2">
      <span class="material-symbols-outlined text-[18px]">add</span>
      Tambah
    </button>
  </div>

  <!-- SEARCH -->
<div class="flex justify-center">
    <input class="h-[42px] w-[400px] md:w-[400px] px-3 text-sm rounded-xl border bg-white"
    placeholder="Cari produk...">
</div>

  <!-- FILTER CENTER -->
  <div class="flex justify-center">
    <div class="flex gap-2 overflow-x-auto">
      <button class="px-4 py-2 rounded-full bg-primary text-white">Semua</button>
      <button class="px-4 py-2 rounded-full bg-white border">Minuman</button>
      <button class="px-4 py-2 rounded-full bg-white border">Makanan</button>
    </div>
  </div>

  <!-- LIST PRODUK -->
  <div class="space-y-3">

    <?php while($row = mysqli_fetch_assoc($query)) : ?>
    <div class="bg-white p-3 rounded-xl flex items-center gap-3 shadow">

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

    </div>
    <?php endwhile; ?>

  </div>
</main>

<!-- ================= MODAL ADD (HIDDEN) ================= -->
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

<!-- ================= NAVBAR ================= -->
<?php include "navbar.php"; ?>

<!-- ================= SCRIPT ================= -->
<script>
function openModal(){
  document.getElementById('modal').classList.remove('hidden');
}
function closeModal(){
  document.getElementById('modal').classList.add('hidden');
}
</script>

</body>
</html>