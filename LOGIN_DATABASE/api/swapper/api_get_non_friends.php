<?php
// API: api_get_non_friends.php
// CASO D'USO: send_friend_request (Permesso #5)
// DESCRIZIONE: Recupera lista utenti con cui NON si è amici

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
    // In modalità test, cerchiamo gli utenti che NON sono amici di 'gianno'
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
    /**
     * Query per ottenere utenti che:
     * 1. Non sono l'utente stesso
     * 2. Non hanno una richiesta inviata (in attesa)
     * 3. Non sono già amici (richiesta accettata)
     */
    $query = $connessione->prepare("
        SELECT 
            u.username,
            u.Nome,
            u.Cognome,
            u.Email,
            u.fotoprofilo,
            u.localita
        FROM utenti u
        WHERE u.username != ?
        AND u.username NOT IN (
            -- Utenti a cui hai inviato richiesta (inviata o accettata)
            SELECT UserDestinatario 
            FROM RichiesteAmicizia 
            WHERE UserMittente = ? AND stato IN ('inviata', 'accettata')
            
            UNION
            
            -- Utenti che hanno inviato richiesta a te (inviata o accettata)
            SELECT UserMittente 
            FROM RichiesteAmicizia 
            WHERE UserDestinatario = ? AND stato IN ('inviata', 'accettata')
        )
        ORDER BY u.Nome, u.Cognome
    ");

    // Passiamo 3 volte $currentUser (per lo username principale e le due subquery)
    $query->bind_param("sss", $currentUser, $currentUser, $currentUser);
    $query->execute();
    $result = $query->get_result();

    $utenti = [];
    while($row = $result->fetch_assoc()) {
        $utenti[] = [
            'username'    => $row['username'],
            'Nome'        => $row['Nome'],
            'Cognome'     => $row['Cognome'],
            'Email'       => $row['Email'],
            'fotoprofilo' => $row['fotoprofilo'],
            'localita'    => $row['localita']
        ];
    }

    echo json_encode([
        "success" => true,
        "currentUser_debug" => $currentUser,
        "utenti" => $utenti,
        "totalUtenti" => count($utenti)
    ]);

    $query->close();
    $connessione->close();

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Errore del server: " . $e->getMessage()
    ]);
}
?>