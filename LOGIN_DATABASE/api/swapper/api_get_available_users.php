<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN) ---
 */
$isDevelopment = true; 

if ($isDevelopment) {
    // In modalità test, escludiamo 'gianno' dalla lista (mostrando tutti gli altri)
    $currentUser = 'gianno'; 
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(['cookie_path' => '/login/']);
    }

    if(!isset($_SESSION['jwt'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Non autorizzato"]); 
        exit;
    }

    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $currentUser = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Token non valido"]);
        exit;
    }
}

try {
    // Query per prendere tutti gli utenti tranne quello loggato
    $query = $connessione->prepare("
        SELECT 
            username,
            Nome,
            Cognome,
            Email,
            fotoprofilo
        FROM utenti
        WHERE username != ?
        ORDER BY Nome, Cognome
    ");

    $query->bind_param("s", $currentUser);
    $query->execute();
    $result = $query->get_result();

    $utenti = [];
    while($row = $result->fetch_assoc()) {
        $utenti[] = [
            'username'    => $row['username'],
            'Nome'        => $row['Nome'],
            'Cognome'     => $row['Cognome'],
            'Email'       => $row['Email'],
            'fotoprofilo' => $row['fotoprofilo']
        ];
    }
    
    echo json_encode([
        "success"           => true,
        "currentUser_debug" => $currentUser,
        "utenti"            => $utenti,
        "totalUtenti"       => count($utenti)
    ]);

    $query->close();
    $connessione->close();

} catch(Exception $e) {
    // In caso di errore nel database o nella query
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "Errore del server: " . $e->getMessage()
    ]);
}
?>