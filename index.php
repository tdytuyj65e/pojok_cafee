<?php
session_start();

/*
|--------------------------------------------------------------------------
| Jika sudah login
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['id']) && isset($_SESSION['role_id'])) {

    if ($_SESSION['role_id'] == 1) {
        header("Location: owner/dashboard.php");
        exit;
    }

    if ($_SESSION['role_id'] == 2) {
        header("Location: karyawan/dashboard.php");
        exit;
    }

    // Jika role tidak dikenal
    session_destroy();
}

/*
|--------------------------------------------------------------------------
| Jika belum login
|--------------------------------------------------------------------------
*/

header("Location: login/login.php");
exit;