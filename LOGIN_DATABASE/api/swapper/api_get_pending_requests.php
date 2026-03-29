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
    // In modalità test, cerchiamo le richieste arrivate a 'gianno'
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
    // Query per ottenere le richieste in attesa (stato = 'inviata') 
    // dove il destinatario è l'utente corrente
    $query = $connessione->prepare("
        SELECT 
            r.idRichiesta,
            r.UserMittente,
            r.UserDestinatario,
            r.stato,
            r.commento,
            r.dataInvio,
            u.Nome,
            u.Cognome,
            u.Email,
            u.fotoprofilo,
            u.localita
        FROM RichiesteAmicizia r
        JOIN utenti u ON r.UserMittente = u.username
        WHERE r.UserDestinatario = ? 
        AND r.stato = 'inviata'
        ORDER BY r.dataInvio DESC
    ");

    $query->bind_param("s", $currentUser);
    $query->execute();
    $result = $query->get_result();

    $richieste = [];
    while($row = $result->fetch_assoc()) {
        $richieste[] = [
            'idRichiesta'   => $row['idRichiesta'],
            'userMittente'  => $row['UserMittente'],
            'Nome'          => $row['Nome'],
            'Cognome'       => $row['Cognome'],
            'Email'         => $row['Email'],
            'fotoprofilo'   => $row['fotoprofilo'],
            'localita'      => $row['localita'],
            'commento'      => $row['commento'],
            'dataInvio'     => $row['dataInvio'],
            'stato'         => $row['stato']
        ];
    }

    echo json_encode([
        "success" => true,
        "currentUser_debug" => $currentUser,
        "richieste" => $richieste,
        "totalRichieste" => count($richieste)
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