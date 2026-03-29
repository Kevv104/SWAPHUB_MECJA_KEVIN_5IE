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
    // Query per prendere tutte le chat dell'utente dalla view
    $query = $connessione->prepare("
        SELECT 
            idChat,
            nomeChat,
            tipoChat,
            stato,
            numPartecipanti,
            dataCreazione,
            descrizione,
            totMessaggi,
            ultimoMessaggio,
            dataUltimoMessaggio,
            autoreUltimoMessaggio
        FROM vista_chat_utente 
        WHERE username = ?
        ORDER BY dataUltimoMessaggio DESC
    ");

    $query->bind_param("s", $currentUser);
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
            'autoreUltimoMessaggio' => $row['autoreUltimoMessaggio']
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