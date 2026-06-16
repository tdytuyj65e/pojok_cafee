<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background font-body-md text-on-background min-h-screen pb-40">
<!-- Top AppBar (JSON Derived Mapping) -->
<header class="bg-primary-container text-on-primary-container shadow-md fixed top-0 w-full z-50 h-[56px] flex items-center justify-between px-md">
<button class="active:scale-95 transition-transform">
<span class="material-symbols-outlined">arrow_back</span>
</button>
<h1 class="font-headline-md text-headline-md font-bold">Keranjang</h1>
<button class="active:scale-95 transition-transform">
<span class="material-symbols-outlined">delete</span>
</button>
</header>
<main class="pt-[72px] px-md space-y-md">
<!-- Cart Items List -->
<section class="space-y-sm">
<!-- Item 1 -->
<div class="bg-surface-container-lowest card-shadow rounded-xl p-md flex items-center gap-md">
<img alt="Es Kopi Susu" class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="A close-up high-angle shot of a refreshing iced milk coffee in a clear glass with condensation droplets. The setting is a warm, sunlit cafe with wooden textures and cream-colored accents. The lighting is soft and natural, emphasizing the creamy texture of the milk swirling into dark espresso. The overall mood is inviting, cozy, and professional, perfectly aligning with the modern-tactile aesthetic of a high-end local coffee shop." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBDz9ub65M4BEdX5AiEn6Cb-qTw5VSn7dw3dNmyanwFCjGWMPWu6O-EOjao8EYJ6ZY5ahhFglgAVt2AM2i9hEMyPUttNnDd12cRB9cV7Ec_EyjZs4WcpGgGerGR8ag1sysGa7tWTX8qXhJdYdT1asxMQ3cIyAwMxrnYusmx-b7Yys0t7U_kOKhTZ3aBvJe5SxcW7NNgAKXT-A--Fctf5SOTGkKkjcojPA22PgB7o1jxy1MqtUf_oHIBotIUaV32pNqYs8qto_SYclLT"/>
<div class="flex-1">
<h3 class="font-headline-md text-[16px] text-on-surface">Es Kopi Susu</h3>
<p class="text-on-surface-variant font-label-md">Rp 16.000</p>
</div>
<div class="flex items-center gap-sm">
<button class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary active:scale-90 transition-transform">
<span class="material-symbols-outlined text-[18px]">remove</span>
</button>
<span class="font-button-text min-w-[20px] text-center">2</span>
<button class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary active:scale-90 transition-transform">
<span class="material-symbols-outlined text-[18px]">add</span>
</button>
</div>
</div>
<!-- Item 2 -->
<div class="bg-surface-container-lowest card-shadow rounded-xl p-md flex items-center gap-md">
<img alt="Roti Bakar Cokelat" class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="A macro photograph of thick-cut toasted bread drizzled with rich chocolate sauce and topped with grated cheese. The lighting is warm and golden, highlighting the crispy texture of the bread and the glossy finish of the chocolate. The scene is set on a minimalist ceramic plate within a cozy, modern cafe environment. The aesthetic is clean and tactile, evoking a sense of comfort and artisanal quality." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAeds5o4NDhnwXn0RzCXDC_0a9OwmOex442-3SYctenSGTiu8cOQH7giGbJzcGVAujJzXete8uBDUezMmnw6DxntuMezWqwshpxOuGsx27-lKMhY7ymkQDLk4rNHEoeywZDXle_NMVD9Dc8Dy8c_t0x-BmK59TNR-XLt4mAOkvftU6mcIOxxpQxIIS8rCh08cmYIz0SGZeB_VgZjQCBcF1bbdj_F0WaQupiB0jXQ17f-Ccz_A3lT5eEBHN7idqrNismNkgOh4nOSaSE"/>
<div class="flex-1">
<h3 class="font-headline-md text-[16px] text-on-surface">Roti Bakar</h3>
<p class="text-on-surface-variant font-label-md">Rp 12.000</p>
</div>
<div class="flex items-center gap-sm">
<button class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[18px]">remove</span>
</button>
<span class="font-button-text min-w-[20px] text-center">1</span>
<button class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[18px]">add</span>
</button>
</div>
</div>
<!-- Item 3 -->
<div class="bg-surface-container-lowest card-shadow rounded-xl p-md flex items-center gap-md">
<img alt="Teh Manis Panas" class="w-[56px] h-[56px] rounded-lg object-cover" data-alt="An elegant shot of steaming hot tea in a glass cup, placed on a rustic wooden table. Soft morning light filters through a nearby window, creating a warm and hazy atmosphere. The color palette is composed of rich ambers, warm creams, and deep browns, maintaining the professional yet cozy vibe of an upscale coffee shop. The steam is captured in high detail, conveying warmth and freshness." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDBEFkfeSRvaMWa2Y4i8GN-X1NuLYOipRx0-ieyHpEGWKf7H1t9KrVrTc-voqrC6XTbQG-R_38RqJxxK5bhsi7lZYf7GFKB6PK00ukdvOPQg9PiyfNHePrvUzNChyRFa75rnuGitNr_m3-hVllWy15ZmhXIhwNBWEXTXzAOsQO7fm7KY5wxsmV08G-8Tqwd8p_gPAL1hmAN-Fsbj1gF8K6gYlv_9sPuYvzb-7AhN3a8g5evKK8EF0NjfFtz6wIJbIlrCHL1r4ow-qFA"/>
<div class="flex-1">
<h3 class="font-headline-md text-[16px] text-on-surface">Teh Manis</h3>
<p class="text-on-surface-variant font-label-md">Rp 10.000</p>
</div>
<div class="flex items-center gap-sm">
<button class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[18px]">remove</span>
</button>
<span class="font-button-text min-w-[20px] text-center">1</span>
<button class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[18px]">add</span>
</button>
</div>
</div>
</section>
<!-- Order Summary Card -->
<section class="bg-surface-container-lowest card-shadow rounded-xl p-md space-y-sm">
<h2 class="font-headline-md text-[16px] text-on-surface-variant">Ringkasan Pesanan</h2>
<div class="flex justify-between items-center text-on-surface">
<span class="font-body-md">Subtotal</span>
<span class="font-body-md">Rp 38.000</span>
</div>
<div class="flex justify-between items-center text-on-surface">
<span class="font-body-md">Diskon</span>
<span class="font-body-md">-</span>
</div>
<div class="pt-sm border-t border-outline-variant flex justify-between items-center">
<span class="font-headline-md text-[16px] text-on-surface">Total</span>
<span class="font-headline-lg text-headline-lg text-primary">Rp 38.000</span>
</div>
</section>
<!-- Payment Section -->
<section class="space-y-md">
<h2 class="font-headline-md text-[16px] text-on-surface">Pembayaran Tunai</h2>
<div class="relative group">
<span class="absolute left-md top-1/2 -translate-y-1/2 text-primary font-headline-md">Rp</span>
<input class="w-full h-[56px] rounded-xl border-2 border-primary bg-surface text-right px-md font-headline-lg text-primary focus:ring-0 focus:outline-none transition-all" placeholder="0" type="number" value="50000"/>
</div>
<div class="flex flex-wrap gap-sm">
<button class="px-md h-10 rounded-full border border-outline text-on-surface-variant font-label-md hover:bg-secondary-container hover:border-primary hover:text-primary transition-colors">Rp 38.000</button>
<button class="px-md h-10 rounded-full border border-outline text-on-surface-variant font-label-md hover:bg-secondary-container hover:border-primary hover:text-primary transition-colors">Rp 40.000</button>
<button class="px-md h-10 rounded-full border border-outline text-on-surface-variant font-label-md hover:bg-secondary-container hover:border-primary hover:text-primary transition-colors bg-secondary-container border-primary text-primary">Rp 50.000</button>
<button class="px-md h-10 rounded-full border border-outline text-on-surface-variant font-label-md hover:bg-secondary-container hover:border-primary hover:text-primary transition-colors">Rp 100.000</button>
</div>
<div class="bg-surface-container rounded-xl p-md flex justify-between items-center border border-primary-container/30">
<span class="font-headline-md text-[16px] text-on-primary-container">Kembalian</span>
<span class="font-headline-lg text-headline-lg text-on-primary-container">Rp 12.000</span>
</div>
</section>
</main>
<!-- Bottom Sticky Area -->
<footer class="fixed bottom-0 left-0 w-full bg-surface px-md pb-md pt-sm border-t border-outline-variant space-y-sm">
<button class="w-full h-[56px] bg-primary text-on-primary rounded-xl font-headline-md button-shadow active:scale-95 transition-all flex items-center justify-center">
            SELESAIKAN TRANSAKSI
        </button>
<p class="text-center text-on-surface-variant font-label-md opacity-70">
            Pastikan jumlah sudah benar sebelum menyelesaikan transaksi.
        </p>
</footer>
<script>
        // Subtle ripple or active state micro-interactions
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('touchstart', () => {
                button.classList.add('opacity-80');
            });
            button.addEventListener('touchend', () => {
                button.classList.remove('opacity-80');
            });
        });
    </script>
</body></html>