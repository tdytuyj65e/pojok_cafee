<?php
session_start();

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'owner') {
        header("Location: owner/dashboard.php");
        exit;
    }

    if ($_SESSION['role'] == 'karyawan') {
        header("Location: karyawan/dashboard.php");
        exit;
    }

}

header("Location: login/login.php");
exit;