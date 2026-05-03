<?php
/**
 * API: Invia un nuovo messaggio
 * Metodo: POST
 * Parametri: {idChat, contenuto}
 * Ritorna: {idMessaggio}
 * Autorizzazione: Controllo che l'utente partecipa alla chat
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
$idChat = $input['idChat'] ?? null;
$contenuto = $input['contenuto'] ?? null;

if (!$idChat || !is_numeric($idChat)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "idChat mancante o non valido"]);
    exit;
}

if (!$contenuto || empty(trim($contenuto))) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Contenuto mancante o vuoto"]);
    exit;
}

try {
    // Step 1: Verifica che l'utente partecipa a questa chat
    $queryCheck = $connessione->prepare("
        SELECT COUNT(*) as count 
        FROM PartecipaChat 
        WHERE idChat = ? AND User = ?
    ");
    $queryCheck->bind_param("is", $idChat, $currentUser);
    $queryCheck->execute();
    $resultCheck = $queryCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if ($rowCheck['count'] == 0) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Non hai accesso a questa chat"]);
        exit;
    }

    // Step 2: Regola ruoli - uno swapper non puo' scrivere a utenti non swapper
    $queryRoleCurrent = $connessione->prepare("
        SELECT r.nomeRuolo
        FROM UtenteRuolo ur
        INNER JOIN Ruolo r ON r.idRuolo = ur.idRuolo
        WHERE ur.username = ?
        LIMIT 1
    ");
    $queryRoleCurrent->bind_param("s", $currentUser);
    $queryRoleCurrent->execute();
    $resultRoleCurrent = $queryRoleCurrent->get_result();
    $rowRoleCurrent = $resultRoleCurrent->fetch_assoc();
    $currentRole = $rowRoleCurrent['nomeRuolo'] ?? null;
    $queryRoleCurrent->close();

    if ($currentRole === 'Swapper') {
        $queryNonSwapperInChat = $connessione->prepare("
            SELECT COUNT(*) as nonSwapperCount
            FROM PartecipaChat pc
            INNER JOIN UtenteRuolo ur ON ur.username = pc.User
            INNER JOIN Ruolo r ON r.idRuolo = ur.idRuolo
            WHERE pc.idChat = ?
              AND pc.User != ?
              AND r.nomeRuolo != 'Swapper'
        ");
        $queryNonSwapperInChat->bind_param("is", $idChat, $currentUser);
        $queryNonSwapperInChat->execute();
        $resultNonSwapperInChat = $queryNonSwapperInChat->get_result();
        $rowNonSwapperInChat = $resultNonSwapperInChat->fetch_assoc();
        $queryNonSwapperInChat->close();

        if ((int)$rowNonSwapperInChat['nonSwapperCount'] > 0) {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "error" => "Uno swapper non puo' inviare messaggi a moderatori, corrieri o admin"
            ]);
            exit;
        }
    }

    // Step 3: Inserisci il messaggio
    // dataInvio viene impostato automaticamente a NOW() dal database
    $queryInsert = $connessione->prepare("
        INSERT INTO Messaggi (idChat, User, contenuto, dataInvio)
        VALUES (?, ?, ?, NOW())
    ");
    
    $queryInsert->bind_param("iss", $idChat, $currentUser, $contenuto);
    $queryInsert->execute();

    // Ottieni l'ID del messaggio inserito
    $idMessaggio = $connessione->insert_id;

    echo json_encode([
        "success" => true,
        "idMessaggio" => $idMessaggio,
        "message" => "Messaggio inviato con successo"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore server: " . $e->getMessage()]);
}
?>
