<?php
// API: api_accept_friend_request.php
// DESCRIZIONE: Accetta una richiesta di amicizia in entrata

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
    // In modalità test, facciamo finta di essere 'gianno' (il destinatario della richiesta)
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

// Verifica metodo POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Metodo non consentito. Usa POST"]);
    exit;
}

try {
    // Lettura input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $idRichiesta = $input['idRichiesta'] ?? null;

    if(empty($idRichiesta)) {
        echo json_encode([
            "success" => false,
            "error" => "ID richiesta obbligatorio"
        ]);
        exit;
    }

    // 1. Verifica esistenza richiesta e che il destinatario sia l'utente corrente
    $checkQuery = $connessione->prepare("
        SELECT idRichiesta, UserMittente, UserDestinatario, stato 
        FROM RichiesteAmicizia 
        WHERE idRichiesta = ? AND UserDestinatario = ?
    ");

    $checkQuery->bind_param("is", $idRichiesta, $currentUser);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if($result->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "error" => "Richiesta non trovata o non sei il destinatario autorizzato"
        ]);
        $checkQuery->close();
        exit;
    }

    $richiesta = $result->fetch_assoc();
    $checkQuery->close();

    // 2. Verifica se la richiesta è ancora in stato 'inviata'
    if($richiesta['stato'] !== 'inviata') {
        echo json_encode([
            "success" => false,
            "error" => "La richiesta non è più in attesa (stato attuale: " . $richiesta['stato'] . ")"
        ]);
        exit;
    }

    // 3. Aggiornamento stato in 'accettata'
    $updateQuery = $connessione->prepare("
        UPDATE RichiesteAmicizia 
        SET stato = 'accettata' 
        WHERE idRichiesta = ?
    ");

    $updateQuery->bind_param("i", $idRichiesta);
    $updateQuery->execute();

    if ($updateQuery->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Richiesta di amicizia accettata con successo!",
            "amicizia" => [
                'idRichiesta' => $idRichiesta,
                'userMittente' => $richiesta['UserMittente'],
                'userDestinatario' => $currentUser,
                'stato' => 'accettata'
            ]
        ]);
    } else {
        throw new Exception("Errore durante l'aggiornamento della richiesta.");
    }

    $updateQuery->close();
    $connessione->close();

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Errore del server: " . $e->getMessage()
    ]);
}
?>