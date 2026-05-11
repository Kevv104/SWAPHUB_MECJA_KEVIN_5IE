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
 * --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN/LOCALHOST) ---
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_path' => '/login/']);
}

// Bypass per development/Postman: se non c'è JWT e siamo in localhost, usa test user
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
               (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

$currentUser = null;

if (isset($_SESSION['jwt'])) {
    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $currentUser = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Token non valido"]);
        exit;
    }
} elseif ($isLocalhost) {
    // Bypass solo per localhost/development
    $currentUser = 'gianno';
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Non autorizzato"]);
    exit;
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

    // Regola ruoli: se l'utente corrente e' Swapper, puo' invitare solo altri Swapper
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
        $notSwapperFound = false;

        $queryRoleParticipant = $connessione->prepare("
            SELECT r.nomeRuolo
            FROM UtenteRuolo ur
            INNER JOIN Ruolo r ON r.idRuolo = ur.idRuolo
            WHERE ur.username = ?
            LIMIT 1
        ");

        foreach($partecipanti as $username) {
            $queryRoleParticipant->bind_param("s", $username);
            $queryRoleParticipant->execute();
            $resultRoleParticipant = $queryRoleParticipant->get_result();
            $rowRoleParticipant = $resultRoleParticipant->fetch_assoc();
            $participantRole = $rowRoleParticipant['nomeRuolo'] ?? null;

            if ($participantRole !== 'Swapper') {
                $notSwapperFound = true;
                break;
            }
        }

        $queryRoleParticipant->close();

        if ($notSwapperFound) {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "error" => "Uno swapper puo' creare chat solo con altri swapper"
            ]);
            exit;
        }
    }

    // Il numero totale è il numero di invitati + l'utente corrente
    $numPartecipanti = count($partecipanti) + 1;

    // --- INIZIO TRANSAZIONE ---
    $connessione->begin_transaction();

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

    // 1. Inserimento nella tabella Chat
    $insertChat = $connessione->prepare("
        INSERT INTO Chat (nome, stato, numPartecipanti, descrizione, tipoChat, idScambio)
        VALUES (?, 'attiva', ?, ?, ?, NULL)
    ");

    $insertChat->bind_param("siss", $nomeChat, $numPartecipanti, $descrizione, $tipoChat);
    $insertChat->execute();

    $idChatCreata = $connessione->insert_id;

    // 1b. Salva il creatore della chat
    $insertOwner = $connessione->prepare("
        INSERT INTO ChatOwner (idChat, creator)
        VALUES (?, ?)
    ");
    $insertOwner->bind_param("is", $idChatCreata, $currentUser);
    $insertOwner->execute();

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
    $insertOwner->close();
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