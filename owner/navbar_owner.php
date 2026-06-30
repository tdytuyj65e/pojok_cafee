<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#16a34a">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        onclick="return konfirmasiLogout(event)"
        class="block text-center bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-semibold">
            Logout
        </a>

    </div>

</aside>

<!-- NAVBAR MOBILE -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-50 pb-[env(safe-area-inset-bottom)]">

    <div class="grid grid-cols-6 text-center">

        <a href="dashboard.php"
        class="flex flex-col items-center justify-center gap-0.5 py-2 transition
        <?= $current_page == 'dashboard.php'
            ? 'text-orange-600'
            : 'text-gray-500 active:text-orange-500' ?>">
            <span class="text-xl leading-none">📊</span>
            <span class="text-[10px] font-medium leading-none">Dashboard</span>
        </a>

        <a href="produk.php"
        class="flex flex-col items-center justify-center gap-0.5 py-2 transition
        <?= $current_page == 'produk.php'
            ? 'text-orange-600'
            : 'text-gray-500 active:text-orange-500' ?>">
            <span class="text-xl leading-none">☕</span>
            <span class="text-[10px] font-medium leading-none">Produk</span>
        </a>

        <a href="karyawan.php"
        class="flex flex-col items-center justify-center gap-0.5 py-2 transition
        <?= $current_page == 'karyawan.php'
            ? 'text-orange-600'
            : 'text-gray-500 active:text-orange-500' ?>">
            <span class="text-xl leading-none">👨‍💼</span>
            <span class="text-[10px] font-medium leading-none">Karyawan</span>
        </a>

        <a href="laporan.php"
        class="flex flex-col items-center justify-center gap-0.5 py-2 transition
        <?= $current_page == 'laporan.php'
            ? 'text-orange-600'
            : 'text-gray-500 active:text-orange-500' ?>">
            <span class="text-xl leading-none">📈</span>
            <span class="text-[10px] font-medium leading-none">Laporan</span>
        </a>

        <a href="profil_owner.php"
        class="flex flex-col items-center justify-center gap-0.5 py-2 transition
        <?= $current_page == 'profil.php'
            ? 'text-orange-600'
            : 'text-gray-500 active:text-orange-500' ?>">
            <span class="text-xl leading-none">👤</span>
            <span class="text-[10px] font-medium leading-none">Profil</span>
        </a>

        <a href="../login/logout.php"
        onclick="return konfirmasiLogout(event)"
        class="flex flex-col items-center justify-center gap-0.5 py-2 transition text-red-500 active:text-red-700">
            <span class="text-xl leading-none">🚪</span>
            <span class="text-[10px] font-medium leading-none">Logout</span>
        </a>

    </div>

</nav>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}

function konfirmasiLogout(event) {
    event.preventDefault();
    const targetUrl = event.currentTarget.href;

    Swal.fire({
        title: 'Yakin mau logout?',
        text: 'Kamu akan keluar dari Owner Panel.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = targetUrl;
        }
    });

    return false;
}
</script>