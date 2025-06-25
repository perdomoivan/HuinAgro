<?php
define('HUINAGRO_SYSTEM', true);


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$_SESSION = [];
session_unset();
session_destroy();


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


header("Location: login.php");
exit;
?>