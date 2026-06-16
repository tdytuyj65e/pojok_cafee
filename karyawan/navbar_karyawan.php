<!-- navbar.php -->

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

<nav class="fixed bottom-0 left-0 right-0 z-50 h-16 bg-white border-t border-gray-200 shadow-lg flex justify-around items-center">

```
<!-- Dashboard -->
<a href="dashboard.php" class="flex flex-col items-center text-orange-600 hover:scale-110 transition">
    <span class="material-symbols-outlined">shopping_cart</span>
    <span class="text-xs">Transaksi</span>
</a>

<!-- Produk -->
<a href="produk.php" class="flex flex-col items-center text-gray-500 hover:text-orange-600 hover:scale-110 transition">
    <span class="material-symbols-outlined">inventory_2</span>
    <span class="text-xs">Produk</span>
</a>

<!-- Laporan -->
<a href="laporan.php" class="flex flex-col items-center text-gray-500 hover:text-orange-600 hover:scale-110 transition">
    <span class="material-symbols-outlined">bar_chart</span>
    <span class="text-xs">Laporan</span>
</a>

<!-- Profil -->
<a href="profil.php" class="flex flex-col items-center text-gray-500 hover:text-orange-600 hover:scale-110 transition">
    <span class="material-symbols-outlined">person</span>
    <span class="text-xs">Profil</span>
</a>

<!-- Logout -->
<button
    onclick="openLogoutModal()"
    class="flex flex-col items-center text-red-500 hover:scale-110 transition"
>
    <span class="material-symbols-outlined">logout</span>
    <span class="text-xs">Logout</span>
</button>
```

</nav>

<!-- MODAL LOGOUT -->

<div
    id="logoutModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[999] flex items-center justify-center p-4"
>

```
<div
    id="logoutCard"
    class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl scale-75 opacity-0 transition-all duration-300"
>

    <!-- Header -->
    <div class="p-6 text-center">

        <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
            <span class="material-symbols-outlined text-red-500 text-5xl">
                logout
            </span>
        </div>

        <h2 class="text-xl font-bold text-gray-800">
            Logout Akun
        </h2>

        <p class="text-gray-500 mt-2">
            Apakah Anda yakin ingin keluar dari aplikasi?
        </p>

    </div>

    <!-- Tombol -->
    <div class="grid grid-cols-2 border-t">

        <button
            onclick="closeLogoutModal()"
            class="py-4 font-medium text-gray-600 hover:bg-gray-100 transition"
        >
            Batal
        </button>

        <a
            href="../login/logout.php"
            class="py-4 text-center font-semibold text-white bg-red-500 hover:bg-red-600 transition"
        >
            Logout
        </a>

    </div>

</div>
```

</div>

<script>
const modal = document.getElementById('logoutModal');
const card = document.getElementById('logoutCard');

function openLogoutModal() {

    modal.classList.remove('hidden');

    setTimeout(() => {
        card.classList.remove('scale-75', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeLogoutModal() {

    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-75', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 250);
}

modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeLogoutModal();
    }
});
</script>
