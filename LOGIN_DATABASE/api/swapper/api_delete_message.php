<?php
/**
 * API: Cancella un messaggio
 * Metodo: POST
 * Parametri: {idMessaggio}
 * Ritorna: {success}
 * Autorizzazione: Solo l'autore del messaggio può cancellarlo
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Estrai l'utente corrente dal JWT
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_path' => '/login/']);
}

if (!isset($_SESSION['jwt'])) {
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

// Ricevi JSON dal body
$input = json_decode(file_get_contents("php://input"), true);

// Validazione parametri
$idMessaggio = $input['idMessaggio'] ?? null;

if (!$idMessaggio || !is_numeric($idMessaggio)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "idMessaggio mancante o non valido"]);
    exit;
}

try {
    // Step 1: Verifica che il messaggio appartiene all'utente corrente
    $queryCheck = $connessione->prepare("
        SELECT User FROM Messaggi 
        WHERE idMessaggio = ?
    ");
    $queryCheck->bind_param("i", $idMessaggio);
    $queryCheck->execute();
    $resultCheck = $queryCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if (!$rowCheck) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Messaggio non trovato"]);
        exit;
    }

    if ($rowCheck['User'] !== $currentUser) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Non puoi cancellare questo messaggio"]);
        exit;
    }

    // Step 2: Cancella il messaggio
    $queryDelete = $connessione->prepare("
        DELETE FROM Messaggi 
        WHERE idMessaggio = ?
    ");
    
    $queryDelete->bind_param("i", $idMessaggio);
    $queryDelete->execute();

    echo json_encode([
        "success" => true,
        "message" => "Messaggio cancellato con successo"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore server: " . $e->getMessage()]);
}
?>
