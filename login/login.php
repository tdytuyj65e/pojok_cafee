<?php
session_start();
include "../koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT users.*, roles.name AS role_name
            FROM users
            JOIN roles ON users.role_id = roles.id
            WHERE users.username = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {

        if (password_verify($password, $user['password'])) {

            if ($user['status'] == 'nonaktif') {
                $error = "Akun tidak aktif!";
            } else {

                // ================= SESSION FIX (PAKAI ROLE_ID SAJA) =================
                $_SESSION['id'] = $user['id'];
                $_SESSION['nama'] = $user['nama_lengkap'];
                $_SESSION['role_id'] = $user['role_id'];

                // ================= REDIRECT =================
                if ($user['role_id'] == 1) {
                    header("Location: ../owner/dashboard.php");
                } else {
                    header("Location: ../karyawan/dashboard.php");
                }
                exit();
            }

        } else {
            $error = "Password salah!";
        }

    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Pojok Kafe</title>
<link rel="manifest" href="/pojok_cafe/manifest.json">
<meta name="theme-color" content="#22c55e">
<script src="https://cdn.tailwindcss.com"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
:root{
    --espresso:#3C2415;
    --espresso-dark:#2A1810;
    --terracotta:#D97732;
    --terracotta-dark:#C0601E;
    --cream:#FDF6EE;
    --gold:#C9A227;
}

*{ box-sizing:border-box; }

body{
    font-family:'Poppins',sans-serif;
    background-color:var(--cream);
    background-image:
        radial-gradient(circle at 1px 1px, rgba(60,36,21,0.06) 1px, transparent 0);
    background-size: 22px 22px;
    margin:0;
}

.font-display{ font-family:'Fredoka', sans-serif; }
.font-mono{ font-family:'JetBrains Mono', monospace; }

/* ===== Header wave ===== */
.header-wrap{
    background: linear-gradient(135deg, var(--terracotta) 0%, var(--terracotta-dark) 60%, var(--espresso) 130%);
    position:relative;
    overflow:hidden;
}
.header-wrap::after{
    content:"";
    position:absolute;
    left:0; right:0; bottom:-2px;
    height:48px;
    background:var(--cream);
    clip-path: polygon(0 100%, 100% 100%, 100% 40%, 75% 100%, 50% 30%, 25% 100%, 0 45%);
}
.bean-float{
    position:absolute;
    opacity:0.16;
    color:#fff;
    filter: blur(0.3px);
}

/* ===== Cup + steam ===== */
.cup-badge{
    width:6.5rem; height:6.5rem;
    background:#fff;
    border-radius:9999px;
    display:flex; align-items:center; justify-content:center;
    box-shadow: 0 14px 30px -8px rgba(0,0,0,0.35), 0 0 0 6px rgba(255,255,255,0.18);
    position:relative;
}
.steam{
    position:absolute;
    top:-22px;
    width:4px;
    border-radius:4px;
    background: rgba(255,255,255,0.85);
    animation: steamRise 2.6s ease-in-out infinite;
}
.steam.s1{ left:38%; height:14px; animation-delay:0s; }
.steam.s2{ left:50%; height:18px; animation-delay:0.5s; }
.steam.s3{ left:62%; height:14px; animation-delay:1s; }
@keyframes steamRise{
    0%   { transform: translateY(6px) scaleY(0.6); opacity:0; }
    35%  { opacity:0.9; }
    100% { transform: translateY(-16px) scaleY(1.15); opacity:0; }
}

/* ===== Card with receipt edge ===== */
.receipt-card{
    background:#fff;
    border-radius: 1.75rem 1.75rem 0 0;
    box-shadow: 0 30px 60px -20px rgba(60,36,21,0.35);
    position:relative;
    padding-bottom: 26px;
}
.receipt-card::after{
    content:"";
    position:absolute;
    left:0; right:0; bottom:0;
    height:18px;
    background:
        linear-gradient(135deg, #fff 50%, transparent 50%) 0 0 / 18px 18px,
        linear-gradient(-135deg, #fff 50%, transparent 50%) 0 0 / 18px 18px;
    background-repeat: repeat-x;
}

.divider-dotted{
    border-top: 1.5px dotted #E5C9AE;
}

/* ===== Inputs ===== */
.input-icon-wrap{ position:relative; }
.input-icon-wrap svg{
    position:absolute; left:14px; top:50%;
    transform: translateY(-50%);
    color:#C97E3F;
    width:18px; height:18px;
}
.field-input{
    width:100%;
    padding: 0.85rem 1rem 0.85rem 2.6rem;
    border:1.5px solid #F0DDC8;
    border-radius: 1rem;
    background:#FFFBF6;
    transition: all .18s ease;
    font-size: 0.95rem;
}
.field-input:focus{
    outline:none;
    border-color: var(--terracotta);
    background:#fff;
    box-shadow: 0 0 0 4px rgba(217,119,50,0.15);
}
.toggle-pass{
    position:absolute; right:12px; top:50%;
    transform: translateY(-50%);
    color:#B98654;
    cursor:pointer;
    background:none; border:none; padding:4px;
}
.toggle-pass:hover{ color:var(--terracotta-dark); }

/* ===== Button ===== */
.btn-submit{
    background: linear-gradient(135deg, var(--terracotta) 0%, var(--terracotta-dark) 100%);
    transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
    box-shadow: 0 10px 22px -6px rgba(217,119,50,0.55);
}
.btn-submit:hover{ transform: translateY(-1px); box-shadow: 0 14px 26px -6px rgba(217,119,50,0.6); }
.btn-submit:active{ transform: translateY(0); }
.btn-submit:disabled{ opacity:0.75; cursor:not-allowed; transform:none; }

.btn-register{
    border:1.5px solid #F0DDC8;
    color: var(--terracotta-dark);
    background:#FFFBF6;
    transition: all .18s ease;
}
.btn-register:hover{
    border-color: var(--terracotta);
    background:#fff;
    box-shadow: 0 8px 18px -6px rgba(217,119,50,0.25);
}

.spinner{
    width:18px; height:18px;
    border:2.5px solid rgba(255,255,255,0.45);
    border-top-color:#fff;
    border-radius:50%;
    animation: spin .7s linear infinite;
}
@keyframes spin{ to{ transform: rotate(360deg); } }

/* ===== Alert ===== */
.alert-error{
    animation: slideDown .25s ease;
}
@keyframes slideDown{
    from{ opacity:0; transform: translateY(-8px); }
    to{ opacity:1; transform: translateY(0); }
}

/* ===== Focus visibility for a11y ===== */
a:focus-visible, button:focus-visible, input:focus-visible{
    outline: 2px solid var(--terracotta-dark);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce){
    .steam, .alert-error, .btn-submit{ animation:none !important; transition:none !important; }
}
</style>
</head>
<body class="min-h-screen">

<!-- Header -->
<div class="header-wrap h-72 relative">

    <!-- decorative floating beans -->
    <svg class="bean-float" style="top:18px; left:8%; width:26px;" viewBox="0 0 24 24" fill="currentColor"><ellipse cx="12" cy="12" rx="9" ry="11" transform="rotate(20 12 12)"/></svg>
    <svg class="bean-float" style="top:48px; right:10%; width:20px;" viewBox="0 0 24 24" fill="currentColor"><ellipse cx="12" cy="12" rx="9" ry="11" transform="rotate(-25 12 12)"/></svg>
    <svg class="bean-float" style="top:120px; left:18%; width:16px;" viewBox="0 0 24 24" fill="currentColor"><ellipse cx="12" cy="12" rx="9" ry="11" transform="rotate(40 12 12)"/></svg>

    <div class="flex flex-col items-center pt-10 relative z-10">

        <div class="cup-badge">
            <span class="steam s1"></span>
            <span class="steam s2"></span>
            <span class="steam s3"></span>
            <span class="text-5xl">☕</span>
        </div>

        <h1 class="font-display text-white text-4xl font-semibold mt-4 tracking-wide">
            Pojok Kafe
        </h1>

        <p class="text-orange-100 mt-1">
            Sistem Kasir Digital
        </p>

    </div>

</div>

<!-- Login Card -->
<div class="max-w-md mx-auto px-4 -mt-16 relative z-10">

    <div class="receipt-card p-8 pt-7">

        <div class="text-center mb-1">
            <p class="font-mono text-[11px] uppercase tracking-[0.2em] text-[#B98654]">Portal Karyawan &amp; Owner</p>
        </div>

        <h2 class="font-display text-2xl font-semibold text-[var(--espresso)] text-center mt-1">
            Selamat Datang
        </h2>

        <p class="text-gray-500 text-sm text-center mt-1 mb-6">
            Masuk untuk melanjutkan sistem kasir.
        </p>

        <div class="divider-dotted mb-6"></div>

        <?php if(!empty($error)): ?>
        <div class="alert-error flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Username
                </label>

                <div class="input-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" name="username" required autofocus autocomplete="username"
                        placeholder="Masukkan username"
                        class="field-input">
                </div>
            </div>

            <div class="mb-6">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Password
                </label>

                <div class="input-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" name="password" id="passwordField" required autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="field-input" style="padding-right:2.6rem;">
                    <button type="button" class="toggle-pass" id="togglePass" aria-label="Tampilkan password">
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="submitBtn"
                class="btn-submit w-full text-white py-3.5 rounded-xl font-semibold flex items-center justify-center gap-2">
                <span id="btnLabel">MASUK</span>
            </button>

        </form>

        <!-- Tombol Register -->
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 divider-dotted"></div>
            <span class="text-[11px] text-[#C7A98A] font-mono">ATAU</span>
            <div class="flex-1 divider-dotted"></div>
        </div>

        <div class="divider-dotted mt-7 pt-4">
            <p class="font-mono text-[10px] text-center text-[#C7A98A] tracking-widest">
                * * * TERIMA KASIH * * *
            </p>
        </div>

    </div>

</div>

<footer class="text-center py-8 text-gray-500 text-sm flex items-center justify-center gap-1.5">
    <span>☕</span> Pojok Kafe © 2026
</footer>

<script>
const togglePass = document.getElementById('togglePass');
const passwordField = document.getElementById('passwordField');
const eyeIcon = document.getElementById('eyeIcon');

togglePass.addEventListener('click', () => {
    const isHidden = passwordField.type === 'password';
    passwordField.type = isHidden ? 'text' : 'password';
    eyeIcon.innerHTML = isHidden
        ? '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.5 18.5 0 0 1 4.22-5.06M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>';
    togglePass.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
});

const loginForm = document.getElementById('loginForm');
const submitBtn = document.getElementById('submitBtn');
const btnLabel = document.getElementById('btnLabel');

loginForm.addEventListener('submit', () => {
    submitBtn.disabled = true;
    btnLabel.textContent = 'MEMPROSES...';
    submitBtn.insertAdjacentHTML('afterbegin', '<span class="spinner"></span>');
});
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pojok_cafe/sw.js');
}
</script>

</body>
</html>