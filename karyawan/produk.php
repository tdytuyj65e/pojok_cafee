<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
<title>Pojok Kafe POS - Manajemen Produk</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<body class="font-body-md text-on-surface">
<!-- TopAppBar Shell -->
<header class="fixed top-0 w-full z-50 bg-primary-container text-on-primary-container h-[56px] flex items-center justify-between px-md shadow-md">
<div class="flex items-center gap-sm">
<div class="w-8 h-8 rounded-full bg-surface-container-highest overflow-hidden border border-on-primary-container/20">
<img alt="Cashier Profile" class="w-full h-full object-cover" data-alt="A friendly male cashier profile avatar in a minimalist illustrative style. The character has a warm smile and is wearing a neat cafe apron. The background is a soft beige tone that complements the warm orange and brown cafe theme. High-quality digital character art with clean lines." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDenT9aaBDfmDijHHHAS4CVfaqHsEklgFhwV1_oSVfjs8NIuv15ZR0nALlVTblWYGJD0qk46qYfNU2DJEN5ETYrVbIILjqTCmqA-Y9H23tup6s4YGQBSM6LJ8elw2fQB5mNlLtr_ECGMVkDfXSO0EcwLxuPzkNzm1VxMQH0mxXR2J1kpniTBvFgPEKJqIQl9DjiCw0K1eXXB6DgVvW0UifvI9d4YgYeDqFm8ythDzQSbZa56qpERxdzeZgoWd1rFKVBmTLmrovUvRpC"/>
</div>
<h1 class="font-headline-md text-headline-md font-bold text-on-primary-container">Pojok Kafe</h1>
</div>
<button class="material-symbols-outlined text-on-primary-container hover:opacity-90 active:scale-95 transition-transform" data-icon="notifications">notifications</button>
</header>
<!-- Main Canvas -->
<main class="pt-[72px] pb-[88px] px-md max-w-2xl mx-auto space-y-md">
<!-- Title Row -->
<section class="flex items-center justify-between">
<h2 class="font-headline-lg text-headline-lg text-on-surface">Manajemen Produk</h2>
<button class="bg-primary text-on-primary px-md py-sm rounded-lg font-button-text text-button-text primary-shadow active:scale-95 transition-all flex items-center gap-xs">
<span class="material-symbols-outlined text-[18px]" data-icon="add">add</span>
<span>Tambah</span>
</button>
</section>
<!-- Search Bar -->
<section class="relative">
<div class="absolute left-md top-1/2 -translate-y-1/2 text-outline">
<span class="material-symbols-outlined" data-icon="search">search</span>
</div>
<input class="w-full h-[48px] pl-[48px] pr-md rounded-xl bg-surface-container-lowest border-[1.5px] border-outline-variant focus:border-primary focus:ring-0 placeholder:text-on-surface-variant transition-colors" placeholder="Cari nama produk..." type="text"/>
</section>
<!-- Category Filter Tabs -->
<section class="flex gap-sm overflow-x-auto pb-xs scrollbar-hide">
<button class="whitespace-nowrap px-md py-sm rounded-full bg-primary text-on-primary font-button-text text-button-text">Semua</button>
<button class="whitespace-nowrap px-md py-sm rounded-full bg-surface-container-high text-on-surface-variant font-button-text text-button-text hover:bg-surface-container-highest transition-colors">Minuman Kemasan</button>
<button class="whitespace-nowrap px-md py-sm rounded-full bg-surface-container-high text-on-surface-variant font-button-text text-button-text hover:bg-surface-container-highest transition-colors">Makanan Berat</button>
<button class="whitespace-nowrap px-md py-sm rounded-full bg-surface-container-high text-on-surface-variant font-button-text text-button-text hover:bg-surface-container-highest transition-colors">Snacks</button>
</section>
<!-- Product List -->
<section class="space-y-sm">
<!-- Product Item 1 -->
<div class="bg-surface-container-lowest p-sm rounded-xl custom-shadow flex items-center gap-md">
<img class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="A cold glass of iced milk coffee with condensation on the glass, set against a warm wooden background in a cozy cafe setting. The lighting is soft and golden, highlighting the texture of the milk swirling into the dark espresso. Modern food photography style with warm brown and cream tones." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAD6sIHVZA6z4a7No2Bi0oirqp2sCEL5PJLzES1MdoGiFQ9N06LNKZjHHUiGa55D4WB3A6UTgHudHxr0MHzshb9QhblpWBSo9zeao9TyzphoRR0QNI1dskYGreUPvKFJUJ8mNShVYtxQMTA5E6GQ7zuEwFwdJOUF6ZU72Mo8YWyxN3wB3jfr-gO9zSF2MpFadZuCmoahUaaEblCh122ws43bU7tEOX7jq9zIgJ9Bhpx4TjGVeQGD9G93wOBhJOrB3UgybETbamC_8sn"/>
<div class="flex-1">
<h3 class="font-headline-md text-headline-md text-on-surface">Es Kopi Susu</h3>
<div class="flex items-center gap-xs mt-1">
<span class="px-xs py-[2px] bg-secondary-container text-on-secondary-container text-[10px] font-bold rounded uppercase">Kopi</span>
<p class="text-primary font-label-md text-label-md">Rp 18.000</p>
</div>
</div>
<div class="flex flex-col items-end gap-sm">
<span class="px-sm py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">18 Stock</span>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="more_vert">more_vert</button>
</div>
</div>
<!-- Product Item 2 -->
<div class="bg-surface-container-lowest p-sm rounded-xl custom-shadow flex items-center gap-md">
<img class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="A vibrant rice bowl with colorful vegetables, grilled protein, and a glossy sauce, presented in a ceramic bowl on a rustic cafe table. High-contrast lighting captures the fresh textures of the ingredients. The overall mood is appetizing and modern-tactile." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9kr5aD9SFwhMHYrqPsSOlZCNXGxjg1DeVGZbOgIXChITtOo79vFzj9RjImjuRdf_gSbzrp0GOYTy1wvnK6eWRi9P6LaGHY3x1iv4m9aIuNEQ3eQCxEr4deYctdn05XLWu7Yhdf6FyrGrhAPKAKPl7B8p5jrrTZLxqrJKl4JhbZNvQL9wCkNuZvYdpD--C5Rqx605wWgjdv2brSacZlDz1vJloOhPMY84TsImyPtybTgF1zICe1mTe6HdoC_YOomG9UiWJfSPf6USy"/>
<div class="flex-1">
<h3 class="font-headline-md text-headline-md text-on-surface">Rice Bowl</h3>
<div class="flex items-center gap-xs mt-1">
<span class="px-xs py-[2px] bg-secondary-container text-on-secondary-container text-[10px] font-bold rounded uppercase">Makanan</span>
<p class="text-primary font-label-md text-label-md">Rp 35.000</p>
</div>
</div>
<div class="flex flex-col items-end gap-sm">
<span class="px-sm py-1 bg-error-container text-error text-[10px] font-bold rounded-full">5 Stock</span>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="more_vert">more_vert</button>
</div>
</div>
<!-- Product Item 3 -->
<div class="bg-surface-container-lowest p-sm rounded-xl custom-shadow flex items-center gap-md">
<img class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="A classic glass bottle of iced tea with visible water droplets and a straw. The background is a sunny cafe terrace with soft bokeh. Bright, refreshing lighting and a warm summer atmosphere that fits the burnt orange brand identity." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBk4liodytk9BDyTRLiE0dMVIZ0PDf-zNxITSjgDDZsXQRkMoqBz1n075lKF2lNHRkF0UvqpsG7XdaOJLYtUgyETT23ZDwUvPXUA4RFCfMb8LzMpdkj4YnJJYT1M2bg3QwEdqUni61aNGWOrvMQ5DJLGzLvAyZZ7To_xbrnb08-i8DYLWyGf1tT4-OcqqkDHATuyw3NBkJeTb-oJ4pMkwrdGSfrVYJTKHPt8YWRw1VMCnc7fQUsRMJwE8jlr7VNWRvbHJMPnUzbm1dd"/>
<div class="flex-1">
<h3 class="font-headline-md text-headline-md text-on-surface">Teh Botol</h3>
<div class="flex items-center gap-xs mt-1">
<span class="px-xs py-[2px] bg-secondary-container text-on-secondary-container text-[10px] font-bold rounded uppercase">Minuman</span>
<p class="text-primary font-label-md text-label-md">Rp 8.000</p>
</div>
</div>
<div class="flex flex-col items-end gap-sm">
<span class="px-sm py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">30 Stock</span>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="more_vert">more_vert</button>
</div>
</div>
<!-- Product Item 4 -->
<div class="bg-surface-container-lowest p-sm rounded-xl custom-shadow flex items-center gap-md">
<img class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="A steaming plate of Indomie Goreng topped with a perfectly fried egg and some garnishes. Captured in a warm, low-key lighting style typical of a comfortable Indonesian cafe. Rich textures and deep golden-brown hues." src="https://lh3.googleusercontent.com/aida-public/AB6AXuA68oarRLtHHdpG9TpmGAJeTimhBTCrO322lqb-UxCGLPDyh7INvZU2UX0BUSXb6DXtRsL01aE5YKWfbpi8pIDC3IwnaYI3VJa6DjFcLEXhLp01BV-SnArUh64HyPU_7_Ncy5abjybzSlnygghctILug-IaUbuOSAo2cLzXWXsWO02g8fvlpgo1txqPj4LJ36n3xkSYUn3e-9HmRG5rry2rtGhVpbUsSq3BeoYYy3VkgIzhX6LCCBpaFyNP08GHiUkaMRmOCUfIlafP"/>
<div class="flex-1">
<h3 class="font-headline-md text-headline-md text-on-surface">Indomie Goreng</h3>
<div class="flex items-center gap-xs mt-1">
<span class="px-xs py-[2px] bg-secondary-container text-on-secondary-container text-[10px] font-bold rounded uppercase">Instant</span>
<p class="text-primary font-label-md text-label-md">Rp 12.000</p>
</div>
</div>
<div class="flex flex-col items-end gap-sm">
<span class="px-sm py-1 bg-error-container text-error text-[10px] font-bold rounded-full">8 Stock</span>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="more_vert">more_vert</button>
</div>
</div>
</section>
</main>
<!-- Floating Action Button -->
<button class="fixed right-md bottom-24 w-14 h-14 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-lg active:scale-95 transition-transform z-40">
<span class="material-symbols-outlined text-[32px]" data-icon="add">add</span>
</button>
<!-- BottomNavBar Shell -->
<nav class="fixed bottom-0 w-full z-50 bg-surface h-[64px] border-t border-outline-variant flex justify-around items-center px-md pb-safe shadow-[0px_-2px_12px_rgba(200,119,58,0.12)]">
<button class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors active:scale-95 duration-200">
<span class="material-symbols-outlined" data-icon="shopping_cart">shopping_cart</span>
<span class="font-label-md text-label-md mt-1">Transaksi</span>
</button>
<button class="flex flex-col items-center justify-center text-primary relative after:content-['•'] after:absolute after:-bottom-1 after:text-[10px] active:scale-95 transition-all duration-200">
<span class="material-symbols-outlined" data-icon="inventory_2" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
<span class="font-label-md text-label-md mt-1">Produk</span>
</button>
<button class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors active:scale-95 duration-200">
<span class="material-symbols-outlined" data-icon="bar_chart">bar_chart</span>
<span class="font-label-md text-label-md mt-1">Laporan</span>
</button>
<button class="flex flex-col items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors active:scale-95 duration-200">
<span class="material-symbols-outlined" data-icon="person">person</span>
<span class="font-label-md text-label-md mt-1">Profil</span>
</button>
</nav>
<script>
        // Simple micro-interactions
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                // Haptic feedback simulation
                if (window.navigator.vibrate) {
                    window.navigator.vibrate(5);
                }
            });
        });

        // Add scroll behavior for the Top Bar
        let lastScrollTop = 0;
        window.addEventListener("scroll", function() {
            let st = window.pageYOffset || document.documentElement.scrollTop;
            if (st > lastScrollTop) {
                // Scrolling down
                document.querySelector('header').classList.add('-translate-y-full');
            } else {
                // Scrolling up
                document.querySelector('header').classList.remove('-translate-y-full');
            }
            lastScrollTop = st <= 0 ? 0 : st;
        }, false);
    </script>
</body></html>