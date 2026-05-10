<?php
/**
 * API: Get dettagli chat
 * Metodo: GET
 * Parametri: idChat
 * Ritorna: dettagli chat + membri con foto profilo
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

$idChat = $_GET['idChat'] ?? null;

if (!$idChat || !is_numeric($idChat)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "idChat mancante o non valido"]);
    exit;
}

try {
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

    $queryAccess = $connessione->prepare("
        SELECT COUNT(*) AS count
        FROM PartecipaChat
        WHERE idChat = ? AND User = ?
    ");
    $queryAccess->bind_param("is", $idChat, $currentUser);
    $queryAccess->execute();
    $accessResult = $queryAccess->get_result();
    $accessRow = $accessResult->fetch_assoc();
    $queryAccess->close();

    if ((int)($accessRow['count'] ?? 0) === 0) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Non hai accesso a questa chat"]);
        exit;
    }

    $queryChat = $connessione->prepare("
        SELECT
            c.idChat,
            c.nome AS nomeChat,
            c.tipoChat,
            c.stato,
            c.numPartecipanti,
            c.descrizione,
            c.dataCreazione,
            COALESCE(co.creator, '') AS creator,
            co.createdAt AS ownerCreatedAt
        FROM Chat c
        LEFT JOIN ChatOwner co ON co.idChat = c.idChat
        WHERE c.idChat = ?
        LIMIT 1
    ");
    $queryChat->bind_param("i", $idChat);
    $queryChat->execute();
    $chatResult = $queryChat->get_result();
    $chatRow = $chatResult->fetch_assoc();
    $queryChat->close();

    if (!$chatRow) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Chat non trovata"]);
        exit;
    }

    $queryMessages = $connessione->prepare("
        SELECT COUNT(*) AS totalMessages
        FROM Messaggi
        WHERE idChat = ?
    ");
    $queryMessages->bind_param("i", $idChat);
    $queryMessages->execute();
    $messagesResult = $queryMessages->get_result();
    $messagesRow = $messagesResult->fetch_assoc();
    $queryMessages->close();

    $queryMembers = $connessione->prepare("
        SELECT
            u.username,
            u.Nome,
            u.Cognome,
            u.fotoprofilo,
            u.localita,
            r.nomeRuolo,
            CASE WHEN co.creator = u.username THEN 1 ELSE 0 END AS isCreator,
            pc.dataAdesione
        FROM PartecipaChat pc
        INNER JOIN utenti u ON u.username = pc.User
        LEFT JOIN UtenteRuolo ur ON ur.username = u.username
        LEFT JOIN Ruolo r ON r.idRuolo = ur.idRuolo
        LEFT JOIN ChatOwner co ON co.idChat = pc.idChat
        WHERE pc.idChat = ?
        ORDER BY isCreator DESC, u.Nome, u.Cognome
    ");
    $queryMembers->bind_param("i", $idChat);
    $queryMembers->execute();
    $membersResult = $queryMembers->get_result();

    $membri = [];
    while ($row = $membersResult->fetch_assoc()) {
        $membri[] = [
            'username' => $row['username'],
            'Nome' => $row['Nome'],
            'Cognome' => $row['Cognome'],
            'fotoprofilo' => $row['fotoprofilo'],
            'localita' => $row['localita'],
            'nomeRuolo' => $row['nomeRuolo'],
            'isCreator' => ((int)$row['isCreator'] === 1),
            'dataAdesione' => $row['dataAdesione']
        ];
    }
    $queryMembers->close();

    echo json_encode([
        'success' => true,
        'chat' => [
            'idChat' => (int)$chatRow['idChat'],
            'nomeChat' => $chatRow['nomeChat'],
            'tipoChat' => $chatRow['tipoChat'],
            'stato' => $chatRow['stato'],
            'numPartecipanti' => (int)$chatRow['numPartecipanti'],
            'descrizione' => $chatRow['descrizione'],
            'dataCreazione' => $chatRow['dataCreazione'],
            'creator' => $chatRow['creator'] ?: null,
            'ownerCreatedAt' => $chatRow['ownerCreatedAt'],
            'youAreCreator' => ($chatRow['creator'] === $currentUser),
            'totalMessages' => (int)($messagesRow['totalMessages'] ?? 0)
        ],
        'membri' => $membri,
        'totalMembri' => count($membri)
    ]);

    $connessione->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore del server: ' . $e->getMessage()
    ]);
}
?>