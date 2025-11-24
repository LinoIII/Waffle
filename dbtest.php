<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=waffle;charset=utf8mb4',
        'waffle_user',
        'waffle',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    echo "CONNESSO OK come waffle_user su waffle";
} catch (PDOException $e) {
    echo "ERRORE CONNESSIONE: " . $e->getMessage();
}
