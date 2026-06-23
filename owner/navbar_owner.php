<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#16a34a">
<!-- SIDEBAR DESKTOP -->
<aside class="hidden lg:flex lg:flex-col w-64 bg-white shadow-lg fixed h-screen">

    <div class="p-6 border-b">

        <h1 class="text-2xl font-bold text-orange-600">
            ☕ Pojok Kafe
        </h1>

        <p class="text-sm text-gray-500 mt-1">
            Owner Panel
        </p>

    </div>

    <nav class="flex-1 p-4 space-y-2">

        <a href="dashboard.php"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
        <?= $current_page == 'dashboard.php'
            ? 'bg-orange-500 text-white'
            : 'hover:bg-orange-100 text-gray-700' ?>">
            📊 Dashboard
        </a>

        <a href="produk.php"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
        <?= $current_page == 'produk.php'
            ? 'bg-orange-500 text-white'
            : 'hover:bg-orange-100 text-gray-700' ?>">
            ☕ Produk
        </a>

        <a href="karyawan.php"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
        <?= $current_page == 'karyawan.php'
            ? 'bg-orange-500 text-white'
            : 'hover:bg-orange-100 text-gray-700' ?>">
            👨‍💼 Karyawan
        </a>

        <a href="laporan.php"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
        <?= $current_page == 'laporan.php'
            ? 'bg-orange-500 text-white'
            : 'hover:bg-orange-100 text-gray-700' ?>">
            📈 Laporan
        </a>

        <a href="profil_owner.php"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
        <?= $current_page == 'profil.php'
            ? 'bg-orange-500 text-white'
            : 'hover:bg-orange-100 text-gray-700' ?>">
            👤 Profil
        </a>

    </nav>

    <div class="p-4 border-t">

        <a href="../login/logout.php"
        class="block text-center bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-semibold">
            Logout
        </a>

    </div>

</aside>

<!-- NAVBAR MOBILE -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-50">

    <div class="grid grid-cols-5 text-center">

        <a href="dashboard.php" class="py-3 text-sm">
            📊
        </a>

        <a href="produk.php" class="py-3 text-sm">
            ☕
        </a>

        <a href="kategori.php" class="py-3 text-sm">
            📂
        </a>

        <a href="karyawan.php" class="py-3 text-sm">
            👨‍💼
        </a>

        <a href="profil.php" class="py-3 text-sm">
            👤
        </a>

    </div>

</nav>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>