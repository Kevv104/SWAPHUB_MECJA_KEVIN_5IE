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
$isDevelopment = false; 

if ($isDevelopment) {
    $currentUser = 'gianno'; 
    $tenantLookup = $connessione->prepare("SELECT tenant_id FROM utenti WHERE username = ?");
    $tenantLookup->bind_param("s", $currentUser);
    $tenantLookup->execute();
    $tenantResult = $tenantLookup->get_result();
    $tenantRow = $tenantResult->fetch_assoc();
    $tenantLookup->close();

    if (!$tenantRow || empty($tenantRow['tenant_id'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Tenant utente non trovato"]);
        exit;
    }

    $currentTenantId = (int)$tenantRow['tenant_id'];
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
        $currentTenantId = isset($decoded->tenant_id) ? (int)$decoded->tenant_id : 0;
        if(!$currentUser || $currentTenantId <= 0) {
            throw new Exception("Token privo di tenant valido");
        }
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
            v.idChat,
            v.nomeChat,
            v.tipoChat,
            v.stato,
            v.numPartecipanti,
            v.dataCreazione,
            v.descrizione,
            v.totMessaggi,
            v.ultimoMessaggio,
            v.dataUltimoMessaggio,
            v.autoreUltimoMessaggio
        FROM vista_chat_utente v
        JOIN utenti u ON u.username = v.username
        WHERE v.username = ?
          AND u.tenant_id = ?
        ORDER BY v.dataUltimoMessaggio DESC
    ");

    $query->bind_param("si", $currentUser, $currentTenantId);
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
        "tenant_debug"      => $currentTenantId,
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