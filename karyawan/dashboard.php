<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@100..900&amp;display=swap" rel="stylesheet"/>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
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
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background font-body-md text-on-background selection:bg-primary-container selection:text-on-primary-container">
<!-- TopAppBar -->
<header class="fixed top-0 w-full h-[56px] z-50 bg-primary-container text-on-primary-container shadow-md flex items-center justify-between px-md">
<div class="flex items-center gap-sm">
<div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center overflow-hidden">
<img alt="Cashier Profile" class="w-full h-full object-cover" data-alt="A professional close-up portrait of a friendly Indonesian male cafe worker wearing a minimalist brown apron over a clean white shirt. He is standing in a brightly lit, warm cafe setting with soft focus coffee equipment in the background. The lighting is warm and inviting, consistent with a high-end service brand aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBAjcxKpFbUskJEDYRwJjTF3avc0W4hLqDvNjlzcpVaImPaKfXkk7k3qkCGN-1aP4bOJ5lPUNIbH5Apxof7FzkDKtl4ZfL2f0zAzMxoaRFkA_tLKEB0EnfeMgPYReSaWSrEowDiZKEEBO_yY_TPpEdUBu8MuVChfBMdBL4WpD7Fx5oOoFH9FWArqauBw265hX-J-vIyN2Fy7XQMu7FxkMfALAA5IC8cLNFj0miNdRExfO7lZB1DRbvQKbdfXO79ORPGppQziJSzc_L4"/>
</div>
</div>
<h1 class="font-headline-md text-headline-md font-bold text-on-primary-container">Pojok Kafe</h1>
<button class="material-symbols-outlined text-on-primary-container/80 hover:opacity-90 active:scale-95 transition-transform p-1">
            notifications
        </button>
