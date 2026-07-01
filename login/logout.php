<?php
session_start();


   CLEAR SESSION
========================== */
$_SESSION = [];
session_unset();
session_destroy();


if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}


header("Location: /pojok_cafe/login/login.php");
exit;
?>