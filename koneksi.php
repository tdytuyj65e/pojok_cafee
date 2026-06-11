<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pojok_kafe";

$conn = mysqli_connect($host, $user, $pass, $db);

// CEK KONEKSI
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>