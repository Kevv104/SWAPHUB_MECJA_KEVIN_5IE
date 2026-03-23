<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'SWAPHUB');
define('DB_USER', 'mecja_kevin');
define('DB_PASS', 'mEcjA69@104');

try
{
   $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false
        ]
   );
}
catch(PDOException $e) {
    // In caso di errore di connessione
    die(json_encode([
        "error" => "Errore connessione database: " . $e->getMessage()
    ]));
}
?>