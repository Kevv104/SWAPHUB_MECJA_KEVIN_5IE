<?php
/**
 * API: Aggiorna descrizione chat
 * Metodo: POST
 * Body JSON: { idChat, descrizione }
 * Requisiti: essere il creatore della chat
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
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
    $currentUser = $decoded->sub;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token non valido']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$idChat = $input['idChat'] ?? null;
$descrizione = $input['descrizione'] ?? '';

if (!$idChat || !is_numeric($idChat)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'idChat mancante o non valido']);
    exit;
}

try {
    // Assicuriamo che esista la tabella ChatOwner
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

    // Verifica che l'utente sia il creatore
    $q = $connessione->prepare("SELECT creator FROM ChatOwner WHERE idChat = ? LIMIT 1");
    $q->bind_param('i', $idChat);
    $q->execute();
    $res = $q->get_result();
    $row = $res->fetch_assoc();
    $q->close();

    if (!$row) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Proprietario della chat non trovato']);
        exit;
    }

    if ($row['creator'] !== $currentUser) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permesso negato: solo il creatore puo\' modificare la descrizione']);
        exit;
    }

    // Aggiorna descrizione
    $upd = $connessione->prepare("UPDATE Chat SET descrizione = ? WHERE idChat = ?");
    $upd->bind_param('si', $descrizione, $idChat);
    $upd->execute();

    echo json_encode(['success' => true, 'messaggio' => 'Descrizione aggiornata']);

    $upd->close();
    $connessione->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore server: ' . $e->getMessage()]);
}

?>
