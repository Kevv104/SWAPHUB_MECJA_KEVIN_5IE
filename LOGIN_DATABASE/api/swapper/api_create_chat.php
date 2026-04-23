<?php
// API: api_create_chat.php
// DESCRIZIONE: Crea una nuova chat e aggiunge i partecipanti (incluso l'autore)

header('Content-type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//bypass per sviluppo locale senza dover passare il jwt da postman (o simili)
$isDevelopment = (
    (isset($_GET['dev_bypass']) && $_GET['dev_bypass'] === '1') ||
    (isset($_SERVER['HTTP_X_DEV_BYPASS']) && $_SERVER['HTTP_X_DEV_BYPASS'] === '1')
);
//se è attivo il bypass, forza un utente e tenant specifico
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

    // Decodifica e verifica del JWT
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

// Verifica metodo POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Metodo non consentito. Usa POST"]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $nomeChat    = $input['nome'] ?? ''; 
    $tipoChat    = $input['tipo'] ?? 'privata';
    $descrizione = $input['descrizione'] ?? '';
    $partecipanti = $input['partecipanti'] ?? [];
    $partecipanti = array_values(array_unique(array_filter(array_map('trim', $partecipanti))));

    // Validazione input
    if(empty($nomeChat)) {
        echo json_encode(["success" => false, "error" => "Nome chat obbligatorio"]);
        exit;
    }

    if(empty($partecipanti)) {
        echo json_encode(["success" => false, "error" => "Seleziona almeno un utente da invitare"]);
        exit;
    }

    if(in_array($currentUser, $partecipanti, true)) {
        echo json_encode(["success" => false, "error" => "Non puoi invitare te stesso nella stessa chat"]);
        exit;
    }

    $checkAuthor = $connessione->prepare("SELECT 1 FROM utenti WHERE username = ? AND tenant_id = ?"); // Verifica che l'autore della chat appartenga al tenant
    $checkAuthor->bind_param("si", $currentUser, $currentTenantId);
    $checkAuthor->execute();
    $checkAuthor->store_result();
    if($checkAuthor->num_rows === 0) {
        $checkAuthor->close();
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Utente non autorizzato per questo tenant"]);
        exit;
    }
    $checkAuthor->close();

    $checkPartecipante = $connessione->prepare("SELECT 1 FROM utenti WHERE username = ? AND tenant_id = ?");
    foreach($partecipanti as $usernamePartecipante) {
        $checkPartecipante->bind_param("si", $usernamePartecipante, $currentTenantId);
        $checkPartecipante->execute();
        $checkPartecipante->store_result();
        if($checkPartecipante->num_rows === 0) {
            $checkPartecipante->close();
            echo json_encode([
                "success" => false,
                "error" => "L'utente {$usernamePartecipante} non appartiene al tuo tenant"
            ]);
            exit;
        }
        $checkPartecipante->free_result();
    }
    $checkPartecipante->close();

    // Il numero totale è il numero di invitati + l'utente corrente
    $numPartecipanti = count($partecipanti) + 1;

    // --- INIZIO TRANSAZIONE ---
    $connessione->begin_transaction();

    // 1. Inserimento nella tabella Chat
    $insertChat = $connessione->prepare("
        INSERT INTO Chat (tenant_id, nome, stato, numPartecipanti, descrizione, tipoChat, idScambio)
        VALUES (?, ?, 'attiva', ?, ?, ?, NULL)
    ");

    $insertChat->bind_param("isiss", $currentTenantId, $nomeChat, $numPartecipanti, $descrizione, $tipoChat);
    $insertChat->execute();

    $idChatCreata = $connessione->insert_id;

    // 2. Inserimento partecipanti nella tabella di collegamento PartecipaChat
    $insertPartecipante = $connessione->prepare("
        INSERT INTO PartecipaChat (idChat, tenant_id, User)
        VALUES (?, ?, ?)
    ");

    // Aggiungiamo l'utente corrente (l'autore)
    $insertPartecipante->bind_param("iis", $idChatCreata, $currentTenantId, $currentUser);
    $insertPartecipante->execute();

    // Aggiungiamo gli altri utenti passati nell'array
    foreach($partecipanti as $username) {
        $insertPartecipante->bind_param("iis", $idChatCreata, $currentTenantId, $username);
        $insertPartecipante->execute();
    }

    // Se tutto è andato bene, salviamo nel database definitivamente
    $connessione->commit();

    echo json_encode([
        "success"           => true,
        "currentUser_debug" => $currentUser,
        "tenant_debug"      => $currentTenantId,
        "idChat"            => $idChatCreata,
        "messaggio"         => "Chat creata con successo!"
    ]);

    $insertChat->close();
    $insertPartecipante->close();
    $connessione->close();

} catch(Exception $e) {
    // Se qualcosa fallisce, annulla tutte le modifiche fatte in questa transazione
    if(isset($connessione)) { $connessione->rollback(); }
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "Errore nella creazione: " . $e->getMessage()
    ]);
}
?>