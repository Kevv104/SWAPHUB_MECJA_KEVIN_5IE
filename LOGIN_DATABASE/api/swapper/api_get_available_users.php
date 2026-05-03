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
    // Determina il ruolo dell'utente corrente
    $queryRole = $connessione->prepare("
        SELECT r.nomeRuolo
        FROM UtenteRuolo ur
        INNER JOIN Ruolo r ON r.idRuolo = ur.idRuolo
        WHERE ur.username = ?
        LIMIT 1
    ");
    $queryRole->bind_param("s", $currentUser);
    $queryRole->execute();
    $resultRole = $queryRole->get_result();
    $rowRole = $resultRole->fetch_assoc();
    $currentRole = $rowRole['nomeRuolo'] ?? null;
    $queryRole->close();

    // Se l'utente corrente e' Swapper, mostra solo altri utenti Swapper
    if ($currentRole === 'Swapper') {
        $query = $connessione->prepare("
            SELECT 
                u.username,
                u.Nome,
                u.Cognome,
                u.Email,
                u.fotoprofilo
            FROM utenti u
            INNER JOIN UtenteRuolo ur ON ur.username = u.username
            INNER JOIN Ruolo r ON r.idRuolo = ur.idRuolo
            WHERE u.username != ?
              AND r.nomeRuolo = 'Swapper'
            ORDER BY u.Nome, u.Cognome
        ");
    } else {
        // Per gli altri ruoli mantiene comportamento attuale
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
    }

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
        "currentRole_debug" => $currentRole,
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