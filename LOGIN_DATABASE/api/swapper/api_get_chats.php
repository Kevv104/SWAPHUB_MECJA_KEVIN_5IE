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
    // In modalità test, carichiamo le chat dell'utente 'gianno'
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
    // Tabella dedicata al proprietario della chat (creatore)
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

    // Query per prendere tutte le chat dell'utente dalla view
    $query = $connessione->prepare("
        SELECT 
            vcu.idChat,
            vcu.nomeChat,
            vcu.tipoChat,
            vcu.stato,
            vcu.numPartecipanti,
            vcu.dataCreazione,
            vcu.descrizione,
            vcu.totMessaggi,
            vcu.ultimoMessaggio,
            vcu.dataUltimoMessaggio,
            vcu.autoreUltimoMessaggio,
            CASE WHEN co.creator = ? THEN 1 ELSE 0 END AS isCreator
        FROM vista_chat_utente vcu
        LEFT JOIN ChatOwner co ON co.idChat = vcu.idChat
        WHERE vcu.username = ?
        ORDER BY vcu.dataUltimoMessaggio DESC
    ");

    $query->bind_param("ss", $currentUser, $currentUser);
    $query->execute();
    $result = $query->get_result();

    $chats = [];
    while($row = $result->fetch_assoc()) {
        $chats[] = [
            'idChat'                => $row['idChat'],
            'nomeChat'              => $row['nomeChat'],
            'tipoChat'              => $row['tipoChat'],
            'stato'                 => $row['stato'],
            'numPartecipanti'       => $row['numPartecipanti'],
            'dataCreazione'         => $row['dataCreazione'],
            'descrizione'           => $row['descrizione'],
            'totMessaggi'           => $row['totMessaggi'] ?? 0,
            'ultimoMessaggio'       => $row['ultimoMessaggio'] ?? 'Nessun messaggio',
            'dataUltimoMessaggio'   => $row['dataUltimoMessaggio'],
            'autoreUltimoMessaggio' => $row['autoreUltimoMessaggio'],
            'isCreator'             => ((int)$row['isCreator'] === 1)
        ];
    }

    echo json_encode([
        "success"           => true,
        "currentUser_debug" => $currentUser,
        "chats"             => $chats,
        "totalChats"        => count($chats)
    ]);

    $query->close();
    $connessione->close();

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "Errore del server: " . $e->getMessage()
    ]);
}
?>