</header>
<!-- Main Content -->
<main class="pt-[56px] pb-[140px] px-md space-y-lg">
<!-- Greeting Section -->
<section class="mt-lg">
<h2 class="font-headline-md text-[16px] text-on-surface">Halo, Kasir! 👋</h2>
<p class="font-label-md text-[12px] text-on-surface-variant">Senin, 2 Juni 2025</p>
</section>
<!-- Summary Cards (Bento Style Layout) -->
<section class="grid grid-cols-2 gap-sm">
<div class="bg-primary-container p-md rounded-xl shadow-[0px_2px_12px_rgba(200,119,58,0.12)] text-on-primary-container flex flex-col justify-between h-[120px]">
<div class="flex items-center gap-xs">
<span class="material-symbols-outlined text-[20px]">trending_up</span>
<span class="font-label-md">Penjualan</span>
</div>
<div>
<p class="font-label-md opacity-80">Hari Ini</p>
<p class="font-headline-md text-[18px] font-bold">Rp 485.000</p>
</div>
</div>
<div class="bg-surface p-md rounded-xl border border-outline-variant shadow-[0px_2px_12px_rgba(200,119,58,0.12)] text-on-surface flex flex-col justify-between h-[120px]">
<div class="flex items-center gap-xs text-primary">
<span class="material-symbols-outlined text-[20px]">shopping_bag</span>
<span class="font-label-md text-on-surface">Total</span>
</div>
<div>
<p class="font-label-md text-on-surface-variant">Transaksi</p>
<p class="font-headline-md text-[18px] font-bold">12 Transaksi</p>
</div>
</div>
</section>
<!-- Category Filter -->
<section class="-mx-md px-md overflow-x-auto hide-scrollbar flex gap-sm py-xs">
<button class="px-lg py-sm rounded-full bg-primary text-white font-button-text whitespace-nowrap shadow-md">Semua</button>
<button class="px-lg py-sm rounded-full bg-surface border border-outline-variant text-on-surface-variant font-button-text whitespace-nowrap">Kopi</button>
<button class="px-lg py-sm rounded-full bg-surface border border-outline-variant text-on-surface-variant font-button-text whitespace-nowrap">Teh</button>
<button class="px-lg py-sm rounded-full bg-surface border border-outline-variant text-on-surface-variant font-button-text whitespace-nowrap">Makanan</button>
<button class="px-lg py-sm rounded-full bg-surface border border-outline-variant text-on-surface-variant font-button-text whitespace-nowrap">Pastry</button>
</section>
<!-- Product Grid -->
<section class="grid grid-cols-2 gap-md">
<!-- Card 1 -->
<div class="bg-surface rounded-xl overflow-hidden shadow-[0px_2px_12px_rgba(200,119,58,0.12)] border border-transparent hover:border-primary/20 transition-all active:scale-[0.98]">
<div class="aspect-square bg-primary-fixed-dim overflow-hidden relative">
<img alt="Es Kopi Susu" class="w-full h-full object-cover" data-alt="A cold, delicious iced coffee latte in a clear plastic cup with condensation droplets. The coffee layers of dark espresso and creamy milk are visible. The scene is set on a light wood cafe table with soft morning sunlight streaming in, creating a warm and cozy atmosphere. Minimalist, modern cafe photography style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuD-XpFPOaHzqaSh_JXN2lwsoyB8BV1bSOVCiqDk2ZeSZ1UZU2SYc253wi-3TNognwvogHcDsVib92pH2VCMQmG-o924Aej3N8SS0GaKSbT1cmhn_3ctyfb2GVvbrt9wb8Ye6y12RzswU3mPYeYCK0FiuGlVQVKDi_ppnHGerXv9z20skU4ETjtKLZhFfXXIYYSMN_BcNTwDvLQVEAdShIWs1ynviofR_Z0v1Qd8HFTrr2Pobie48v2rIMN-zaQ_0nzUsB7CZOrR1B-y"/>
<div class="absolute top-sm right-sm bg-surface/90 backdrop-blur-sm px-xs py-1 rounded-lg">
<span class="font-label-md text-on-surface text-[10px]">Stok: 24</span>
</div>
</div>
<div class="p-sm space-y-xs relative">
<h3 class="font-body-md font-semibold text-on-surface line-clamp-1">Es Kopi Susu</h3>
<p class="font-label-md text-primary font-bold">Rp 18.000</p>
<button class="absolute bottom-sm right-sm w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shadow-lg active:scale-90 transition-transform">
<span class="material-symbols-outlined text-[20px]">add</span>
</button>
</div>
</div>
<!-- Card 2 -->
<div class="bg-surface rounded-xl overflow-hidden shadow-[0px_2px_12px_rgba(200,119,58,0.12)] border border-transparent hover:border-primary/20 transition-all active:scale-[0.98]">
<div class="aspect-square bg-primary-fixed-dim overflow-hidden relative">
<img alt="Cappuccino" class="w-full h-full object-cover" data-alt="A hot cappuccino in a ceramic white cup featuring professional latte art in the shape of a heart. The cup is placed on a warm brown saucer on a textured stone surface. The lighting is moody and warm, highlighting the creamy texture of the foam and the rich brown tones of the coffee. High-quality editorial food photography." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBaA3rxvMPSyDGsPhmJeijqlSAiZbU4aC5DOszFJOJ4HqZDvllAurDGK1joFfgwWOPhRhX8O36P9i2N9_noUId0RusiG_y-r6LsZ-DHL_l_xQUONSltRnlY0NUGEIO83Lvsk-tv3sAruEW5CBnF_8z1smZT4LuCJD36uAwTKk_jRdhEM5gRpdfzLKg_uo1wu5Q2v9Kf2SKw6fED68UVp8ZmMNoVNDe3ED8rVZP0bPYArXgS8s5RJBKgaMe98mq1kPTRqWpJ_NkSlCUI"/>
<div class="absolute top-sm right-sm bg-surface/90 backdrop-blur-sm px-xs py-1 rounded-lg">
<span class="font-label-md text-on-surface text-[10px]">Stok: 15</span>
</div>
</div>
<div class="p-sm space-y-xs relative">
<h3 class="font-body-md font-semibold text-on-surface line-clamp-1">Cappuccino</h3>
<p class="font-label-md text-primary font-bold">Rp 22.000</p>
<button class="absolute bottom-sm right-sm w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shadow-lg active:scale-90 transition-transform">
<span class="material-symbols-outlined text-[20px]">add</span>
</button>
</div>
</div>
<!-- Card 3 -->
<div class="bg-surface rounded-xl overflow-hidden shadow-[0px_2px_12px_rgba(200,119,58,0.12)] border border-transparent hover:border-primary/20 transition-all active:scale-[0.98]">
<div class="aspect-square bg-primary-fixed-dim overflow-hidden relative">
<img alt="Croissant Butter" class="w-full h-full object-cover" data-alt="A golden-brown, flaky butter croissant sitting on a minimalist white ceramic plate. The pastry has a perfect spiral and a glossy finish. It is positioned on a rustic wooden table within a cozy cafe environment, bathed in soft afternoon light. The aesthetic is clean, warm, and appetizing." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBKhPW5mmoPTlPAYLxEzyQV5eZOKGO9_U6FOdZDiPPSaGyK4X3YzIM6K9GEQlWG14n7jBQWs7c1Z7rq7_YApehwUqfu9Aescx-3L6a2mzOUH-qvy3hRSrQe2mtcqwQZw9X7jgCtMAWsW5Oy77f8hq_fRUb_ONSnZG7QS-9B8RWinHRDHX3RBz5InSnmg75Ey9mFozZMmZgPso0u23t7Ccyz7Lz9EyNbJ7Ai0a8H-BQzDjWI_Mmpmh3cjU_WbgUqwKltxGSixPRaW0EH"/>
<div class="absolute top-sm right-sm bg-surface/90 backdrop-blur-sm px-xs py-1 rounded-lg">
<span class="font-label-md text-on-surface text-[10px]">Stok: 8</span>
</div>
</div>
<div class="p-sm space-y-xs relative">
<h3 class="font-body-md font-semibold text-on-surface line-clamp-1">Croissant Butter</h3>
<p class="font-label-md text-primary font-bold">Rp 25.000</p>
<button class="absolute bottom-sm right-sm w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shadow-lg active:scale-90 transition-transform">
<span class="material-symbols-outlined text-[20px]">add</span>
</button>
</div>
</div>
<!-- Card 4 -->
<div class="bg-surface rounded-xl overflow-hidden shadow-[0px_2px_12px_rgba(200,119,58,0.12)] border border-transparent hover:border-primary/20 transition-all active:scale-[0.98]">
<div class="aspect-square bg-primary-fixed-dim overflow-hidden relative">
<img alt="Matcha Latte" class="w-full h-full object-cover" data-alt="A vibrant green iced matcha latte in a tall glass, showing beautiful marbled swirls of green tea and white milk. The drink is garnished with a sprig of mint and set against a minimalist cream background. The lighting is bright and fresh, highlighting the vivid colors and refreshing nature of the drink. Modern aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuD13Fah0ftn0jkVAMkFlk5vPnafFtO67mDt6sOdqbHiiFv2B_Xz04usp3m2xHu8rdCuehzanWTUDB5TTe-zpZqFBzJ7-TUUwIfOBz2e1f3sIbGXBTfnxatDUJDTjaU2qNAYR3irH9JLS7CgE1lE3u_R8g4SjcjISMbLV05RcjN4qAFQC-jXeCBAsQJhjSb0PdBHxF4pcmVTeXuKkmH9JITZM59c9pmWoh6uoMl6Sy1mXja7g1LSaCvLg1St0hhfKQD26WFy6hQJRrj9"/>
<div class="absolute top-sm right-sm bg-surface/90 backdrop-blur-sm px-xs py-1 rounded-lg">
<span class="font-label-md text-on-surface text-[10px]">Stok: 12</span>
</div>
</div>
<div class="p-sm space-y-xs relative">
<h3 class="font-body-md font-semibold text-on-surface line-clamp-1">Matcha Latte</h3>
<p class="font-label-md text-primary font-bold">Rp 24.000</p>
<button class="absolute bottom-sm right-sm w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shadow-lg active:scale-90 transition-transform">
<span class="material-symbols-outlined text-[20px]">add</span>
</button>
</div>
</div>
</section>
</main>
<!-- Floating Cart Bar -->
<div class="fixed bottom-[80px] left-md right-md z-40">
<div class="bg-primary-container h-[56px] rounded-[16px] shadow-[0px_4px_12px_rgba(200,119,58,0.30)] flex items-center justify-between px-md text-on-primary-container animate-bounce-short">
<div class="flex items-center gap-sm">
<span class="material-symbols-outlined">shopping_cart</span>
<span class="font-button-text">3 Item</span>
</div>
<div class="flex items-center gap-xs">
<span class="font-headline-md text-[18px]">Rp 67.000</span>
<span class="material-symbols-outlined">arrow_forward</span>
</div>
</div>
</div>
<!-- BottomNavBar -->
<nav class="fixed bottom-0 w-full z-50 h-[64px] bg-surface border-t border-outline-variant shadow-[0px_-2px_12px_rgba(200,119,58,0.12)] flex justify-around items-center px-md pb-safe">
<a class="flex flex-col items-center justify-center text-primary relative after:content-['•'] after:absolute after:-bottom-1 after:text-[10px] scale-95 transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
<span class="font-label-md text-label-md">Transaksi</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors" href="#">
<span class="material-symbols-outlined">inventory_2</span>
<span class="font-label-md text-label-md">Produk</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors" href="#">
<span class="material-symbols-outlined">bar_chart</span>
<span class="font-label-md text-label-md">Laporan</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors" href="#">
<span class="material-symbols-outlined">person</span>
<span class="font-label-md text-label-md">Profil</span>
</a>
</nav>
<script>
        // Micro-interaction for Cart Bar
        const cartBar = document.querySelector('.animate-bounce-short');
        if (cartBar) {
            cartBar.addEventListener('click', () => {
                cartBar.classList.add('scale-95');
                setTimeout(() => cartBar.classList.remove('scale-95'), 100);
            });
        }

        // Horizontal scroll animation for categories
        const categoryContainer = document.querySelector('.overflow-x-auto');
        let isDown = false;
        let startX;
        let scrollLeft;

        categoryContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - categoryContainer.offsetLeft;
            scrollLeft = categoryContainer.scrollLeft;
        });
        categoryContainer.addEventListener('mouseleave', () => {
            isDown = false;
        });
        categoryContainer.addEventListener('mouseup', () => {
            isDown = false;
        });
        categoryContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - categoryContainer.offsetLeft;
            const walk = (x - startX) * 2;
            categoryContainer.scrollLeft = scrollLeft - walk;
        });
    </script>
</body></html>