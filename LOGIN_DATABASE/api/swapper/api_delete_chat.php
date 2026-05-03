<?php
/**
 * API: Cancella chat
 * Metodo: POST
 * Parametri: {idChat}
 * Regola: puo' cancellare solo il creatore della chat
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

$input = json_decode(file_get_contents("php://input"), true);
$idChat = $input['idChat'] ?? null;

if (!$idChat || !is_numeric($idChat)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "idChat mancante o non valido"]);
    exit;
}

try {
    // Tabella proprietario chat
    $connessione->query(" 
        CREATE TABLE IF NOT EXISTS ChatOwner (
            idChat INT(11) NOT NULL,
            creator VARCHAR(50) NOT NULL,
            createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (idChat),
            KEY creator (creator),
            CONSTRAINT fk_chatowner_chat FOREIGN KEY (idChat) REFERENCES Chat(idChat) ON DELETE CASCADE,
            CONSTRAINT fk_chatowner_creator FOREIGN KEY (creator) REFERENCES utenti(username) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    // Verifica creatore
    $queryOwner = $connessione->prepare(" 
        SELECT creator
        FROM ChatOwner
        WHERE idChat = ?
        LIMIT 1
    ");
    $queryOwner->bind_param("i", $idChat);
    $queryOwner->execute();
    $resultOwner = $queryOwner->get_result();
    $rowOwner = $resultOwner->fetch_assoc();
    $queryOwner->close();

    if (!$rowOwner) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "Chat non cancellabile: creatore non registrato"
        ]);
        exit;
    }

    if ($rowOwner['creator'] !== $currentUser) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "Solo il creatore puo' cancellare la chat"
        ]);
        exit;
    }

    $connessione->begin_transaction();

    // Cancella messaggi (non c'e' FK su Messaggi -> Chat nello schema)
    $deleteMessages = $connessione->prepare("DELETE FROM Messaggi WHERE idChat = ?");
    $deleteMessages->bind_param("i", $idChat);
    $deleteMessages->execute();
    $deleteMessages->close();

    // Cancella partecipanti
    $deleteMembers = $connessione->prepare("DELETE FROM PartecipaChat WHERE idChat = ?");
    $deleteMembers->bind_param("i", $idChat);
    $deleteMembers->execute();
    $deleteMembers->close();

    // Cancella owner (se non entra in cascade)
    $deleteOwner = $connessione->prepare("DELETE FROM ChatOwner WHERE idChat = ?");
    $deleteOwner->bind_param("i", $idChat);
    $deleteOwner->execute();
    $deleteOwner->close();

    // Cancella chat
    $deleteChat = $connessione->prepare("DELETE FROM Chat WHERE idChat = ?");
    $deleteChat->bind_param("i", $idChat);
    $deleteChat->execute();

    if ($deleteChat->affected_rows === 0) {
        $deleteChat->close();
        $connessione->rollback();
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Chat non trovata"]);
        exit;
    }

    $deleteChat->close();

    $connessione->commit();

    echo json_encode([
        "success" => true,
        "message" => "Chat cancellata con successo"
    ]);
} catch (Exception $e) {
    if (isset($connessione)) {
        $connessione->rollback();
    }

    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore server: " . $e->getMessage()]);
}
?>
