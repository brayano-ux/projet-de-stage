<?php

session_start();

$_SESSION = array();

// Détruire le cookie de session si applicable
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

// Détruire la session
session_destroy();

// Message de succès et redirection
header("Location: CONNEXION.php?logout=success");
exit();
?>
