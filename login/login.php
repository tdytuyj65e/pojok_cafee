<?php
session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT users.*, roles.name AS role_name
        FROM users
        JOIN roles ON users.role_id = roles.id
        WHERE username = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user) {

    if (password_verify($password, $user['password_hash'])) {

        if ($user['is_active'] == 0) {
            echo "Akun nonaktif";
            exit;
        }

        $_SESSION['id'] = $user['id'];
        $_SESSION['nama'] = $user['full_name'];
        $_SESSION['role'] = $user['role_name'];

        header("Location: dashboard.php");
        exit;

    } else {
        echo "Password salah";
    }

} else {
    echo "Username tidak ditemukan";
}
?>
<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login | Pojok Kafe POS</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Poppins:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
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
                    "label-md": ["Plus Jakarta Sans"],
                    "poppins": ["Poppins", "sans-serif"]
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
      .wave-container {
        position: relative;
        background-color: #C8773A;
        height: 260px;
        width: 100%;
        overflow: hidden;
      }
      .wave-path {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        line-height: 0;
      }
      .form-card {
        margin-top: -60px;
        position: relative;
        z-index: 10;
        box-shadow: 0px 4px 20px rgba(200, 119, 58, 0.12);
      }
      .input-transition:focus-within {
        border-color: #8e4a0e;
        box-shadow: 0 0 0 1px #8e4a0e;
      }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-[#FDF6EE] font-body-md text-on-surface min-h-screen flex flex-col">
<!-- Top Area -->
<header class="wave-container flex flex-col items-center pt-xl">
<div class="z-20 flex flex-col items-center">
<div class="w-[80px] h-[80px] bg-white rounded-full flex items-center justify-center mb-md shadow-lg transform transition-transform hover:scale-105 duration-300">
<span class="material-symbols-outlined text-[#C8773A] text-[40px]" data-icon="coffee">coffee</span>
</div>
<h1 class="font-poppins font-bold text-[28px] text-white leading-tight">Pojok Kafe</h1>
<p class="font-poppins font-normal text-[13px] text-[#F2D4B8]">Sistem Kasir Digital</p>
</div>
<!-- Curved Wave Transition -->
<div class="wave-path">
<svg preserveaspectratio="none" style="height: 80px; width: 100%;" viewbox="0 0 500 150">
<path d="M-0.00,49.85 C150.00,150.00 349.20,-49.85 500.00,49.85 L500.00,150.00 L-0.00,150.00 Z" style="stroke: none; fill: #FDF6EE;"></path>
</svg>
</div>
</header>
<!-- Form Card -->
<main class="flex-grow px-md flex flex-col items-center">
<div class="form-card w-full max-w-[400px] bg-white rounded-t-[20px] rounded-b-xl p-lg space-y-lg">
<div class="space-y-xs">
<h2 class="font-poppins font-bold text-[22px] text-[#2C1A0E]">Selamat Datang</h2>
<p class="font-poppins font-normal text-[13px] text-[#7A5C44]">Masuk untuk melanjutkan</p>
</div>
<form class="space-y-md" onsubmit="event.preventDefault(); window.location.reload();">
<!-- Username -->
<div class="space-y-xs">
<label class="font-label-md text-label-md text-on-surface-variant block ml-1">Username</label>
<div class="flex items-center border-[1.5px] border-[#E8D5C0] rounded-[10px] px-md py-sm bg-white input-transition transition-all duration-200">
<span class="material-symbols-outlined text-[#B8977E] mr-sm text-[20px]" data-icon="person">person</span>
<input class="flex-grow border-none focus:ring-0 p-0 text-body-md placeholder-[#B8977E] bg-transparent" placeholder="Masukkan username" type="text"/>
</div>
</div>
<!-- Password -->
<div class="space-y-xs">
<label class="font-label-md text-label-md text-on-surface-variant block ml-1">Password</label>
<div class="flex items-center border-[1.5px] border-[#E8D5C0] rounded-[10px] px-md py-sm bg-white input-transition transition-all duration-200">
<span class="material-symbols-outlined text-[#B8977E] mr-sm text-[20px]" data-icon="lock">lock</span>
<input class="flex-grow border-none focus:ring-0 p-0 text-body-md placeholder-[#B8977E] bg-transparent" id="password-input" placeholder="Masukkan password" type="password"/>
<button class="flex items-center justify-center hover:bg-surface-container-low rounded-full p-xs transition-colors" onclick="togglePassword()" type="button">
<span class="material-symbols-outlined text-[#B8977E] text-[20px]" data-icon="visibility" id="password-toggle-icon">visibility</span>
</button>
</div>
</div>
<!-- Action Button -->
<button class="w-full bg-[#C8773A] hover:bg-primary py-md rounded-[12px] font-poppins font-semibold text-[14px] text-white shadow-[0px_4px_12px_rgba(200,119,58,0.30)] active:scale-95 transition-all duration-200 mt-md" type="submit">
                    MASUK
                </button>
</form>
<!-- Decorative Link -->
<div class="text-center pt-xs">
<a class="text-primary text-label-md hover:underline font-medium" href="#">Lupa Password?</a>
</div>
</div>
</main>
<!-- Bottom Footer -->
<footer class="py-lg text-center">
<p class="font-poppins font-normal text-[11px] text-[#B8977E]">Pojok Kafe © 2025</p>
</footer>
<script>
        function togglePassword() {
            const input = document.getElementById('password-input');
            const icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerText = 'visibility_off';
                icon.setAttribute('data-icon', 'visibility_off');
            } else {
                input.type = 'password';
                icon.innerText = 'visibility';
                icon.setAttribute('data-icon', 'visibility');
            }
        }

        // Simple touch feedback for the card
        const card = document.querySelector('.form-card');
        card.addEventListener('touchstart', () => {
          card.style.transform = 'translateY(-2px)';
        }, {passive: true});
        card.addEventListener('touchend', () => {
          card.style.transform = 'translateY(0px)';
        }, {passive: true});
    </script>
</body></html>