<?php
// FILE: logout.php (DA METTERE NELLA ROOT)
// Percorso: C:\xampp\htdocs\waf-fle-master-vecchio\logout.php

// 1. Carichiamo la configurazione (siamo nella root, quindi niente ../)
require_once 'config.php';

// 2. Avviamo la sessione per poterla distruggere
session_start();

// 3. Svuotiamo l'array di sessione
$_SESSION = array();

// 4. Se si usano i cookie di sessione (come fa XAMPP di default), cancelliamoli
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Distruggiamo fisicamente la sessione sul server
session_destroy();

// 6. Reindirizziamo l'utente alla pagina di Login (che ora è index.php nella root)
header("Location: index.php");
exit;
?>