<?php
/**
 * API: GET messaggi di una chat
 * Metodo: GET
 * Parametri: idChat (query string)
 * Ritorna: Array di messaggi con flag isMine
 * Autorizzazione: Controllo che l'utente partecipa alla chat
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Estrai l'utente corrente dal JWT (dalla sessione)
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

// Validazione parametri
$idChat = $_GET['idChat'] ?? null;

if (!$idChat || !is_numeric($idChat)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "idChat mancante o non valido"]);
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

    // Step 2: Carica i messaggi della chat, ordinati per data
    $queryMessages = $connessione->prepare("
        SELECT 
            m.idMessaggio,
            m.idChat,
            m.User,
            m.contenuto,
            m.dataInvio
        FROM Messaggi m
        WHERE m.idChat = ?
        ORDER BY m.dataInvio ASC
    ");
    
    $queryMessages->bind_param("i", $idChat);
    $queryMessages->execute();
    $resultMessages = $queryMessages->get_result();

    $messages = [];
    while ($row = $resultMessages->fetch_assoc()) {
        $messages[] = [
            'idMessaggio' => (int)$row['idMessaggio'],
            'idChat' => (int)$row['idChat'],
            'user' => $row['User'],
            'contenuto' => htmlspecialchars($row['contenuto']),
            'dataInvio' => $row['dataInvio'],
            'isMine' => ($row['User'] === $currentUser) ? true : false
        ];
    }

    echo json_encode([
        "success" => true,
        "messages" => $messages,
        "totalMessages" => count($messages)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore server: " . $e->getMessage()]);
}
?>
