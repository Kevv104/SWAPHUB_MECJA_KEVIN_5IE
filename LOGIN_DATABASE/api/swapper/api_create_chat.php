<?php
// API: api_create_chat.php
// DESCRIZIONE: Crea una nuova chat e aggiunge i partecipanti (incluso l'autore)

header('Content-type: application/json');
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
    // In modalità test, l'autore della chat sarà 'gianno'
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
    $input = json_decode(file_get_contents('php://input'), true);

    $nomeChat    = $input['nome'] ?? ''; 
    $tipoChat    = $input['tipo'] ?? 'privata';
    $descrizione = $input['descrizione'] ?? '';
    $partecipanti = $input['partecipanti'] ?? []; // Array di username: ["mario", "anna"]

    // Validazione input
    if(empty($nomeChat)) {
        echo json_encode(["success" => false, "error" => "Nome chat obbligatorio"]);
        exit;
    }

    if(empty($partecipanti)) {
        echo json_encode(["success" => false, "error" => "Seleziona almeno un utente da invitare"]);
        exit;
    }

    // Il numero totale è il numero di invitati + l'utente corrente
    $numPartecipanti = count($partecipanti) + 1;

    // --- INIZIO TRANSAZIONE ---
    $connessione->begin_transaction();

    // 1. Inserimento nella tabella Chat
    $insertChat = $connessione->prepare("
        INSERT INTO Chat (nome, stato, numPartecipanti, descrizione, tipoChat, idScambio)
        VALUES (?, 'attiva', ?, ?, ?, NULL)
    ");

    $insertChat->bind_param("siss", $nomeChat, $numPartecipanti, $descrizione, $tipoChat);
    $insertChat->execute();

    $idChatCreata = $connessione->insert_id;

    // 2. Inserimento partecipanti nella tabella di collegamento PartecipaChat
    $insertPartecipante = $connessione->prepare("
        INSERT INTO PartecipaChat (idChat, User)
        VALUES (?, ?)
    ");

    // Aggiungiamo l'utente corrente (l'autore)
    $insertPartecipante->bind_param("is", $idChatCreata, $currentUser);
    $insertPartecipante->execute();

    // Aggiungiamo gli altri utenti passati nell'array
    foreach($partecipanti as $username) {
        $insertPartecipante->bind_param("is", $idChatCreata, $username);
        $insertPartecipante->execute();
    }

    // Se tutto è andato bene, salviamo nel database definitivamente
    $connessione->commit();

    echo json_encode([
        "success"           => true,
        "currentUser_debug" => $currentUser,
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